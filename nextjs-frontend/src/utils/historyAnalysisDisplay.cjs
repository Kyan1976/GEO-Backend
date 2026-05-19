function getBrandSentimentDisplay(metric = {}) {
  if (!metric.brand_mentioned) {
    return { sentimentLabel: '-', sentimentColor: 'default', sentimentReason: '', sentimentRiskTerms: [] };
  }

  const sentiment = metric.sentiment || 'neutral';
  const sentimentMap = {
    positive: { label: '正向', color: 'green' },
    negative: { label: '负向', color: 'red' },
    neutral: { label: '中性', color: 'default' }
  };
  const sentimentDisplay = sentimentMap[sentiment] || sentimentMap.neutral;
  const riskTerms = Array.isArray(metric.sentiment_risk_terms)
    ? metric.sentiment_risk_terms.map((item) => sanitizeSentimentRiskTerm(item)).filter(Boolean)
    : [];
  return {
    sentimentLabel: sentimentDisplay.label,
    sentimentColor: sentimentDisplay.color,
    sentimentReason: sanitizeSentimentReason(metric.sentiment_reason),
    sentimentRiskTerms: riskTerms
  };
}

function sanitizeSentimentText(value) {
  return String(value || '')
    .replace(/DeepSeek/ig, '')
    .replace(/API\s*Key/ig, '')
    .replace(/API/ig, '')
    .replace(/\s+/g, '')
    .trim();
}

function sanitizeSentimentReason(value) {
  const cleaned = sanitizeSentimentText(value).split(/[，,；;。.!！?？]/)[0] || '';
  return cleaned.slice(0, 20);
}

function sanitizeSentimentRiskTerm(value) {
  return sanitizeSentimentText(value).slice(0, 14);
}

function getHistoryAnalysisDisplay(row = {}) {
  if (row.status !== 'completed') {
    return {
      sov: '-',
      sentimentLabel: '-',
      sentimentColor: 'default',
      sentimentReason: '',
      sentimentRiskTerms: [],
      brandMentionLabel: '-',
      brandMentionColor: 'default'
    };
  }

  const metric = row.visibilityMetric || {};
  const sentimentDisplay = getBrandSentimentDisplay(metric);
  const mentioned = !!metric.brand_mentioned;
  return {
    sov: `${Number(metric.share_of_voice || 0).toFixed(2)}%`,
    sentimentLabel: sentimentDisplay.sentimentLabel,
    sentimentColor: sentimentDisplay.sentimentColor,
    sentimentReason: sentimentDisplay.sentimentReason,
    sentimentRiskTerms: sentimentDisplay.sentimentRiskTerms,
    brandMentionLabel: mentioned ? '已提及' : '未提及',
    brandMentionColor: mentioned ? 'green' : 'default'
  };
}

module.exports = { getBrandSentimentDisplay, getHistoryAnalysisDisplay };
