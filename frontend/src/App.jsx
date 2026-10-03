import { useEffect, useState } from 'react'
import Login from './components/Login.jsx'
import Products from './components/Products.jsx'
import { tokens, logout as apiLogout } from './api.js'

export default function App() {
  const [user, setUser] = useState(() => {
    try { return tokens.access ? JSON.parse(localStorage.getItem('user')) : null } catch { return null }
  })

  // The API layer fires this event when the refresh token is no longer valid.
  useEffect(() => {
    const onLogout = () => setUser(null)
    window.addEventListener('auth:logout', onLogout)
    return () => window.removeEventListener('auth:logout', onLogout)
  }, [])

  const handleLogin = (data) => {
    tokens.save(data)
    localStorage.setItem('user', JSON.stringify(data.user))
    setUser(data.user)
  }

  const handleLogout = async () => {
    try { await apiLogout() } catch { /* token may already be expired */ }
    tokens.clear()
    setUser(null)
  }

  return user
    ? <Products user={user} onLogout={handleLogout} />
    : <Login onLogin={handleLogin} />
}
