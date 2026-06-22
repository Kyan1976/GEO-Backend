const test = require('node:test');
const assert = require('node:assert/strict');

const { getApiErrorMessage } = require('./apiErrorMessage.cjs');

test('uses backend api error messages when available', () => {
  assert.equal(
    getApiErrorMessage({ response: { data: { message: '已存在相同品牌项目' } } }, '保存失败'),
    '已存在相同品牌项目'
  );
});

test('falls back when backend api error messages are unavailable', () => {
  assert.equal(getApiErrorMessage({}, '保存失败'), '保存失败');
  assert.equal(getApiErrorMessage({ response: { data: { message: ' ' } } }, '保存失败'), '保存失败');
});
