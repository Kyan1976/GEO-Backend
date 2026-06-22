import test from 'node:test';
import assert from 'node:assert/strict';
import { getRunResultNotice } from './runResultMessage.cjs';

test('formats run result notices by severity', () => {
  assert.deepEqual(getRunResultNotice({ completed: 3, failed: 0 }), {
    type: 'success',
    text: '分析完成：成功 3 条，失败 0 条'
  });

  assert.deepEqual(getRunResultNotice({ completed: 2, failed: 1 }), {
    type: 'warning',
    text: '分析完成：成功 2 条，失败 1 条'
  });

  assert.deepEqual(getRunResultNotice({ completed: 0, failed: 2 }), {
    type: 'error',
    text: '分析全部失败：成功 0 条，失败 2 条'
  });
});

test('formats queued project run notices without claiming completion', () => {
  assert.deepEqual(getRunResultNotice({ status: 'queued', pending: 10, completed: 0, failed: 0 }), {
    type: 'success',
    text: '分析已加入队列：待处理 10 条'
  });
});
