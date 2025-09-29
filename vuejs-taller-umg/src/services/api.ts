
import axios, { AxiosRequestHeaders } from 'axios'

function computeBaseUrl(): string {
  const envUrl = (import.meta.env.VITE_API_URL as string | undefined)?.trim()
  if (envUrl) return envUrl
  const { protocol, hostname } = window.location
  const port = '5000' 
  return `${protocol}//${hostname}:${port}/api`
}


function extractPathFromConfig(baseURL: string | undefined, url: string | undefined): string {
  const base = baseURL ?? ''
  const raw = url ?? ''

  try {
    const u = new URL(raw, base || window.location.origin)
    return u.pathname.replace(/\/+$/, '')
  } catch {

    const path = (base + raw).replace(/^https?:\/\/[^/]+/, '')
    return path.split('?')[0].replace(/\/+$/, '')
  }
}

const api = axios.create({
  baseURL: computeBaseUrl(),
})


api.interceptors.request.use((config) => {

  const headers = (config.headers ?? {}) as AxiosRequestHeaders
  headers['Accept'] = 'application/json'
  headers['Content-Type'] = headers['Content-Type'] ?? 'application/json'
  config.headers = headers

  const path = extractPathFromConfig(config.baseURL, config.url)

  const noAuth = new Set<string>([
    '/api/login',
    '/api/register',

    '/login',
    '/register',
  ])

  if (!noAuth.has(path)) {
    const token = localStorage.getItem('token')
    if (!token) {

      window.location.href = '/login'

      return Promise.reject(new axios.Cancel('No token, redirecting to /login'))
    }
    headers['Authorization'] = `Bearer ${token}`
  } else {

    delete headers['Authorization']
  }

  return config
})


api.interceptors.response.use(
  (r) => r,
  (err) => {
    if (err?.response?.status === 401) {
      localStorage.removeItem('token')
      window.location.href = '/login'
    }
    return Promise.reject(err)
  }
)

export default api
