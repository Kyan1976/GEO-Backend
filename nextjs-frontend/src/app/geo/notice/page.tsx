'use client';

import React, { useCallback, useEffect, useMemo, useState } from 'react';
import { Card, Typography, Alert, Space, Button, Skeleton } from 'antd';
import axios from 'axios';

export default function GeoNoticePage() {
  const [loading, setLoading] = useState(false);
  const [notice, setNotice] = useState('');
  const [updatedAt, setUpdatedAt] = useState<string | null>(null);

  const fetchNotice = useCallback(async () => {
    setLoading(true);
    try {
      // 统一走相对路径（经 next.config rewrites 代理到后端），避免硬编码端口（审计 N5）
      const res = await axios.get('/api/settings/notice');
      const data = res?.data?.data || {};
      setNotice(String(data.notice || ''));
      setUpdatedAt(data.updated_at || null);
    } catch {
      // no-op
    } finally { setLoading(false); }
  }, [API_BASE]);

  useEffect(() => { fetchNotice(); }, [fetchNotice]);

  const formatTime = (ts: string | null) => {
    if (!ts) return '';
    try { return new Date(ts).toLocaleString(); } catch { return String(ts); }
  };

  return (
    <Space orientation="vertical" size="middle">
      <Card title="系统通知" extra={<Button onClick={fetchNotice}>刷新</Button>}>
        {loading ? (
          <Skeleton active paragraph={{ rows: 3 }} />
        ) : (
          notice ? (
            <Typography.Paragraph style={{ whiteSpace: 'pre-wrap' }}>
              {notice}
            </Typography.Paragraph>
          ) : (
            <Alert type="info" title="当前暂无系统通知" showIcon />
          )
        )}
        {updatedAt && (
          <Typography.Text type="secondary">最近更新：{formatTime(updatedAt)}</Typography.Text>
        )}
      </Card>
    </Space>
  );
}
