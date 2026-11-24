import { useEffect, useState } from "react";
import { api, errorText } from "../csrf";

export default function Enrollments() {
  const [enrollments, setEnrollments] = useState([]);
  const [students, setStudents] = useState([]);
  const [sections, setSections] = useState([]);
  const [form, setForm] = useState({ student_id: "", class_section_id: "" });
  const [message, setMessage] = useState("");
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(true);
  const [busy, setBusy] = useState(false);

  async function load() {
    try {
      const [e, s, c] = await Promise.all([api("/enrollments"), api("/students"), api("/class-sections")]);
      setEnrollments(e || []);
      setStudents(s || []);
      setSections(c || []);
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

  function change(e) {
    setForm({ ...form, [e.target.name]: e.target.value });
  }

  // Enroll a student in a section
  async function enroll(e) {
    e.preventDefault();
    clearNotices();
    setBusy(true);

    try {
      await api("/enrollments", { method: "POST", body: form });
      setMessage("Student enrolled.");
      await load();
    } catch (err) {
      setError(errorText(err));
    } finally {
      setBusy(false);
    }
  }

  // Drop a student, or re-enroll a dropped one
  async function setStatus(en, status) {
    if (!window.confirm("Are you sure?")) return;
    clearNotices();
    try {
      await api("/enrollments/" + en.id, { method: "PUT", body: { status } });
      setMessage(status === "dropped" ? "Student dropped." : "Student re-enrolled.");
      await load();
    } catch (err) {
      setError(errorText(err));
    }
  }

  return (
    <div>
      <div className="page-head">
        <h1>Enrollments</h1>
      </div>

      {message && <div className="success">{message}</div>}
      {error && <div className="alert">{error}</div>}

      <form className="form-panel" onSubmit={enroll}>
        <h2>Enroll a student</h2>

        <div className="field">
          <label>Student</label>
          <select name="student_id" value={form.student_id} onChange={change} required>
            <option value="">Choose a student</option>
            {students.map((s) => (
              <option key={s.id} value={s.id}>
                {s.student_number} - {s.last_name}, {s.first_name}
              </option>
            ))}
          </select>
        </div>

        <div className="field">
          <label>Class section</label>
          <select name="class_section_id" value={form.class_section_id} onChange={change} required>
            <option value="">Choose a class section</option>
            {sections.map((c) => (
              <option key={c.id} value={c.id}>
                {c.subject.code}-{c.section_name} ({c.school_year} {c.semester}) {c.enrolled_count}/{c.max_students} seats
              </option>
            ))}
          </select>
        </div>

        <div className="actions">
          <button type="submit" disabled={busy}>
            {busy ? "Enrolling..." : "Enroll"}
          </button>
        </div>
      </form>

      {loading ? (
        <p>Loading...</p>
      ) : (
        <div className="table-wrap">
          <table>
            <thead>
              <tr>
                <th>Student</th>
                <th>Subject</th>
                <th>Section</th>
                <th>Term</th>
                <th>Schedule</th>
                <th>Faculty</th>
                <th>Status</th>
                <th>Grade</th>
                <th>Remarks</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              {enrollments.length === 0 && (
                <tr>
                  <td colSpan="10">No enrollments yet.</td>
                </tr>
              )}
              {enrollments.map((en) => (
                <tr key={en.id}>
                  <td>
                    {en.student.student_number} - {en.student.last_name}
                  </td>
                  <td>{en.class_section.subject.code}</td>
                  <td>{en.class_section.section_name}</td>
                  <td>
                    {en.class_section.school_year} {en.class_section.semester}
                  </td>
                  <td>{en.class_section.schedule}</td>
                  <td>{en.class_section.faculty.name}</td>
                  <td>
                    <span className={"badge " + (en.status === "enrolled" ? "good" : "warn")}>{en.status}</span>
                  </td>
                  <td>{en.grade?.grade ?? "-"}</td>
                  <td>{en.grade?.remarks ?? "-"}</td>
                  <td>
                    {en.status === "enrolled" ? (
                      <button className="small danger" onClick={() => setStatus(en, "dropped")}>
                        Drop
                      </button>
                    ) : (
                      <button className="small light" onClick={() => setStatus(en, "enrolled")}>
                        Re-enroll
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