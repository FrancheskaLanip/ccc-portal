import { useEffect, useState } from "react";
import { useSearchParams } from "react-router-dom";
import { api, errorText } from "../csrf";

const EMPTY = { title: "", body: "", audience: "all" };
const AUDIENCE = { all: "Everyone", student: "Students only", faculty: "Faculty only" };

export default function Announcements() {
  const user = JSON.parse(localStorage.getItem("user") || "{}");
  const isStaff = user.role === "admin" || user.role === "registrar";

  // /announcements (list), /announcements?new=1 (post), /announcements?edit=5 (edit). Only staff see the form.
  const [params, setParams] = useSearchParams();
  const editId = params.get("edit");
  const isNew = params.get("new") === "1";
  const showForm = isStaff && (isNew || Boolean(editId));

  const [items, setItems] = useState([]);
  const [form, setForm] = useState(null);
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

  // Fill the form when the post or edit page opens
  useEffect(() => {
    if (isNew) {
      setForm({ ...EMPTY });
    } else if (editId) {
      const a = items.find((x) => String(x.id) === String(editId));
      setForm(a ? { title: a.title, body: a.body, audience: a.audience } : null);
    } else {
      setForm(null);
    }
  }, [isNew, editId, items]);

  function clearNotices() {
    setMessage("");
    setError("");
  }

  function openAdd() {
    clearNotices();
    setParams({ new: 1 });
  }

  function openEdit(a) {
    clearNotices();
    setParams({ edit: a.id });
  }

  function back() {
    clearNotices();
    setParams({});
  }

  function change(e) {
    setForm({ ...form, [e.target.name]: e.target.value });
  }

  async function save(e) {
    e.preventDefault();
    clearNotices();
    setBusy(true);

    try {
      if (editId) {
        await api("/announcements/" + editId, { method: "PUT", body: form });
        setMessage("Announcement updated.");
      } else {
        await api("/announcements", { method: "POST", body: form });
        setMessage("Announcement posted.");
      }
      await load();
      setParams({}); // back to the list
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

  // ---------- Page 2: the post / edit form (staff only) ----------
  if (showForm) {
    return (
      <div>
        <div className="page-head">
          <h1>{editId ? "Edit announcement" : "Post an announcement"}</h1>
          <button className="light" onClick={back}>
            Back to Announcements
          </button>
        </div>

        {error && <div className="alert">{error}</div>}

        {!form ? (
          <p>{loading ? "Loading..." : "Announcement not found."}</p>
        ) : (
          <form className="form-panel" onSubmit={save}>
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
              <textarea name="body" value={form.body} onChange={change} rows={5} maxLength={5000} required />
            </div>

            <div className="actions">
              <button type="submit" disabled={busy}>
                {busy ? "Saving..." : "Save"}
              </button>
              <button type="button" className="light" onClick={back}>
                Cancel
              </button>
            </div>
          </form>
        )}
      </div>
    );
  }

  // ---------- Page 1: the list ----------
  return (
    <div>
      <div className="page-head">
        <h1>Announcements</h1>
        {isStaff && <button onClick={openAdd}>Post an announcement</button>}
      </div>

      {message && <div className="success">{message}</div>}
      {error && <div className="alert">{error}</div>}

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