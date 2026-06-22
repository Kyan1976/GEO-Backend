/* eslint-disable @typescript-eslint/no-require-imports */
const test = require('node:test');
const assert = require('node:assert/strict');

const {
  hasEffectiveDashboardMetrics,
  shouldRenderMetricChart
} = require('./dashboardChartState.cjs');

test('treats calendar-filled trend rows as empty when no effective checks exist', () => {
  const chartRows = [
    { date: '2026-05-17', type: '品牌提及率', value: 0 }
  ];

  assert.equal(hasEffectiveDashboardMetrics({ total_checks: 0 }), false);
  assert.equal(shouldRenderMetricChart({ total_checks: 0 }, chartRows), false);
});

test('renders metric charts only when the dashboard has effective checks and chart rows', () => {
  assert.equal(shouldRenderMetricChart({ total_checks: 2 }, [{ value: 50 }]), true);
  assert.equal(shouldRenderMetricChart({ total_checks: 2 }, []), false);
});
