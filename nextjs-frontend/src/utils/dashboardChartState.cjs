function hasEffectiveDashboardMetrics(summary) {
  return Number(summary?.total_checks || 0) > 0;
}

function shouldRenderMetricChart(summary, chartRows) {
  return hasEffectiveDashboardMetrics(summary) && Array.isArray(chartRows) && chartRows.length > 0;
}

module.exports = {
  hasEffectiveDashboardMetrics,
  shouldRenderMetricChart
};
