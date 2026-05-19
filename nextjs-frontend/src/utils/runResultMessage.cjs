function getRunResultNotice(data = {}) {
  if (data.status === 'queued' || Number(data.queued || 0) > 0) {
    const pending = Number(data.pending || data.queued || data.total || 0);
    return {
      type: 'success',
      text: `分析已加入队列：待处理 ${pending} 条`
    };
  }

  const completed = Number(data.completed || 0);
  const failed = Number(data.failed || 0);
  const type = completed === 0 && failed > 0
    ? 'error'
    : (failed > 0 ? 'warning' : 'success');
  const prefix = type === 'error' ? '分析全部失败' : '分析完成';
  return {
    type,
    text: `${prefix}：成功 ${completed} 条，失败 ${failed} 条`
  };
}

module.exports = { getRunResultNotice };
