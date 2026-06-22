/* eslint-disable @typescript-eslint/no-require-imports */
const test = require('node:test');
const assert = require('node:assert/strict');

const {
  formatHistoryErrorMessage,
  formatHistoryParsingErrorMessage
} = require('./historyErrorDisplay.cjs');

test('keeps safe business history error messages unchanged', () => {
  assert.equal(formatHistoryErrorMessage('监测平台返回内容为空'), '监测平台返回内容为空');
  assert.equal(formatHistoryErrorMessage('AI 平台返回内容为空'), '监测平台返回内容为空');
  assert.equal(formatHistoryErrorMessage('指标生成失败，请稍后重试'), '指标生成失败，请稍后重试');
  assert.equal(formatHistoryErrorMessage('AI 平台调用失败，请稍后重试'), '监测平台调用失败，请稍后重试');
  assert.equal(formatHistoryErrorMessage('监测平台调用失败，请稍后重试'), '监测平台调用失败，请稍后重试');
});

test('hides internal platform and storage errors from history display', () => {
  assert.equal(formatHistoryErrorMessage('指标生成失败: metric write failed'), '指标生成失败，请稍后重试');
  assert.equal(formatHistoryErrorMessage('[deepseek] 401 invalid api key'), '监测平台调用失败，请稍后重试');
  assert.equal(formatHistoryErrorMessage('network down'), '监测平台调用失败，请稍后重试');
});

test('returns a neutral placeholder for blank history errors', () => {
  assert.equal(formatHistoryErrorMessage(''), '-');
  assert.equal(formatHistoryErrorMessage(null), '-');
});

test('hides internal parsing errors from history display', () => {
  assert.equal(formatHistoryParsingErrorMessage('Unexpected token < in JSON at position 0'), '回答处理失败，请稍后重试');
  assert.equal(formatHistoryParsingErrorMessage('Cannot read properties of undefined'), '回答处理失败，请稍后重试');
  assert.equal(formatHistoryParsingErrorMessage(''), '-');
});
