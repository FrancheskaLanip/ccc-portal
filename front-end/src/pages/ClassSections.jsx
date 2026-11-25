import { useEffect, useState } from "react";
import { useSearchParams } from "react-router-dom";
import { api, errorText } from "../csrf";

const DAYS = ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];

const EMPTY = {
  subject_id: "",
  faculty_id: "",
  section_name: "",
  school_year: "2026-2027",
  semester: "1st",
  max_students: 40,
  day: "",
  start_time: "",
  end_time: "",
  room: "",
};

// The API sends times like 08:00:00; the time input needs 08:00
const hhmm = (t) => (t ? t.slice(0, 5) : "");

export default function ClassSections() {
  // /class-sections (list), /class-sections?new=1 (add), /class-sections?edit=5 (edit)
  const [params, setParams] = useSearchParams();
  const editId = params.get("edit");
  const isNew = params.get("new") === "1";
  const showForm = isNew || Boolean(editId);

  const [sections, setSections] = useState([]);
  const [subjects, setSubjects] = useState([]);
  const [facultyList, setFacultyList] = useState([]);
  const [form, setForm] = useState(null);
  const [message, setMessage] = useState("");
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(true);
  const [busy, setBusy] = useState(false);

  async function load() {
    try {
      const [c, s, f] = await Promise.all([api("/class-sections"), api("/subjects"), api("/faculty")]);
      setSections(c || []);
      setSubjects(s || []);
      setFacultyList(f || []);
    } catch (err) {
      setError(errorText(err));
    } finally {
      setLoading(false);
    }
  }

  useEffect(() => {
    load();
  }, []);

  // Fill the form when the add or edit page opens
  useEffect(() => {
    if (isNew) {
      setForm({ ...EMPTY });
    } else if (editId) {
      const c = sections.find((x) => String(x.id) === String(editId));
      setForm(
        c
          ? {
              subject_id: c.subject_id,
              faculty_id: c.faculty_id,
              section_name: c.section_name,
              school_year: c.school_year,
              semester: c.semester,
              max_students: c.max_students,
              day: c.day || "",
              start_time: hhmm(c.start_time),
              end_time: hhmm(c.end_time),
              room: c.room || "",
            }
          : null
      );
    } else {
      setForm(null);
    }
  }, [isNew, editId, sections]);

  function clearNotices() {
    setMessage("");
    setError("");
  }

  function openAdd() {
    clearNotices();
    setParams({ new: 1 });
  }

  function openEdit(c) {
    clearNotices();
    setParams({ edit: c.id });
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
        await api("/class-sections/" + editId, { method: "PUT", body: form });
        setMessage("Class section updated.");
      } else {
        await api("/class-sections", { method: "POST", body: form });
        setMessage("Class section added.");
      }
      await load();
      setParams({}); // back to the list
    } catch (err) {
      setError(errorText(err));
    } finally {
      setBusy(false);
    }
  }

  async function remove(c) {
    if (!window.confirm("Delete this class section?")) return;
    clearNotices();
    try {
      await api("/class-sections/" + c.id, { method: "DELETE" });
      setMessage("Class section deleted.");
      await load();
    } catch (err) {
      setError(errorText(err));
    }
  }

  // ---------- Page 2: the add / edit form ----------
  if (showForm) {
    return (
      <div>
        <div className="page-head">
          <h1>{editId ? "Edit class section" : "Add class section"}</h1>
          <button className="light" onClick={back}>
            Back to Class Sections
          </button>
        </div>

        {error && <div className="alert">{error}</div>}

        {!form ? (
          <p>{loading ? "Loading..." : "Class section not found."}</p>
        ) : (
          <form className="form-panel" onSubmit={save}>
            <div className="field">
              <label>Subject</label>
              <select name="subject_id" value={form.subject_id} onChange={change} required>
                <option value="">Choose a subject</option>
                {subjects.map((s) => (
                  <option key={s.id} value={s.id}>
                    {s.code} - {s.title}
                  </option>
                ))}
              </select>
            </div>

            <div className="field">
              <label>Faculty</label>
              <select name="faculty_id" value={form.faculty_id} onChange={change} required>
                <option value="">Choose a faculty</option>
                {facultyList.map((f) => (
                  <option key={f.id} value={f.id}>
                    {f.name}
                  </option>
                ))}
              </select>
            </div>

            <div className="field">
              <label>Section name</label>
              <input name="section_name" value={form.section_name} onChange={change} maxLength={20} required />
            </div>

            <div className="field">
              <label>School year</label>
              <input name="school_year" value={form.school_year} onChange={change} placeholder="2026-2027" required />
            </div>

            <div className="field">
              <label>Semester</label>
              <select name="semester" value={form.semester} onChange={change}>
                <option value="1st">1st</option>
                <option value="2nd">2nd</option>
                <option value="Summer">Summer</option>
              </select>
            </div>

            <div className="field">
              <label>Max students</label>
              <input name="max_students" type="number" min="1" max="200" value={form.max_students} onChange={change} required />
            </div>

            <div className="field">
              <label>Day</label>
              <select name="day" value={form.day} onChange={change}>
                <option value="">-</option>
                {DAYS.map((d) => (
                  <option key={d} value={d}>
                    {d}
                  </option>
                ))}
              </select>
            </div>

            <div className="field">
              <label>Start time</label>
              <input name="start_time" type="time" value={form.start_time} onChange={change} />
            </div>

            <div className="field">
              <label>End time</label>
              <input name="end_time" type="time" value={form.end_time} onChange={change} />
            </div>

            <div className="field">
              <label>Room</label>
              <input name="room" value={form.room} onChange={change} maxLength={50} />
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
        <h1>Class Sections</h1>
        <button onClick={openAdd}>Add class section</button>
      </div>

      {message && <div className="success">{message}</div>}
      {error && <div className="alert">{error}</div>}

      {loading ? (
        <p>Loading...</p>
      ) : (
        <div className="table-wrap">
          <table>
            <thead>
              <tr>
                <th>Subject</th>
                <th>Section</th>
                <th>Term</th>
                <th>Schedule</th>
                <th>Faculty</th>
                <th>Seats</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              {sections.length === 0 && (
                <tr>
                  <td colSpan="7">No class sections yet.</td>
                </tr>
              )}
              {sections.map((c) => (
                <tr key={c.id}>
                  <td>
                    {c.subject.code} - {c.subject.title}
                  </td>
                  <td>{c.section_name}</td>
                  <td>
                    {c.school_year} {c.semester}
                  </td>
                  <td>{c.schedule}</td>
                  <td>{c.faculty.name}</td>
                  <td>
                    {c.enrolled_count} / {c.max_students}
                  </td>
                  <td>
                    <div className="row-actions">
                      <button className="small light" onClick={() => openEdit(c)}>
                        Edit
                      </button>
                      <button className="small danger" onClick={() => remove(c)}>
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