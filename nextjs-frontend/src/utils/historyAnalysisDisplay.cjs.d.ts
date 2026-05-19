export function getBrandSentimentDisplay(metric?: {
  sentiment?: string;
  brand_mentioned?: boolean;
  sentiment_reason?: string;
  sentiment_risk_terms?: string[];
}): {
  sentimentLabel: string;
  sentimentColor: string;
  sentimentReason: string;
  sentimentRiskTerms: string[];
};

export function getHistoryAnalysisDisplay(row?: {
  status?: string;
  visibilityMetric?: {
    share_of_voice?: number;
    sentiment?: string;
    brand_mentioned?: boolean;
    sentiment_reason?: string;
    sentiment_risk_terms?: string[];
  };
}): {
  sov: string;
  sentimentLabel: string;
  sentimentColor: string;
  sentimentReason: string;
  sentimentRiskTerms: string[];
  brandMentionLabel: string;
  brandMentionColor: string;
};
