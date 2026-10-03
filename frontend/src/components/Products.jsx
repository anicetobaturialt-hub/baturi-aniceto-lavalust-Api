import { useCallback, useEffect, useState } from 'react'
import ProductForm from './ProductForm.jsx'
import { getProducts, createProduct, updateProduct, deleteProduct, errorMessage } from '../api.js'

const peso = new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' })

export default function Products({ user, onLogout }) {
  const [products, setProducts] = useState([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')
  const [formError, setFormError] = useState('')
  const [saving, setSaving] = useState(false)
  const [editing, setEditing] = useState(null) // null = closed, {} = add, product = edit

  const load = useCallback(async () => {
    setLoading(true); setError('')
    try {
      const { data } = await getProducts()
      setProducts(data.data)
    } catch (err) {
      setError(errorMessage(err))
    } finally {
      setLoading(false)
    }
  }, [])

  useEffect(() => { load() }, [load])

  const save = async (payload) => {
    setSaving(true); setFormError('')
    try {
      if (editing.id) await updateProduct(editing.id, payload)
      else await createProduct(payload)
      setEditing(null)
      await load()
    } catch (err) {
      setFormError(errorMessage(err))
    } finally {
      setSaving(false)
    }
  }

  const remove = async (p) => {
    if (!window.confirm(`Delete "${p.product_name}"?`)) return
    try {
      await deleteProduct(p.id)
      setProducts((list) => list.filter((x) => x.id !== p.id))
    } catch (err) {
      setError(errorMessage(err))
    }
  }

  const openForm = (p) => { setFormError(''); setEditing(p) }

  return (
    <div className="page">
      <header className="topbar">
        <h1>Product Management</h1>
        <div className="who">
          <span className="muted">Signed in as <strong>{user.username}</strong></span>
          <button className="btn" onClick={onLogout}>Logout</button>
        </div>
      </header>

      <div className="card">
        <div className="toolbar">
          <h2>Products <span className="muted">({products.length})</span></h2>
          <button className="btn primary" onClick={() => openForm({})}>+ Add product</button>
        </div>

        {error && <div className="alert error">{error}</div>}

        {loading ? <p className="muted pad">Loading…</p> : products.length === 0 ? (
          <p className="muted pad">No products yet. Click “Add product” to create one.</p>
        ) : (
          <div className="table-wrap">
            <table>
              <thead>
                <tr><th>ID</th><th>Name</th><th>Description</th><th className="num">Price</th><th className="num">Qty</th><th>Created</th><th></th></tr>
              </thead>
              <tbody>
                {products.map((p) => (
                  <tr key={p.id}>
                    <td>{p.id}</td>
                    <td><strong>{p.product_name}</strong></td>
                    <td className="desc">{p.description}</td>
                    <td className="num">{peso.format(p.price)}</td>
                    <td className="num">{p.quantity}</td>
                    <td>{p.created_at}</td>
                    <td className="row-actions">
                      <button className="btn small" onClick={() => openForm(p)}>Edit</button>
                      <button className="btn small danger" onClick={() => remove(p)}>Delete</button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>

      {editing && (
        <ProductForm
          product={editing.id ? editing : null}
          onSave={save}
          onCancel={() => setEditing(null)}
          saving={saving}
          error={formError}
        />
      )}
    </div>
  )
}
