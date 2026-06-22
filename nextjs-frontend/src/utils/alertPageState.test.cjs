/* eslint-disable @typescript-eslint/no-require-imports */
const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');

const source = fs.readFileSync(path.resolve(__dirname, '../app/geo/alerts/page.tsx'), 'utf8');

test('alert rules page ignores stale async rule responses after project changes', () => {
  assert.match(source, /useRef/);
  assert.match(source, /const currentProjectIdRef = useRef\(null\)/);
  assert.match(source, /const rulesRequestRef = useRef\(0\)/);
  assert.match(source, /const invalidateRulesRequest = \(\) =>/);
  assert.match(source, /rulesRequestRef\.current \+= 1/);
  assert.match(source, /const requestId = rulesRequestRef\.current \+ 1/);
  assert.match(source, /rulesRequestRef\.current = requestId/);
  assert.match(source, /if \(!id\) \{[\s\S]*setRules\(\[\]\);[\s\S]*setRuleLoading\(false\);[\s\S]*return;/);
  assert.match(source, /setRules\(\[\]\)[\s\S]*setRuleLoading\(true\)/);
  assert.match(source, /if \(rulesRequestRef\.current === requestId\) setRules\(extractList\(res\)\)/);
  assert.match(source, /if \(rulesRequestRef\.current === requestId\) setRuleLoading\(false\)/);
});

test('alert rule mutations only refresh the current project rules', () => {
  assert.match(source, /currentProjectIdRef\.current = projectId/);
  assert.match(source, /const refreshRulesForProject = \(targetProjectId\) =>/);
  assert.match(source, /if \(currentProjectIdRef\.current !== targetProjectId\) return/);
  assert.match(source, /const mutationProjectId = projectId/);
  assert.match(source, /if \(currentProjectIdRef\.current !== mutationProjectId\) return/);
  assert.match(source, /refreshRulesForProject\(mutationProjectId\)/);
  assert.doesNotMatch(source, /message\.success\([\s\S]{0,200}fetchRules\(projectId\)/);
});

test('alert rules page closes stale rule editor when switching projects', () => {
  assert.match(source, /const handleProjectChange = \(nextProjectId\) =>/);
  assert.match(source, /setProjectId\(nextProjectId\)/);
  assert.match(source, /setModalOpen\(false\)/);
  assert.match(source, /setEditingRule\(null\)/);
  assert.match(source, /form\.resetFields\(\)/);
  assert.match(source, /onChange=\{handleProjectChange\}/);
});
