function normalizeSourceContextValues(values) {
  if (!Array.isArray(values)) return [];
  const seen = new Set();
  return values
    .map((item) => String(item || '').trim())
    .filter(Boolean)
    .filter((item) => {
      if (seen.has(item)) return false;
      seen.add(item);
      return true;
    });
}

function hasSourceContextValues(values) {
  return normalizeSourceContextValues(values).length > 0;
}

module.exports = {
  hasSourceContextValues,
  normalizeSourceContextValues
};
