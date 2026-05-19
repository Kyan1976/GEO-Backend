import test from 'node:test';
import assert from 'node:assert/strict';

import { buildBrandKeywords } from './brandKeywords.cjs';

test('includes model-like brand product keywords without requiring the brand name', () => {
  const keywords = buildBrandKeywords({
    projectName: '米其林',
    aliases: ['Michelin'],
    primaryKeywords: ['Pilot Sport 5', '静音轮胎']
  });

  assert.deepEqual(keywords, ['米其林', 'Michelin', 'Pilot Sport 5']);
});

test('deduplicates project and row brand keywords case-insensitively', () => {
  const keywords = buildBrandKeywords({
    rowBrand: 'Goodie AI',
    projectName: 'Goodie AI',
    aliases: ['goodie ai'],
    primaryKeywords: ['GoodieAI GEO'],
    rowBrandKeywords: 'GoodieAI GEO,GoodieAI GEO'
  });

  assert.deepEqual(keywords, ['Goodie AI', 'GoodieAI GEO']);
});
