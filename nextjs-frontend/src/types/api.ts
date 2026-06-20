/**
 * 后端 API 响应与实体的统一类型定义。
 *
 * 后端统一响应格式: { success: boolean, message?: string, data?: T }
 * 各页面的 res?.data?.data 解包后即为这里的实体类型。
 */

// ── 通用响应包装 ──
export interface ApiResponse<T = unknown> {
  success: boolean;
  message?: string;
  data?: T;
}

export interface PaginatedData<T> {
  items?: T[];
  total?: number;
  page?: number;
  pageSize?: number;
  total_pages?: number;
  records?: T[];
}

// ── 用户 ──
export type UserRole = 'admin' | 'user';
export type MembershipLevel = 'free' | 'pro' | 'enterprise';
export type UserStatus = 'active' | 'inactive';

export interface User {
  id: number;
  username: string;
  email: string;
  role: UserRole;
  membership_level: MembershipLevel;
  membership_expires_at: string | null;
  status: UserStatus;
  last_login: string | null;
  created_at?: string;
  quota_summary?: QuotaSummary;
}

export interface QuotaSummary {
  detection?: { used: number; limit: number };
  [feature: string]: { used: number; limit: number } | undefined;
}

export interface LoginResponse {
  token: string;
  user: {
    id: number;
    username: string;
    email: string;
    role: UserRole;
    last_login: string | null;
  };
}

// ── 品牌项目 ──
export type ProjectStatus = 'active' | 'archived';
export type Platform = 'doubao' | 'deepseek' | string;

export interface BrandProject {
  id: number;
  user_id: number;
  name: string;
  aliases: string[];
  website?: string | null;
  industry?: string | null;
  primary_keywords: string[];
  platforms: Platform[];
  monitoring_enabled: boolean;
  monitoring_time: string;
  monitoring_last_run_at?: string | null;
  monitoring_next_run_at?: string | null;
  status: ProjectStatus;
  competitors?: BrandCompetitor[];
  createdAt?: string;
  updatedAt?: string;
}

export interface BrandCompetitor {
  id?: number;
  project_id?: number;
  name: string;
  aliases?: string[];
  website?: string | null;
  [key: string]: unknown;
}

// ── 检测记录 ──
export interface QuestionRecord {
  id: number;
  user_id: number;
  project_id?: number;
  platform: string;
  question: string;
  brand?: string;
  response_text?: string;
  brand_mentioned?: boolean;
  sentiment?: SentimentLabel;
  created_at: string;
  resultDetail?: { ai_response_original?: unknown; parsing_status?: string };
  user?: { id: number; username: string; email: string };
  [key: string]: unknown;
}

export type SentimentLabel = 'positive' | 'neutral' | 'negative' | string;

// ── 可见度指标 ──
export interface VisibilityMetric {
  id?: number;
  project_id?: number;
  category?: string;
  mention_rate?: number;
  share_of_voice?: number;
  citation_rate?: number;
  sentiment_breakdown?: Record<string, number>;
  [key: string]: unknown;
}

// ── 引用来源 ──
export interface CitationSource {
  id?: number;
  domain?: string;
  url?: string;
  platform?: string;
  source_type?: 'self' | 'competitor' | 'third_party' | string;
  count?: number;
  [key: string]: unknown;
}

// ── 提示词 ──
export interface TrackedPrompt {
  id: number;
  group_id?: number;
  content: string;
  platform?: string;
  category?: string;
  enabled?: boolean;
  [key: string]: unknown;
}

export interface PromptGroup {
  id: number;
  name: string;
  [key: string]: unknown;
}

// ── 报告快照 ──
export interface ReportSnapshot {
  id: number;
  project_id?: number;
  title?: string;
  content?: string;
  period?: string;
  created_at: string;
  [key: string]: unknown;
}

// ── 告警规则 ──
export interface AlertRule {
  id: number;
  project_id?: number;
  metric?: string;
  operator?: string;
  threshold?: number;
  enabled?: boolean;
  [key: string]: unknown;
}

// ── 会员套餐 ──
export interface MembershipPlan {
  id: number;
  code: string;
  name: string;
  price?: number;
  features?: Record<string, number>;
  [key: string]: unknown;
}

// ── 系统设置 ──
export interface Setting {
  id?: number;
  key: string;
  value: string;
  [key: string]: unknown;
}

// ── 仪表盘统计 ──
export interface DashboardOverview {
  total_projects?: number;
  active_projects?: number;
  total_detections?: number;
  total_mentions?: number;
  avg_visibility?: number;
  [key: string]: unknown;
}

// ── React 组件 Props 占位（用于移除 ts-nocheck 后的函数组件）──
// eslint-disable-next-line @typescript-eslint/no-explicit-any
export type AnyRecord = Record<string, any>;
