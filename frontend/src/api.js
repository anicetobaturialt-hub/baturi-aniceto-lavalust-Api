import axios from 'axios'

const API_URL = (import.meta.env.VITE_API_URL || 'http://localhost:3000').replace(/\/$/, '')

const api = axios.create({ baseURL: `${API_URL}/api` })

// ---- token storage -------------------------------------------------------
export const tokens = {
  get access() { return localStorage.getItem('access_token') },
  get refresh() { return localStorage.getItem('refresh_token') },
  save(t) {
    localStorage.setItem('access_token', t.access_token)
    localStorage.setItem('refresh_token', t.refresh_token)
  },
  clear() {
    localStorage.removeItem('access_token')
    localStorage.removeItem('refresh_token')
    localStorage.removeItem('user')
  },
}

// ---- attach the access token to every request ------------------------------
api.interceptors.request.use((config) => {
  if (tokens.access) config.headers.Authorization = `Bearer ${tokens.access}`
  return config
})

// ---- on 401, try the refresh token once, then retry the request ------------
let refreshing = null
api.interceptors.response.use(
  (res) => res,
  async (error) => {
    const original = error.config
    const isAuthCall = original?.url?.startsWith('/auth/')

    if (error.response?.status === 401 && !original._retry && !isAuthCall && tokens.refresh) {
      original._retry = true
      try {
        refreshing = refreshing || axios.post(`${API_URL}/api/auth/refresh`, { refresh_token: tokens.refresh })
        const { data } = await refreshing
        tokens.save(data.tokens)
        original.headers.Authorization = `Bearer ${data.tokens.access_token}`
        return api(original)
      } catch (e) {
        tokens.clear()
        window.dispatchEvent(new Event('auth:logout'))
      } finally {
        refreshing = null
      }
    }
    return Promise.reject(error)
  }
)

export const errorMessage = (err) => {
  const d = err.response?.data
  if (d?.errors) return Object.values(d.errors).join(' ')
  return d?.error || err.message || 'Something went wrong'
}

// ---- endpoints -------------------------------------------------------------
export const login = (username, password) => api.post('/auth/login', { username, password })
export const register = (payload) => api.post('/auth/register', payload)
export const logout = () => api.post('/auth/logout', { refresh_token: tokens.refresh })

export const getProducts = () => api.get('/products')
export const createProduct = (p) => api.post('/products', p)
export const updateProduct = (id, p) => api.put(`/products/${id}`, p)
export const deleteProduct = (id) => api.delete(`/products/${id}`)
