function normalizeKeywords(keywords) {
  const seen = new Set();
  return (Array.isArray(keywords) ? keywords : [])
    .map((keyword) => String(keyword || '').trim())
    .filter(Boolean)
    .filter((keyword) => {
      const key = keyword.toLowerCase();
      if (seen.has(key)) return false;
      seen.add(key);
      return true;
    });
}

function termMatches(text, term, englishWordBoundary = true) {
  const source = typeof text === 'string' ? text : String(text || '');
  const keyword = String(term || '').trim();
  if (!source || !keyword) return [];

  const escaped = keyword.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
  const useBoundary = englishWordBoundary && /^[A-Za-z0-9][A-Za-z0-9\s._-]*$/.test(keyword);
  const pattern = useBoundary ? `(^|[^A-Za-z0-9])(${escaped})(?=$|[^A-Za-z0-9])` : escaped;
  const re = new RegExp(pattern, 'gi');
  const matches = [];

  for (const match of source.matchAll(re)) {
    const start = useBoundary && match[1] ? match.index + match[1].length : match.index;
    const matchedText = useBoundary ? match[2] : match[0];
    matches.push({ start, end: start + String(matchedText || '').length });
  }

  return matches;
}

function compactTerm(term) {
  return String(term || '').toLowerCase().replace(/[\s._-]+/g, '');
}

function termVariants(term) {
  const value = String(term || '').trim();
  const compact = compactTerm(value);
  if (compact && compact !== value.toLowerCase() && compact.length >= 3) {
    return [value, compact];
  }
  return [value];
}

function overlaps(a, b) {
  return a.start < b.end && b.start < a.end;
}

function keywordPattern(keyword, englishWordBoundary = true) {
  const escaped = String(keyword || '').replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
  const useBoundary = englishWordBoundary && /^[A-Za-z0-9][A-Za-z0-9\s._-]*$/.test(String(keyword || ''));
  return useBoundary ? `(^|[^A-Za-z0-9])(${escaped})(?=$|[^A-Za-z0-9])` : escaped;
}

function buildKeywordRegex(keywords, englishWordBoundary = true) {
  const patterns = normalizeKeywords(keywords)
    .flatMap((keyword) => termVariants(keyword))
    .sort((a, b) => b.length - a.length || a.localeCompare(b, 'zh-Hans-CN'))
    .map((keyword) => {
      const escaped = String(keyword || '').replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
      const useBoundary = englishWordBoundary && /^[A-Za-z0-9][A-Za-z0-9\s._-]*$/.test(keyword);
      return useBoundary ? `(?<![A-Za-z0-9])${escaped}(?![A-Za-z0-9])` : escaped;
    });
  if (!patterns.length) return null;
  return new RegExp(patterns.join('|'), 'gi');
}

function countKeywordOccurrences(text, keywords, englishWordBoundary = true) {
  const ranges = normalizeKeywords(keywords).flatMap((keyword) => (
    termVariants(keyword)
      .flatMap((variant) => termMatches(text, variant, englishWordBoundary))
      .map((range) => ({ ...range, keyword }))
  ));
  const selected = [];

  ranges
    .sort((a, b) => a.start - b.start || (b.end - b.start) - (a.end - a.start))
    .forEach((range) => {
      if (!selected.some((item) => overlaps(item, range))) selected.push(range);
    });

  const counts = new Map();
  selected.forEach((range) => {
    counts.set(range.keyword, (counts.get(range.keyword) || 0) + 1);
  });

  return Array.from(counts.entries()).map(([keyword, count]) => ({ keyword, count }));
}

function normalizeStoredStats(stats) {
  return (Array.isArray(stats) ? stats : [])
    .map((item) => ({
      keyword: String(item?.keyword || '').trim(),
      count: Number(item?.count || 0)
    }))
    .filter((item) => item.keyword && item.count > 0);
}

/**
 * @param {{ text?: unknown, keywords?: unknown[], storedStats?: unknown[], englishWordBoundary?: boolean }} options
 */
function resolveKeywordStats({ text, keywords, storedStats, englishWordBoundary = true } = {}) {
  const source = typeof text === 'string' ? text : String(text || '');
  const normalizedKeywords = normalizeKeywords(keywords);
  if (source && normalizedKeywords.length) {
    return countKeywordOccurrences(source, normalizedKeywords, englishWordBoundary);
  }
  return normalizeStoredStats(storedStats);
}

module.exports = {
  buildKeywordRegex,
  countKeywordOccurrences,
  keywordPattern,
  normalizeStoredStats,
  compactTerm,
  normalizeKeywords,
  resolveKeywordStats,
  termVariants,
  termMatches,
  overlaps
};
