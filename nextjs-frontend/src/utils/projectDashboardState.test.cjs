/* eslint-disable @typescript-eslint/no-require-imports */
const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');

const source = fs.readFileSync(path.resolve(__dirname, '../app/geo/project-dashboard/page.tsx'), 'utf8');

test('project dashboard ignores stale async dashboard responses after project or period changes', () => {
  assert.match(source, /useRef/);
  assert.match(source, /const dashboardRequestRef = useRef\(0\)/);
  assert.match(source, /const invalidateDashboardRequest = \(\) =>/);
  assert.match(source, /dashboardRequestRef\.current \+= 1/);
  assert.match(source, /const handleProjectChange = \(value\) =>/);
  assert.match(source, /const handleDaysChange = \(value\) =>/);
  assert.match(source, /onChange=\{handleProjectChange\}/);
  assert.match(source, /onChange=\{handleDaysChange\}/);
  assert.match(source, /const requestId = dashboardRequestRef\.current \+ 1/);
  assert.match(source, /dashboardRequestRef\.current = requestId/);
  assert.match(source, /if \(!id\) \{[\s\S]*setDashboard\(null\);[\s\S]*setDashboardLoading\(false\);[\s\S]*return;/);
  assert.match(source, /setDashboard\(null\)[\s\S]*setDashboardLoading\(true\)/);
  assert.match(source, /if \(dashboardRequestRef\.current === requestId\) setDashboard\(res\?\.data\?\.data \|\| null\)/);
  assert.match(source, /if \(dashboardRequestRef\.current === requestId\) setDashboardLoading\(false\)/);
});

test('project dashboard uses shared active-project selection rules', () => {
  assert.match(source, /getSelectableProjects/);
  assert.match(source, /resolveSelectedProjectId/);
  assert.doesNotMatch(source, /filter\(\(item\) => item\?\.status !== 'archived'\)/);
});

test('project dashboard recent metrics expose prompt question context', () => {
  assert.match(source, /title:\s*'问题'/);
  assert.match(source, /row\?\.prompt\?\.question\s*\|\|\s*row\?\.questionRecord\?\.question/);
});

test('project dashboard competitor table shows visibility score context', () => {
  assert.match(source, /title:\s*'可见度得分'/);
  assert.match(source, /dataIndex:\s*'visibility_score'/);
});
