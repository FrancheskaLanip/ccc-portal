import { useEffect, useState } from "react";
import { api, errorText } from "../csrf";

const EMPTY = { name: "", email: "", role: "faculty" };
const ROLES = ["faculty", "registrar", "admin"];
const cap = (s) => s.charAt(0).toUpperCase() + s.slice(1);

export default function Users() {
  const me = JSON.parse(localStorage.getItem("user") || "{}");

  const [users, setUsers] = useState([]);
  const [form, setForm] = useState(null); // null = form hidden
  const [message, setMessage] = useState("");
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(true);
  const [busy, setBusy] = useState(false);

  async function load() {
    try {
      setUsers((await api("/users")) || []);
    } catch (err) {
      setError(errorText(err));
    } finally {
      setLoading(false);
    }
  }

  useEffect(() => {
    load();
  }, []);

  function clearNotices() {
    setMessage("");
    setError("");
  }

  function openAdd() {
    clearNotices();
    setForm({ ...EMPTY });
  }

  function change(e) {
    setForm({ ...form, [e.target.name]: e.target.value });
  }

  async function save(e) {
    e.preventDefault();
    clearNotices();
    setBusy(true);

    try {
      await api("/users", { method: "POST", body: form });
      setMessage("Account successfully created");
      setForm(null);
      await load();
    } catch (err) {
      setError(errorText(err));
    } finally {
      setBusy(false);
    }
  }

  async function remove(u) {
    if (!window.confirm("Delete this account?")) return;
    clearNotices();
    try {
      await api("/users/" + u.id, { method: "DELETE" });
      setMessage("Account deleted.");
      await load();
    } catch (err) {
      setError(errorText(err));
    }
  }

  return (
    <div>
      <div className="page-head">
        <h1>Users</h1>
        {!form && <button onClick={openAdd}>Add account</button>}
      </div>

      {message && <div className="success">{message}</div>}
      {error && <div className="alert">{error}</div>}

      {form && (
        <form className="form-panel" onSubmit={save}>
          <h2>Add account</h2>

          <div className="field">
            <label>Name</label>
            <input name="name" value={form.name} onChange={change} maxLength={255} required />
          </div>

          <div className="field">
            <label>Email</label>
            <input type="email" name="email" value={form.email} onChange={change} maxLength={255} required />
          </div>

          <div className="field">
            <label>Role</label>
            <select name="role" value={form.role} onChange={change} required>
              {ROLES.map((r) => (
                <option key={r} value={r}>
                  {cap(r)}
                </option>
              ))}
            </select>
          </div>

          <div className="actions">
            <button type="submit" disabled={busy}>
              {busy ? "Saving..." : "Save"}
            </button>
            <button type="button" className="light" onClick={() => setForm(null)}>
              Cancel
            </button>
          </div>
        </form>
      )}

      {loading ? (
        <p>Loading...</p>
      ) : (
        <div className="table-wrap">
          <table>
            <thead>
              <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              {users.length === 0 && (
                <tr>
                  <td colSpan="4">No accounts yet.</td>
                </tr>
              )}
              {users.map((u) => (
                <tr key={u.id}>
                  <td>{u.name}</td>
                  <td>{u.email}</td>
                  <td>{cap(u.role)}</td>
                  <td>
                    {u.id === me.id ? (
                      "You"
                    ) : (
                      <button className="small danger" onClick={() => remove(u)}>
                        Delete
                      </button>
                    )}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
}
