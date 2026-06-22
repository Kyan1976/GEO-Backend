/* eslint-disable @typescript-eslint/no-require-imports */
const test = require('node:test');
const assert = require('node:assert/strict');

const { hasSourceContextValues, normalizeSourceContextValues } = require('./sourceDisplay.cjs');

test('normalizes source context values for display', () => {
  assert.deepEqual(normalizeSourceContextValues(['购买决策', '', '竞品对比', '购买决策']), ['购买决策', '竞品对比']);
  assert.deepEqual(normalizeSourceContextValues('购买决策'), []);
});

test('detects whether a source row has context values', () => {
  assert.equal(hasSourceContextValues(['购买决策']), true);
  assert.equal(hasSourceContextValues([]), false);
  assert.equal(hasSourceContextValues(null), false);
});
