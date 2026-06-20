/**
 * DetectionService — 检测任务的业务编排层。
 *
 * 从 backend/routes/detection.js 抽离的纯业务逻辑（审计 N4）：
 *   - resolveProjectContext:    解析项目上下文（委托 ScheduleProjectContextService）
 *   - saveCompletedDetectionResult: 保存流式/同步检测结果到 DB
 *   - processAIQuery:           异步执行 AI 查询并落库
 *
 * 这些函数不依赖 HTTP（req/res），便于单独测试与复用。
 * SSE 流式编排（操作 res）仍保留在路由层。
 */

const {
  QuestionRecord,
  ResultDetail,
} = require('../models');
const AIPlatformService = require('./AIPlatformService');
const ResultParserService = require('./ResultParserService');
const ProjectRecordFinalizationService = require('./ProjectRecordFinalizationService');
const ScheduleProjectContextService = require('./ScheduleProjectContextService');

const SAFE_PLATFORM_FAILURE_MESSAGE = '监测平台调用失败，请稍后重试';

/**
 * 解析检测请求的项目上下文。
 * @param {{user: object, source: object}} params
 * @returns {Promise<object>} { user_id, project_id, tracked_prompt_id, ... } 或抛错
 */
async function resolveProjectContext({ user, source }) {
  return ScheduleProjectContextService.resolveProjectContext({
    user,
    source,
    messages: {
      promptRequiresProject: '使用 Prompt 检测时必须提供 project_id',
      archivedProject: '归档项目不能运行检测',
      disabledPrompt: '停用 Prompt 不能运行检测'
    }
  });
}

/**
 * 保存一条已完成的检测结果（流式/同步共用）。
 * @param {object} args
 * @returns {Promise<object>} 新建的 QuestionRecord
 */
async function saveCompletedDetectionResult({
  user_id,
  platform,
  question,
  brand,
  brandKeywordsStr,
  projectContext,
  responseText,
  aiResponse = null
}) {
  const record = await QuestionRecord.create({
    user_id: projectContext?.user_id || user_id,
    project_id: projectContext?.project_id || null,
    tracked_prompt_id: projectContext?.tracked_prompt_id || null,
    platform,
    question,
    brand: brand ? String(brand).trim() : null,
    brand_keywords: brandKeywordsStr || ''
  });

  await ResultDetail.create({
    question_record_id: record.id,
    ai_response_original: responseText,
    parsing_status: 'completed'
  });

  const keywordsArr = typeof brandKeywordsStr === 'string'
    ? brandKeywordsStr.split(/[,，]/).map(s => s.trim()).filter(Boolean)
    : [];
  await ProjectRecordFinalizationService.finalize({
    record,
    responseText,
    aiResponse,
    keywords: keywordsArr
  });
  return record;
}

/**
 * 异步执行 AI 查询并落库（fire-and-forget 场景，错误落 status=failed）。
 * @param {number} recordId
 * @param {string} platform
 * @param {string} question
 */
async function processAIQuery(recordId, platform, question) {
  try {
    const aiResult = await AIPlatformService.queryPlatform(platform, question);

    if (!aiResult.success) {
      await QuestionRecord.update(
        { status: 'failed', error_message: SAFE_PLATFORM_FAILURE_MESSAGE },
        { where: { id: recordId } }
      );
      return;
    }

    const originalText = ResultParserService.extractResponseText(aiResult.data);
    if (!String(originalText || '').trim()) {
      await QuestionRecord.update(
        { status: 'failed', error_message: '监测平台返回内容为空' },
        { where: { id: recordId } }
      );
      return;
    }
    await ResultDetail.create({
      question_record_id: recordId,
      ai_response_original: originalText,
      parsing_status: 'completed'
    });

    const rec = await QuestionRecord.findByPk(recordId);
    const brandKeywordsArr = typeof rec?.brand_keywords === 'string'
      ? rec.brand_keywords.split(/[,，]/).map(s => s.trim()).filter(Boolean)
      : Array.isArray(rec?.brand_keywords) ? rec.brand_keywords : [];
    await ProjectRecordFinalizationService.finalize({
      record: rec,
      responseText: originalText,
      aiResponse: aiResult.data,
      keywords: brandKeywordsArr
    });
  } catch (error) {
    console.error(`处理AI查询失败 (recordId: ${recordId}):`, error);
    await QuestionRecord.update(
      { status: 'failed', error_message: SAFE_PLATFORM_FAILURE_MESSAGE },
      { where: { id: recordId } }
    );
  }
}

module.exports = {
  SAFE_PLATFORM_FAILURE_MESSAGE,
  resolveProjectContext,
  saveCompletedDetectionResult,
  processAIQuery,
};
