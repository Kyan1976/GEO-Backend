function canSaveGeneratedPrompts({ projectId, suggestions, saving } = {}) {
  return Boolean(projectId) && Array.isArray(suggestions) && suggestions.length > 0 && !saving;
}

module.exports = {
  canSaveGeneratedPrompts
};
