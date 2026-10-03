import { useState } from 'react'
import { login, register, errorMessage } from '../api.js'

export default function Login({ onLogin }) {
  const [mode, setMode] = useState('login')
  const [form, setForm] = useState({ username: '', email: '', password: '' })
  const [error, setError] = useState('')
  const [info, setInfo] = useState('')
  const [busy, setBusy] = useState(false)

  const set = (k) => (e) => setForm({ ...form, [k]: e.target.value })

  const submit = async (e) => {
    e.preventDefault()
    setError(''); setInfo(''); setBusy(true)
    try {
      if (mode === 'register') {
        await register(form)
        setInfo('Account created. You can log in now.')
        setMode('login')
      } else {
        const { data } = await login(form.username, form.password)
        onLogin(data)
      }
    } catch (err) {
      setError(errorMessage(err))
    } finally {
      setBusy(false)
    }
  }

  return (
    <div className="center">
      <form className="card auth" onSubmit={submit}>
        <h1>Product Management</h1>
        <p className="muted">{mode === 'login' ? 'Log in to manage products' : 'Create an account'}</p>

        {error && <div className="alert error">{error}</div>}
        {info && <div className="alert ok">{info}</div>}

        <label>{mode === 'login' ? 'Username or email' : 'Username'}
          <input value={form.username} onChange={set('username')} required autoFocus />
        </label>

        {mode === 'register' && (
          <label>Email
            <input type="email" value={form.email} onChange={set('email')} required />
          </label>
        )}

        <label>Password
          <input type="password" value={form.password} onChange={set('password')} required minLength={6} />
        </label>

        <button className="btn primary" disabled={busy}>
          {busy ? 'Please wait…' : mode === 'login' ? 'Login' : 'Register'}
        </button>

        <button type="button" className="link" onClick={() => { setMode(mode === 'login' ? 'register' : 'login'); setError(''); setInfo('') }}>
          {mode === 'login' ? "No account? Register" : 'Have an account? Login'}
        </button>
      </form>
    </div>
  )
}
