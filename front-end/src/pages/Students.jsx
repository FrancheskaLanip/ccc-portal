import { useEffect, useMemo, useState } from "react";
import { api, errorText } from "../csrf";

const EMPTY = {
  email: "",
  student_number: "",
  first_name: "",
  last_name: "",
  program_id: "",
  year_level: "",
  section: "",
  contact_number: "",
  enrollment_status: "Not Enrolled",
};

const SORTERS = {
  student_number: (s) => s.student_number || "",
  name: (s) => `${s.last_name} ${s.first_name}`.toLowerCase(),
  class: (s) => `${s.program?.code || ""} ${s.year_level || 0} ${s.section || ""}`,
  status: (s) => s.enrollment_status || "",
};

export default function Students() {
  const [students, setStudents] = useState([]);
  const [programs, setPrograms] = useState([]);
  const [form, setForm] = useState(null); // null = form hidden
  const [editingId, setEditingId] = useState(null);
  const [message, setMessage] = useState("");
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(true);
  const [busy, setBusy] = useState(false);

  // Search, filter and sort
  const [search, setSearch] = useState("");
  const [programFilter, setProgramFilter] = useState("");
  const [statusFilter, setStatusFilter] = useState("");
  const [sortBy, setSortBy] = useState("name");
  const [sortDir, setSortDir] = useState("asc");

  async function load() {
    try {
      const [s, p] = await Promise.all([api("/students"), api("/programs")]);
      setStudents(s || []);
      setPrograms(p || []); // the API already sorts programs by code
    } catch (err) {
      setError(errorText(err));
    } finally {
      setLoading(false);
    }
  }

  useEffect(() => {
    load();
  }, []);

  const shown = useMemo(() => {
    const q = search.trim().toLowerCase();
    const list = students.filter((s) => {
      if (programFilter && String(s.program_id) !== programFilter) return false;
      if (statusFilter && s.enrollment_status !== statusFilter) return false;
      if (q) {
        const text = `${s.student_number} ${s.first_name} ${s.last_name} ${s.last_name}, ${s.first_name}`.toLowerCase();
        if (!text.includes(q)) return false;
      }
      return true;
    });

    const get = SORTERS[sortBy];
    list.sort((a, b) => {
      const x = get(a);
      const y = get(b);
      if (x < y) return sortDir === "asc" ? -1 : 1;
      if (x > y) return sortDir === "asc" ? 1 : -1;
      return 0;
    });
    return list;
  }, [students, search, programFilter, statusFilter, sortBy, sortDir]);

  function sortOn(key) {
    if (sortBy === key) {
      setSortDir(sortDir === "asc" ? "desc" : "asc");
    } else {
      setSortBy(key);
      setSortDir("asc");
    }
  }

  function arrow(key) {
    if (sortBy !== key) return "";
    return sortDir === "asc" ? " ▲" : " ▼";
  }

  function resetFilters() {
    setSearch("");
    setProgramFilter("");
    setStatusFilter("");
  }

  const filtering = search || programFilter || statusFilter;

  function clearNotices() {
    setMessage("");
    setError("");
  }

  function openAdd() {
    clearNotices();
    setEditingId(null);
    setForm({ ...EMPTY });
  }

  function openEdit(s) {
    clearNotices();
    setEditingId(s.id);
    setForm({
      email: "",
      student_number: s.student_number,
      first_name: s.first_name,
      last_name: s.last_name,
      program_id: s.program_id,
      year_level: s.year_level,
      section: s.section || "",
      contact_number: s.contact_number || "",
      enrollment_status: s.enrollment_status,
    });
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

    const body = { ...form };
    if (editingId) delete body.email; // the email is only used when adding

    try {
      if (editingId) {
        await api("/students/" + editingId, { method: "PUT", body });
        setMessage("Student updated.");
      } else {
        await api("/students", { method: "POST", body });
        setMessage('Student added. They can get their login credentials with "Forgot password" on the login page.');
      }
      closeForm();
      await load();
    } catch (err) {
      setError(errorText(err));
    } finally {
      setBusy(false);
    }
  }

  async function enroll(s) {
    clearNotices();
    try {
      await api("/students/" + s.id + "/enroll", { method: "PATCH" });
      setMessage("Student officially enrolled.");
      await load();
    } catch (err) {
      setError(errorText(err));
    }
  }

  async function remove(s) {
    if (!window.confirm("Delete this student and their login?")) return;
    clearNotices();
    try {
      await api("/students/" + s.id, { method: "DELETE" });
      setMessage("Student deleted.");
      await load();
    } catch (err) {
      setError(errorText(err));
    }
  }

  return (
    <div>
      <div className="page-head">
        <h1>Students</h1>
        {!form && <button onClick={openAdd}>Add student</button>}
      </div>

      {message && <div className="success">{message}</div>}
      {error && <div className="alert">{error}</div>}

      {form && (
        <form className="form-panel" onSubmit={save}>
          <h2>{editingId ? "Edit student" : "Add student"}</h2>

          {!editingId && (
            <div className="field">
              <label>Email (used for Forgot password)</label>
              <input type="email" name="email" value={form.email} onChange={change} required />
            </div>
          )}

          <div className="field">
            <label>Student number</label>
            <input
              name="student_number"
              value={form.student_number}
              onChange={change}
              placeholder="CCC-2023-0353"
              pattern="CCC-\d{4}-\d{4}"
              title="Format: CCC-2023-0353"
              required
            />
          </div>

          <div className="field">
            <label>First name</label>
            <input name="first_name" value={form.first_name} onChange={change} required />
          </div>

          <div className="field">
            <label>Last name</label>
            <input name="last_name" value={form.last_name} onChange={change} required />
          </div>

          <div className="field">
            <label>Program</label>
            <select name="program_id" value={form.program_id} onChange={change} required>
              <option value="">Select</option>
              {programs.map((p) => (
                <option key={p.id} value={p.id}>
                  {p.code}
                </option>
              ))}
            </select>
          </div>

          <div className="field">
            <label>Year level</label>
            <input type="number" name="year_level" min="1" max="6" value={form.year_level} onChange={change} required />
          </div>

          <div className="field">
            <label>Section</label>
            <input name="section" maxLength={10} value={form.section} onChange={change} placeholder="A" />
          </div>

          <div className="field">
            <label>Contact number</label>
            <input name="contact_number" value={form.contact_number} onChange={change} />
          </div>

          <div className="field">
            <label>Status</label>
            <select name="enrollment_status" value={form.enrollment_status} onChange={change}>
              <option>Not Enrolled</option>
              <option>Pending</option>
              <option>Enrolled</option>
            </select>
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

      <div className="toolbar">
        <input
          type="search"
          className="toolbar-search"
          placeholder="Search by name or student number"
          value={search}
          onChange={(e) => setSearch(e.target.value)}
        />
        <select value={programFilter} onChange={(e) => setProgramFilter(e.target.value)}>
          <option value="">All programs</option>
          {programs.map((p) => (
            <option key={p.id} value={p.id}>
              {p.code}
            </option>
          ))}
        </select>
        <select value={statusFilter} onChange={(e) => setStatusFilter(e.target.value)}>
          <option value="">All statuses</option>
          <option>Not Enrolled</option>
          <option>Pending</option>
          <option>Enrolled</option>
        </select>
        {filtering && (
          <button type="button" className="small light" onClick={resetFilters}>
            Clear
          </button>
        )}
        <span className="toolbar-count">
          Showing {shown.length} of {students.length}
        </span>
      </div>

      {loading ? (
        <p>Loading...</p>
      ) : (
        <div className="table-wrap">
          <table>
            <thead>
              <tr>
                <th className="sortable" onClick={() => sortOn("student_number")}>
                  Student no.{arrow("student_number")}
                </th>
                <th className="sortable" onClick={() => sortOn("name")}>
                  Name{arrow("name")}
                </th>
                <th className="sortable" onClick={() => sortOn("class")}>
                  Class{arrow("class")}
                </th>
                <th className="sortable" onClick={() => sortOn("status")}>
                  Status{arrow("status")}
                </th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              {shown.length === 0 && (
                <tr>
                  <td colSpan="5">{students.length === 0 ? "No students yet." : "No students match your search."}</td>
                </tr>
              )}
              {shown.map((s) => (
                <tr key={s.id}>
                  <td>{s.student_number}</td>
                  <td>
                    {s.last_name}, {s.first_name}
                  </td>
                  <td>
                    {s.program?.code} {s.year_level}
                    {s.section ? "-" + s.section : ""}
                  </td>
                  <td>{s.enrollment_status}</td>
                  <td>
                    <div className="row-actions">
                      <button className="small light" onClick={() => openEdit(s)}>
                        Edit
                      </button>
                      {s.enrollment_status === "Pending" && (
                        <button className="small" onClick={() => enroll(s)}>
                          Confirm enrollment
                        </button>
                      )}
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