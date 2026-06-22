const test = require('node:test');
const assert = require('node:assert/strict');

const { getApiRunResultData } = require('./apiRunResult.cjs');

test('reads run result data from non-2xx api responses', () => {
  const result = getApiRunResultData({
    response: {
      data: {
        success: false,
        message: '分析全部失败',
        data: { completed: 0, failed: 2, attempted: 2 }
      }
    }
  });

  assert.deepEqual(result, { completed: 0, failed: 2, attempted: 2 });
});

test('ignores api errors without object run result data', () => {
  assert.equal(getApiRunResultData({ response: { data: { data: null } } }), null);
  assert.equal(getApiRunResultData({ response: { data: { data: '分析失败' } } }), null);
  assert.equal(getApiRunResultData(null), null);
});
