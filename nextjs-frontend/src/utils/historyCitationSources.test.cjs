/* eslint-disable @typescript-eslint/no-require-imports */
const test = require('node:test');
const assert = require('node:assert/strict');

const { normalizeHistoryCitationSources } = require('./historyCitationSources.cjs');

test('normalizes citation sources for prompt history display', () => {
  assert.deepEqual(normalizeHistoryCitationSources([
    { url: ' https://brand.cn/a ', domain: 'brand.cn', owned: true },
    { url: '', domain: 'competitor.cn', competitor_owned: true },
    { url: 'https://media.cn/post', domain: '' },
    { url: 'https://media.cn/post', domain: 'media.cn' },
    null
  ]), [
    { url: 'https://brand.cn/a', domain: 'brand.cn', source_type: '自有来源' },
    { url: '', domain: 'competitor.cn', source_type: '竞品来源' },
    { url: 'https://media.cn/post', domain: 'media.cn', source_type: '第三方来源' }
  ]);
});

test('returns an empty list when history has no citation sources', () => {
  assert.deepEqual(normalizeHistoryCitationSources(null), []);
});
