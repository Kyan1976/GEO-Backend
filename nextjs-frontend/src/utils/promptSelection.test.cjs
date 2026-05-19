/* eslint-disable @typescript-eslint/no-require-imports */
const test = require('node:test');
const assert = require('node:assert/strict');

const {
  getSelectablePromptProjects,
  resolveSelectedPromptProjectId,
  shouldClearGeneratedPromptSuggestions,
  shouldResetPromptListFilters,
  shouldResetPromptSelection
} = require('./promptSelection.cjs');

test('keeps prompt selection when the project is unchanged', () => {
  assert.equal(shouldResetPromptSelection(1, 1), false);
  assert.equal(shouldResetPromptSelection('1', 1), false);
});

test('resets prompt selection when switching projects', () => {
  assert.equal(shouldResetPromptSelection(1, 2), true);
});

test('clears generated suggestions when prompt project context changes', () => {
  assert.equal(shouldClearGeneratedPromptSuggestions(null, 1), false);
  assert.equal(shouldClearGeneratedPromptSuggestions(1, 1), false);
  assert.equal(shouldClearGeneratedPromptSuggestions('1', 1), false);
  assert.equal(shouldClearGeneratedPromptSuggestions(1, 2), true);
  assert.equal(shouldClearGeneratedPromptSuggestions(1, null), true);
});

test('resets prompt list filters when switching prompt projects', () => {
  assert.equal(shouldResetPromptListFilters(null, 1), false);
  assert.equal(shouldResetPromptListFilters(1, 1), false);
  assert.equal(shouldResetPromptListFilters('1', 1), false);
  assert.equal(shouldResetPromptListFilters(1, 2), true);
  assert.equal(shouldResetPromptListFilters(1, null), true);
});

test('does not reset prompt selection before a project is selected', () => {
  assert.equal(shouldResetPromptSelection(null, 1), false);
  assert.equal(shouldResetPromptSelection(1, null), false);
});

test('only active projects are selectable in prompt management', () => {
  const projects = [
    { id: 1, name: '旧项目', status: 'archived' },
    { id: 2, name: '新项目', status: 'active' },
    { id: 3, name: '默认项目' },
  ];

  assert.deepEqual(getSelectablePromptProjects(projects).map((item) => item.id), [2, 3]);
});

test('moves prompt management selection away from archived projects', () => {
  const projects = [
    { id: 1, name: '旧项目', status: 'archived' },
    { id: 2, name: '新项目', status: 'active' },
  ];

  assert.equal(resolveSelectedPromptProjectId(projects, 1), 2);
  assert.equal(resolveSelectedPromptProjectId(projects, 2), 2);
  assert.equal(resolveSelectedPromptProjectId([{ id: 1, status: 'archived' }], 1), null);
});

test('prompt project selection returns the selectable project id using the list value type', () => {
  const projects = [
    { id: '1', status: 'active' },
    { id: '2', status: 'active' }
  ];

  assert.equal(resolveSelectedPromptProjectId(projects, 2), '2');
});
