import { useEffect, useState } from "react";
import { api, errorText } from "../csrf";

const EMPTY = { code: "", title: "", units: 3, programs: [] };

export default function Subjects() {
  const [subjects, setSubjects] = useState([]);
  const [programs, setPrograms] = useState([]);
  const [form, setForm] = useState(null); // null = form hidden
  const [editingId, setEditingId] = useState(null);
  const [message, setMessage] = useState("");
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(true);
  const [busy, setBusy] = useState(false);

  async function load() {
    try {
      const [s, p] = await Promise.all([api("/subjects"), api("/programs")]);
      setSubjects(s || []);
      setPrograms(p || []);
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
    setForm({ ...EMPTY, programs: [] });
  }

  function openEdit(s) {
    clearNotices();
    setEditingId(s.id);
    setForm({ code: s.code, title: s.title, units: s.units, programs: s.programs.map((p) => p.id) });
  }

  function closeForm() {
    setForm(null);
    setEditingId(null);
  }

  function change(e) {
    setForm({ ...form, [e.target.name]: e.target.value });
  }

  // Tick or untick one program
  function toggleProgram(id) {
    const has = form.programs.includes(id);
    setForm({ ...form, programs: has ? form.programs.filter((p) => p !== id) : [...form.programs, id] });
  }

  async function save(e) {
    e.preventDefault();
    clearNotices();
    setBusy(true);

    try {
      if (editingId) {
        await api("/subjects/" + editingId, { method: "PUT", body: form });
        setMessage("Subject updated.");
      } else {
        await api("/subjects", { method: "POST", body: form });
        setMessage("Subject added.");
      }
      closeForm();
      await load();
    } catch (err) {
      setError(errorText(err));
    } finally {
      setBusy(false);
    }
  }

  async function remove(s) {
    if (!window.confirm("Delete this subject?")) return;
    clearNotices();
    try {
      await api("/subjects/" + s.id, { method: "DELETE" });
      setMessage("Subject deleted.");
      await load();
    } catch (err) {
      setError(errorText(err));
    }
  }

  return (
    <div>
      <div className="page-head">
        <h1>Subjects</h1>
        {!form && <button onClick={openAdd}>Add subject</button>}
      </div>

      {message && <div className="success">{message}</div>}
      {error && <div className="alert">{error}</div>}

      {form && (
        <form className="form-panel" onSubmit={save}>
          <h2>{editingId ? "Edit subject" : "Add subject"}</h2>

          <div className="field">
            <label>Code</label>
            <input name="code" value={form.code} onChange={change} maxLength={20} required />
          </div>

          <div className="field">
            <label>Title</label>
            <input name="title" value={form.title} onChange={change} maxLength={255} required />
          </div>

          <div className="field">
            <label>Units (1 to 6)</label>
            <input name="units" type="number" min="1" max="6" value={form.units} onChange={change} required />
          </div>

          <div className="check-row">
            <strong>Offered by:</strong>
            {programs.map((p) => (
              <label key={p.id}>
                <input type="checkbox" checked={form.programs.includes(p.id)} onChange={() => toggleProgram(p.id)} />
                {p.code}
              </label>
            ))}
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
                <th>Title</th>
                <th>Units</th>
                <th>Programs</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              {subjects.length === 0 && (
                <tr>
                  <td colSpan="5">No subjects yet.</td>
                </tr>
              )}
              {subjects.map((s) => (
                <tr key={s.id}>
                  <td>{s.code}</td>
                  <td>{s.title}</td>
                  <td>{s.units}</td>
                  <td>{s.programs.map((p) => p.code).join(", ") || "-"}</td>
                  <td>
                    <div className="row-actions">
                      <button className="small light" onClick={() => openEdit(s)}>
                        Edit
                      </button>
                      <button className="small danger" onClick={() => remove(s)}>
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