/* eslint-disable @typescript-eslint/no-require-imports */
const test = require('node:test');
const assert = require('node:assert/strict');

const { canSaveGeneratedPrompts } = require('./generatedPromptSaveState.cjs');

test('allows saving generated prompts only when project and suggestions exist', () => {
  assert.equal(canSaveGeneratedPrompts({ projectId: 1, suggestions: [{ question: '静音轮胎怎么选' }], saving: false }), true);
  assert.equal(canSaveGeneratedPrompts({ projectId: null, suggestions: [{ question: '静音轮胎怎么选' }], saving: false }), false);
  assert.equal(canSaveGeneratedPrompts({ projectId: 1, suggestions: [], saving: false }), false);
  assert.equal(canSaveGeneratedPrompts({ projectId: 1, suggestions: [{ question: '静音轮胎怎么选' }], saving: true }), false);
});
