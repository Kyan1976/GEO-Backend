import test from 'node:test';
import assert from 'node:assert/strict';
import { getBrandSentimentDisplay, getHistoryAnalysisDisplay } from './historyAnalysisDisplay.cjs';

test('hides analysis metrics for failed prompt history records', () => {
  assert.deepEqual(getHistoryAnalysisDisplay({ status: 'failed' }), {
    sov: '-',
    sentimentLabel: '-',
    sentimentColor: 'default',
    sentimentReason: '',
    sentimentRiskTerms: [],
    brandMentionLabel: '-',
    brandMentionColor: 'default'
  });
});

test('formats completed prompt history analysis metrics', () => {
  assert.deepEqual(getHistoryAnalysisDisplay({
    status: 'completed',
    visibilityMetric: {
      share_of_voice: 37.5,
      sentiment: 'negative',
      sentiment_reason: '价格和售后风险',
      sentiment_risk_terms: ['价格高', '售后'],
      brand_mentioned: true
    }
  }), {
    sov: '37.50%',
    sentimentLabel: '负向',
    sentimentColor: 'red',
    sentimentReason: '价格和售后风险',
    sentimentRiskTerms: ['价格高', '售后'],
    brandMentionLabel: '已提及',
    brandMentionColor: 'green'
  });
});

test('does not show sentiment when the brand was not mentioned', () => {
  assert.deepEqual(getHistoryAnalysisDisplay({
    status: 'completed',
    visibilityMetric: {
      share_of_voice: 0,
      sentiment: 'neutral',
      brand_mentioned: false
    }
  }), {
    sov: '0.00%',
    sentimentLabel: '-',
    sentimentColor: 'default',
    sentimentReason: '',
    sentimentRiskTerms: [],
    brandMentionLabel: '未提及',
    brandMentionColor: 'default'
  });
});

test('formats brand sentiment only for mentioned metrics', () => {
  assert.deepEqual(getBrandSentimentDisplay({ sentiment: 'positive', brand_mentioned: true }), {
    sentimentLabel: '正向',
    sentimentColor: 'green',
    sentimentReason: '',
    sentimentRiskTerms: []
  });
  assert.deepEqual(getBrandSentimentDisplay({ sentiment: 'negative', brand_mentioned: false }), {
    sentimentLabel: '-',
    sentimentColor: 'default',
    sentimentReason: '',
    sentimentRiskTerms: []
  });
});

test('hides provider details from stored sentiment display fields', () => {
  const display = getBrandSentimentDisplay({
    sentiment: 'negative',
    brand_mentioned: true,
    sentiment_reason: 'DeepSeek API 判断价格和售后风险，需要继续观察',
    sentiment_risk_terms: ['DeepSeek API 价格高', 'API Key 配置异常', '售后慢']
  });

  assert.equal(display.sentimentLabel, '负向');
  assert.equal(display.sentimentReason, '判断价格和售后风险');
  assert.deepEqual(display.sentimentRiskTerms, ['价格高', '配置异常', '售后慢']);
  assert.doesNotMatch(`${display.sentimentReason} ${display.sentimentRiskTerms.join(' ')}`, /DeepSeek|API|Key/i);
});
