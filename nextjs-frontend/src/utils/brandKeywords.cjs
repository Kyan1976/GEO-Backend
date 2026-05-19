function normalizeList(value) {
  if (Array.isArray(value)) return value;
  if (typeof value === 'string') return value.split(/[,，]/);
  return [];
}

function dedupeTerms(terms) {
  const seen = new Set();
  return (Array.isArray(terms) ? terms : [])
    .map((item) => String(item || '').trim())
    .filter(Boolean)
    .filter((item) => {
      const key = item.toLowerCase();
      if (seen.has(key)) return false;
      seen.add(key);
      return true;
    });
}

function isModelLikeProductTerm(term) {
  const value = String(term || '').trim();
  if (!value) return false;
  return /[A-Za-z]/.test(value) && /\d/.test(value);
}

function compactTerm(term) {
  return String(term || '').toLowerCase().replace(/[\s._-]+/g, '');
}

function buildBrandKeywords({ rowBrand, projectName, aliases, primaryKeywords, rowBrandKeywords } = {}) {
  const baseTerms = dedupeTerms([
    rowBrand,
    projectName,
    ...normalizeList(aliases)
  ]);
  const baseLower = baseTerms.map((item) => item.toLowerCase());
  const baseCompact = baseTerms.map((item) => compactTerm(item)).filter(Boolean);
  const brandProductTerms = [
    ...normalizeList(primaryKeywords),
    ...normalizeList(rowBrandKeywords)
  ]
    .map((item) => String(item || '').trim())
    .filter(Boolean)
    .filter((item) => {
      const lower = item.toLowerCase();
      const compact = compactTerm(item);
      return baseLower.some((base) => lower.includes(base))
        || baseCompact.some((base) => compact.includes(base))
        || isModelLikeProductTerm(item);
    });
  return dedupeTerms([...baseTerms, ...brandProductTerms]);
}

module.exports = {
  buildBrandKeywords,
  compactTerm,
  dedupeTerms,
  isModelLikeProductTerm,
  normalizeList
};
