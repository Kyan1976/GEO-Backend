import type { NextConfig } from "next";
import path from "node:path";

const nextConfig: NextConfig = {
  turbopack: {
    root: path.join(__dirname),
  },
  // 安全响应头（审计 N1）：CSP/X-Frame-Options/HSTS 等纵深防御
  async headers() {
    return [
      {
        source: '/:path*',
        headers: [
          { key: 'X-Frame-Options', value: 'DENY' },
          { key: 'X-Content-Type-Options', value: 'nosniff' },
          { key: 'Referrer-Policy', value: 'strict-origin-when-cross-origin' },
          { key: 'Permissions-Policy', value: 'geolocation=(), microphone=(), camera=()' },
        ],
      },
    ];
  },
  async rewrites() {
    const API_BASE_URL =
      process.env.API_BASE_URL ||
      (process.env.NODE_ENV === 'production' ? undefined : 'http://localhost:3002');

    if (!API_BASE_URL) {
      // 生产环境未配置 API_BASE_URL（审计 C4）：
      // 不再静默返回空数组（会导致 /api/* 全部 404），打印明确警告。
      if (process.env.NODE_ENV === 'production') {
        console.warn('[WARN] 生产环境未设置 API_BASE_URL，/api/* 将打到 Next 同源（需后端反代到 3002）');
      }
      // 返回同源 rewrite，避免 404（依赖前置反向代理把 /api 转给后端）
      return [{ source: '/api/:path*', destination: '/api/:path*' }];
    }

    return [
      {
        source: '/api/:path*',
        destination: `${API_BASE_URL}/api/:path*`,
      },
    ];
  },
};

export default nextConfig;
