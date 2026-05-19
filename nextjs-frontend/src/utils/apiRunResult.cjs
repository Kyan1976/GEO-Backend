function getApiRunResultData(error) {
  const data = error?.response?.data?.data;
  if (!data || typeof data !== 'object' || Array.isArray(data)) return null;
  return data;
}

module.exports = { getApiRunResultData };
