import test from 'node:test';
import assert from 'node:assert/strict';

import {
  PROMPT_GENERATION_BATCH_SIZE,
  PROMPT_GENERATION_MAX_COUNT,
  PROMPT_GENERATION_MIN_COUNT
} from './promptGenerationLimits.cjs';

test('keeps prompt generation bulk limit above the DeepSeek batch size', () => {
  assert.equal(PROMPT_GENERATION_MIN_COUNT, 3);
  assert.equal(PROMPT_GENERATION_BATCH_SIZE, 20);
  assert.equal(PROMPT_GENERATION_MAX_COUNT, 100);
  assert.equal(PROMPT_GENERATION_MAX_COUNT > PROMPT_GENERATION_BATCH_SIZE, true);
});
