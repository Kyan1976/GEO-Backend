/* eslint-disable @typescript-eslint/no-require-imports */
const test = require('node:test');
const assert = require('node:assert/strict');

const {
  getSelectableProjects,
  resolveSelectedProjectId
} = require('./projectSelection.cjs');

test('filters archived projects from selectable project lists', () => {
  assert.deepEqual(getSelectableProjects([
    { id: 1, status: 'active' },
    { id: 2, status: 'archived' },
    { id: 3 }
  ]).map((item) => item.id), [1, 3]);
});

test('moves selection away from projects that are no longer selectable', () => {
  const projects = [
    { id: 1, status: 'archived' },
    { id: 2, status: 'active' }
  ];

  assert.equal(resolveSelectedProjectId(projects, 1), 2);
  assert.equal(resolveSelectedProjectId(projects, 99), 2);
});

test('uses a preferred project only when it is selectable', () => {
  const projects = [
    { id: 1, status: 'active' },
    { id: 2, status: 'active' },
    { id: 3, status: 'archived' }
  ];

  assert.equal(resolveSelectedProjectId(projects, undefined, 2), 2);
  assert.equal(resolveSelectedProjectId(projects, undefined, 3), 1);
});

test('keeps project selection when ids differ only by string and number representation', () => {
  const projects = [
    { id: '1', status: 'active' },
    { id: '2', status: 'active' }
  ];

  assert.equal(resolveSelectedProjectId(projects, 2), '2');
  assert.equal(resolveSelectedProjectId(projects, undefined, 1), '1');
});
