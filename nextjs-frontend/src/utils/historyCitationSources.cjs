function normalizeDomain(value) {
  const text = String(value || '').trim();
  if (!text) return '';
  try {
    return new URL(text.startsWith('http') ? text : `https://${text}`).hostname.toLowerCase().replace(/^www\./, '');
  } catch {
    return text.replace(/^https?:\/\//i, '').split('/')[0].toLowerCase().replace(/^www\./, '');
  }
}

function sourceType(source) {
  if (source?.owned) return '自有来源';
  if (source?.competitor_owned) return '竞品来源';
  return source?.source_type || '第三方来源';
}

function normalizeHistoryCitationSources(sources) {
  const seen = new Set();
  return (Array.isArray(sources) ? sources : [])
    .map((source) => {
      const url = String(source?.url || '').trim();
      const domain = normalizeDomain(source?.domain || url);
      if (!domain) return null;
      return {
        url,
        domain,
        source_type: sourceType(source)
      };
    })
    .filter(Boolean)
    .filter((source) => {
      const key = source.url ? `url:${source.url.toLowerCase()}` : `domain:${source.domain}`;
      if (seen.has(key)) return false;
      seen.add(key);
      return true;
    });
}

module.exports = {
  normalizeHistoryCitationSources
};
