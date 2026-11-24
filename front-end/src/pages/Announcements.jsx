import { useEffect, useState } from "react";
import { api, errorText } from "../csrf";

const EMPTY = { title: "", body: "", audience: "all" };
const AUDIENCE = { all: "Everyone", student: "Students only", faculty: "Faculty only" };

export default function Announcements() {
  const user = JSON.parse(localStorage.getItem("user") || "{}");
  const isStaff = user.role === "admin" || user.role === "registrar";

  const [items, setItems] = useState([]);
  const [form, setForm] = useState(null); // null = form hidden
  const [editingId, setEditingId] = useState(null);
  const [message, setMessage] = useState("");
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(true);
  const [busy, setBusy] = useState(false);

  async function load() {
    try {
      setItems((await api("/announcements")) || []);
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

  function openEdit(a) {
    clearNotices();
    setEditingId(a.id);
    setForm({ title: a.title, body: a.body, audience: a.audience });
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
        await api("/announcements/" + editingId, { method: "PUT", body: form });
        setMessage("Announcement updated.");
      } else {
        await api("/announcements", { method: "POST", body: form });
        setMessage("Announcement posted.");
      }
      closeForm();
      await load();
    } catch (err) {
      setError(errorText(err));
    } finally {
      setBusy(false);
    }
  }

  async function remove(a) {
    if (!window.confirm("Delete this announcement?")) return;
    clearNotices();
    try {
      await api("/announcements/" + a.id, { method: "DELETE" });
      setMessage("Announcement deleted.");
      await load();
    } catch (err) {
      setError(errorText(err));
    }
  }

  return (
    <div>
      <div className="page-head">
        <h1>Announcements</h1>
        {isStaff && !form && <button onClick={openAdd}>Post an announcement</button>}
      </div>

      {message && <div className="success">{message}</div>}
      {error && <div className="alert">{error}</div>}

      {isStaff && form && (
        <form className="form-panel" onSubmit={save}>
          <h2>{editingId ? "Edit announcement" : "Post an announcement"}</h2>

          <div className="field">
            <label>Title</label>
            <input name="title" value={form.title} onChange={change} maxLength={255} required />
          </div>

          <div className="field">
            <label>For</label>
            <select name="audience" value={form.audience} onChange={change}>
              {Object.entries(AUDIENCE).map(([value, label]) => (
                <option key={value} value={value}>
                  {label}
                </option>
              ))}
            </select>
          </div>

          <div className="field" style={{ gridColumn: "1 / -1" }}>
            <label>Message</label>
            <textarea name="body" value={form.body} onChange={change} rows={4} maxLength={5000} required />
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
      ) : items.length === 0 ? (
        <p className="muted">No announcements yet.</p>
      ) : (
        items.map((a) => (
          <article className="note" key={a.id}>
            <h3>{a.title}</h3>
            <small>
              {new Date(a.created_at).toLocaleDateString()} by {a.poster?.name || "Unknown"}
              {isStaff && <span className="badge" style={{ marginLeft: 8 }}>{AUDIENCE[a.audience]}</span>}
            </small>
            <p>{a.body}</p>
            {isStaff && (
              <div className="row-actions">
                <button className="small light" onClick={() => openEdit(a)}>
                  Edit
                </button>
                <button className="small danger" onClick={() => remove(a)}>
                  Delete
                </button>
              </div>
            )}
          </article>
        ))
      )}
    </div>
  );
}