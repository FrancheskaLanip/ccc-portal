import { useEffect, useState } from "react";
import { api, errorText } from "../csrf";

const EMPTY = { code: "", name: "" };

export default function Programs() {
  const [programs, setPrograms] = useState([]);
  const [form, setForm] = useState(null); // null = form hidden
  const [editingId, setEditingId] = useState(null);
  const [message, setMessage] = useState("");
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(true);
  const [busy, setBusy] = useState(false);

  async function load() {
    try {
      setPrograms((await api("/programs")) || []);
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
    setEditingId(null);
    setForm({ ...EMPTY });
  }

  function openEdit(p) {
    clearNotices();
    setEditingId(p.id);
    setForm({ code: p.code, name: p.name });
  }

  function closeForm() {
    setForm(null);
    setEditingId(null);
  }

  function change(e) {
    setForm({ ...form, [e.target.name]: e.target.value });
  }

  async function save(e) {
    e.preventDefault();
    clearNotices();
    setBusy(true);

    try {
      if (editingId) {
        await api("/programs/" + editingId, { method: "PUT", body: form });
        setMessage("Program updated.");
      } else {
        await api("/programs", { method: "POST", body: form });
        setMessage("Program added.");
      }
      closeForm();
      await load();
    } catch (err) {
      setError(errorText(err));
    } finally {
      setBusy(false);
    }
  }

  async function remove(p) {
    if (!window.confirm("Delete this program?")) return;
    clearNotices();
    try {
      await api("/programs/" + p.id, { method: "DELETE" });
      setMessage("Program deleted.");
      await load();
    } catch (err) {
      setError(errorText(err));
    }
  }

  return (
    <div>
      <div className="page-head">
        <h1>Programs</h1>
        {!form && <button onClick={openAdd}>Add program</button>}
      </div>

      {message && <div className="success">{message}</div>}
      {error && <div className="alert">{error}</div>}

      {form && (
        <form className="form-panel" onSubmit={save}>
          <h2>{editingId ? "Edit program" : "Add program"}</h2>

          <div className="field">
            <label>Code</label>
            <input name="code" value={form.code} onChange={change} maxLength={20} required />
          </div>

          <div className="field">
            <label>Name</label>
            <input name="name" value={form.name} onChange={change} maxLength={255} required />
          </div>

          <div className="actions">
            <button type="submit" disabled={busy}>
              {busy ? "Saving..." : "Save"}
            </button>
            <button type="button" className="light" onClick={closeForm}>
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
                <th>Code</th>
                <th>Name</th>
                <th>Students</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              {programs.length === 0 && (
                <tr>
                  <td colSpan="4">No programs yet.</td>
                </tr>
              )}
              {programs.map((p) => (
                <tr key={p.id}>
                  <td>{p.code}</td>
                  <td>{p.name}</td>
                  <td>{p.students_count}</td>
                  <td>
                    <div className="row-actions">
                      <button className="small light" onClick={() => openEdit(p)}>
                        Edit
                      </button>
                      <button className="small danger" onClick={() => remove(p)}>
                        Delete
                      </button>
                    </div>
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
