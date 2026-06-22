function getSelectableProjects(projects) {
  return (Array.isArray(projects) ? projects : []).filter((item) => item?.status !== 'archived');
}

function resolveSelectedProjectId(projects, currentId, preferredId) {
  const rows = getSelectableProjects(projects);
  const current = rows.find((item) => String(item.id) === String(currentId));
  if (currentId && current) return current.id;
  const preferred = rows.find((item) => String(item.id) === String(preferredId));
  if (preferredId && preferred) return preferred.id;
  return rows[0]?.id;
}

module.exports = {
  getSelectableProjects,
  resolveSelectedProjectId
};
