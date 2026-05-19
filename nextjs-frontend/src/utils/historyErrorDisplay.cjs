const SAFE_EMPTY_RESPONSE_MESSAGE = '监测平台返回内容为空';
const SAFE_METRIC_FAILURE_MESSAGE = '指标生成失败，请稍后重试';
const SAFE_PLATFORM_FAILURE_MESSAGE = '监测平台调用失败，请稍后重试';
const SAFE_PARSING_FAILURE_MESSAGE = '回答处理失败，请稍后重试';

function formatHistoryErrorMessage(value) {
  const text = String(value || '').trim();
  if (!text) return '-';
  if (text === SAFE_EMPTY_RESPONSE_MESSAGE || text === 'AI 平台返回内容为空') return SAFE_EMPTY_RESPONSE_MESSAGE;
  if (text === SAFE_METRIC_FAILURE_MESSAGE) return text;
  if (text === SAFE_PLATFORM_FAILURE_MESSAGE || text === 'AI 平台调用失败，请稍后重试') return SAFE_PLATFORM_FAILURE_MESSAGE;
  if (/指标生成失败/i.test(text)) return SAFE_METRIC_FAILURE_MESSAGE;
  if (/\b(401|403|api key|invalid|unauthorized|network|timeout|ECONN|ENOTFOUND)\b/i.test(text)) {
    return SAFE_PLATFORM_FAILURE_MESSAGE;
  }
  return text;
}

function formatHistoryParsingErrorMessage(value) {
  const text = String(value || '').trim();
  return text ? SAFE_PARSING_FAILURE_MESSAGE : '-';
}

module.exports = {
  formatHistoryErrorMessage,
  formatHistoryParsingErrorMessage
};
