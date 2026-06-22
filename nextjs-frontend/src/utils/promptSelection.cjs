function shouldResetPromptSelection(previousProjectId, nextProjectId) {
  if (previousProjectId == null || nextProjectId == null) return false;
  return String(previousProjectId) !== String(nextProjectId);
}

function shouldClearGeneratedPromptSuggestions(previousProjectId, nextProjectId) {
  if (previousProjectId == null) return false;
  return String(previousProjectId) !== String(nextProjectId);
}

function shouldResetPromptListFilters(previousProjectId, nextProjectId) {
  if (previousProjectId == null) return false;
  return String(previousProjectId) !== String(nextProjectId);
}

function getSelectablePromptProjects(projects) {
  if (!Array.isArray(projects)) return [];
  return projects.filter((item) => item && item.status !== 'archived');
}

function resolveSelectedPromptProjectId(projects, previousProjectId) {
  const selectableProjects = getSelectablePromptProjects(projects);
  if (previousProjectId != null) {
    const selected = selectableProjects.find((item) => String(item.id) === String(previousProjectId));
    if (selected) return selected.id;
  }
  return selectableProjects[0]?.id || null;
}

module.exports = {
  getSelectablePromptProjects,
  resolveSelectedPromptProjectId,
  shouldClearGeneratedPromptSuggestions,
  shouldResetPromptListFilters,
  shouldResetPromptSelection,
};
