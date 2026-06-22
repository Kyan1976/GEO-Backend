import test from 'node:test';
import assert from 'node:assert/strict';

import { buildKeywordRegex, countKeywordOccurrences, resolveKeywordStats } from './keywordStats.cjs';

test('deduplicates overlapping keyword statistics and keeps the specific term', () => {
  const stats = countKeywordOccurrences('豆包大模型适合中文内容生产，DeepSeek 适合代码场景。', ['豆包', '豆包大模型']);

  assert.deepEqual(stats, [
    { keyword: '豆包大模型', count: 1 }
  ]);
});

test('keeps ASCII keyword word boundaries when counting keyword statistics', () => {
  const stats = countKeywordOccurrences('GoodieAI GEO helps Goodie teams, but GoodieAIPro is different.', ['GoodieAI', 'Goodie']);

  assert.deepEqual(stats, [
    { keyword: 'GoodieAI', count: 1 },
    { keyword: 'Goodie', count: 1 }
  ]);
});

test('counts compact keyword spellings without exposing compact keywords', () => {
  const stats = countKeywordOccurrences('GoodieAI 适合做品牌可见度监测。', ['Goodie AI']);

  assert.deepEqual(stats, [
    { keyword: 'Goodie AI', count: 1 }
  ]);
});

test('builds highlight regex that prefers longer overlapping keywords', () => {
  const regex = buildKeywordRegex(['豆包', '豆包大模型']);
  const match = '豆包大模型适合中文内容生产'.match(regex);

  assert.equal(match?.[0], '豆包大模型');
});

test('builds highlight regex without consuming ASCII keyword boundaries', () => {
  const regex = buildKeywordRegex(['GoodieAI']);
  const match = regex.exec('Use GoodieAI for GEO');

  assert.equal(match?.[0], 'GoodieAI');
});

test('builds highlight regex for compact keyword spellings', () => {
  const regex = buildKeywordRegex(['Goodie AI']);
  const match = regex.exec('Use GoodieAI for GEO');

  assert.equal(match?.[0], 'GoodieAI');
});

test('prefers recalculated keyword stats over stale stored overlapping stats', () => {
  const stats = resolveKeywordStats({
    text: '豆包大模型适合中文内容生产',
    keywords: ['豆包', '豆包大模型'],
    storedStats: [
      { keyword: '豆包', count: 1 },
      { keyword: '豆包大模型', count: 1 }
    ]
  });

  assert.deepEqual(stats, [
    { keyword: '豆包大模型', count: 1 }
  ]);
});

test('falls back to stored keyword stats when original response text is unavailable', () => {
  const stats = resolveKeywordStats({
    text: '',
    keywords: ['豆包'],
    storedStats: [
      { keyword: '豆包', count: 2 },
      { keyword: 'DeepSeek', count: 0 }
    ]
  });

  assert.deepEqual(stats, [
    { keyword: '豆包', count: 2 }
  ]);
});
