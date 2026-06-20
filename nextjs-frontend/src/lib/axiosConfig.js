import axios from 'axios';

function normalizeApiBase(value) {
  const base = (value || '').trim().replace(/\/+$/, '');

  if (!base || base === '/api') {
    return '';
  }

  return base.endsWith('/api') ? base.slice(0, -4) : base;
}

const API_BASE = normalizeApiBase(
  process.env.NEXT_PUBLIC_API_BASE_URL ||
  process.env.NEXT_PUBLIC_API_URL ||
  ''
);

axios.defaults.baseURL = API_BASE;
axios.defaults.withCredentials = true;  // 携带 httpOnly cookie（审计 C4）

// 确保拦截器只注册一次
let interceptorsInitialized = false;

if (!interceptorsInitialized) {
  // 请求拦截器：自动添加token，并检查token是否即将过期
  axios.interceptors.request.use(config => {
    if (typeof window !== 'undefined') {
      const token = localStorage.getItem('agd_token');
      if (token) {
        config.headers.Authorization = `Bearer ${token}`;

        // 检查token是否即将过期（30分钟内）
        try {
          const payload = JSON.parse(atob(token.split('.')[1]));
          const expiresIn = payload.exp * 1000 - Date.now();
          const thirtyMinutes = 30 * 60 * 1000;
          const fiveMinutes = 5 * 60 * 1000;

          if (expiresIn < fiveMinutes && expiresIn > 0) {
            // 5分钟内过期：显示警告
            console.warn('Token将在5分钟内过期，请重新登录');
            // 可以触发自定义事件供UI监听
            if (typeof window !== 'undefined' && window.dispatchEvent) {
              window.dispatchEvent(new CustomEvent('token-expiring', {
                detail: { expiresIn, minutes: Math.ceil(expiresIn / 60000) }
              }));
            }
          } else if (expiresIn < thirtyMinutes && expiresIn > 0) {
            // 30分钟内过期：记录信息
            console.info(`Token将在${Math.ceil(expiresIn / 60000)}分钟后过期`);
          }
        } catch {
          // 解析token失败，忽略
        }
      }
    }
    return config;
  }, error => {
    return Promise.reject(error);
  });

  // 响应拦截器：统一处理401错误
  axios.interceptors.response.use(
    response => response,
    error => {
      if (error.response?.status === 401) {
        if (typeof window !== 'undefined') {
          localStorage.removeItem('agd_token');
          localStorage.removeItem('agd_user');
          localStorage.removeItem('agd_user_id');
          // 清除axios默认header
          delete axios.defaults.headers.common['Authorization'];
          // 重定向到登录页
          window.location.href = '/login';
        }
      }
      return Promise.reject(error);
    }
  );

  interceptorsInitialized = true;
}


// 辅助函数：检查token是否即将过期
export function shouldRefreshToken(token) {
  if (!token) return false;
  try {
    const payload = JSON.parse(atob(token.split('.')[1]));
    const expiresIn = payload.exp * 1000 - Date.now();
    // 如果token在30分钟内过期，则需要刷新
    return expiresIn < 30 * 60 * 1000;
  } catch {
    return false;
  }
}

// 辅助函数：获取当前token
export function getCurrentToken() {
  if (typeof window === 'undefined') return null;
  return localStorage.getItem('agd_token');
}

// 辅助函数：设置token（用于登录成功后的设置）
export function setAuthToken(token) {
  if (typeof window === 'undefined') return;
  if (token) {
    localStorage.setItem('agd_token', token);
    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
  } else {
    localStorage.removeItem('agd_token');
    delete axios.defaults.headers.common['Authorization'];
  }
}

// 辅助函数：清除认证信息（用于退出登录）
export function clearAuth() {
  if (typeof window === 'undefined') return;
  localStorage.removeItem('agd_token');
  localStorage.removeItem('agd_user');
  localStorage.removeItem('agd_user_id');
  delete axios.defaults.headers.common['Authorization'];
}

export default axios;

// 统一 API 封装（审计 M2/M3）：推荐新代码使用这些函数，
// 自动解包后端 { success, data } 结构，统一错误处理。
// 用法：import { apiGet } from '@/lib/axiosConfig';
//      const users = await apiGet('/api/users');
async function apiRequest(method, url, config) {
  try {
    const res = await axios({ method, url, ...config });
    // 后端统一返回 { success, data, message }，返回 data 层；失败抛出
    const body = res?.data;
    if (body && typeof body === 'object' && 'success' in body) {
      if (!body.success) {
        const msg = body.message || '请求失败';
        const err = new Error(msg);
        err.response = res;
        err.apiMessage = msg;
        throw err;
      }
      return body.data;
    }
    // 非标准结构，原样返回
    return body;
  } catch (err) {
    // 401 已由全局拦截器处理；其余错误向上抛，由调用方决定提示文案
    throw err;
  }
}

export const apiGet = (url, config) => apiRequest('get', url, config);
export const apiPost = (url, data, config) => apiRequest('post', url, { ...config, data });
export const apiPut = (url, data, config) => apiRequest('put', url, { ...config, data });
export const apiDelete = (url, config) => apiRequest('delete', url, config);

