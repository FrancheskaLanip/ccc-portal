import { useEffect, useState } from "react";
import { api, errorText } from "../csrf";

export default function MyClasses() {
  const [sections, setSections] = useState([]);
  const [open, setOpen] = useState(null); // the section whose class list is shown
  const [rows, setRows] = useState([]); // enrollments of the open section
  const [edits, setEdits] = useState({}); // typed grades, by grade id
  const [message, setMessage] = useState("");
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    api("/my-classes")
      .then((s) => setSections(s || []))
      .catch((err) => setError(errorText(err)))
      .finally(() => setLoading(false));
  }, []);

  function clearNotices() {
    setMessage("");
    setError("");
  }

  // Load the class list of one section
  async function openSection(section) {
    clearNotices();
    try {
      const list = (await api("/my-classes/" + section.id)) || [];
      const typed = {};
      list.forEach((en) => {
        if (en.grade) typed[en.grade.id] = { grade: en.grade.grade ?? "", inc: en.grade.remarks === "INC" };
      });
      setRows(list);
      setEdits(typed);
      setOpen(section);
    } catch (err) {
      setError(errorText(err));
    }
  }

  function edit(gradeId, change) {
    setEdits({ ...edits, [gradeId]: { ...edits[gradeId], ...change } });
  }

  // Save one student's grade (or INC)
  async function save(en) {
    clearNotices();
    const e = edits[en.grade.id];
    try {
      await api("/grades/" + en.grade.id, {
        method: "PUT",
        body: { grade: e.inc || e.grade === "" ? null : e.grade, remarks: e.inc ? "INC" : null },
      });
      await openSection(open);
      setMessage("Grade saved.");
    } catch (err) {
      setError(errorText(err));
    }
  }

  return (
    <div>
      <div className="page-head">
        <h1>My Classes</h1>
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
                <th>Students</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              {sections.length === 0 && (
                <tr>
                  <td colSpan="6">You have no class sections.</td>
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
                  <td>{c.enrolled_count}</td>
                  <td>
                    <button className="small light" onClick={() => openSection(c)}>
                      Open class list
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}

      {open && (
        <>
          <h2 style={{ marginTop: 24 }}>
            {open.subject.code} - Section {open.section_name} ({open.school_year} {open.semester})
          </h2>
          <div className="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>Student</th>
                  <th>Status</th>
                  <th>Grade (1.00 - 5.00)</th>
                  <th>Mark INC</th>
                  <th>Remarks</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                {rows.length === 0 && (
                  <tr>
                    <td colSpan="6">No students in this section.</td>
                  </tr>
                )}
                {rows.map((en) => {
                  const e = en.grade ? edits[en.grade.id] : null;
                  const canEdit = en.status === "enrolled" && e;
                  return (
                    <tr key={en.id}>
                      <td>
                        {en.student.student_number} - {en.student.last_name}, {en.student.first_name}
                      </td>
                      <td>{en.status}</td>
                      {canEdit ? (
                        <>
                          <td>
                            <input
                              className="grade-input"
                              type="number"
                              step="0.25"
                              min="1"
                              max="5"
                              value={e.grade}
                              disabled={e.inc}
                              onChange={(ev) => edit(en.grade.id, { grade: ev.target.value })}
                            />
                          </td>
                          <td>
                            <input
                              type="checkbox"
                              checked={e.inc}
                              onChange={(ev) => edit(en.grade.id, { inc: ev.target.checked, grade: "" })}
                            />
                          </td>
                          <td>{en.grade.remarks ?? "-"}</td>
                          <td>
                            <button className="small" onClick={() => save(en)}>
                              Save
                            </button>
                          </td>
                        </>
                      ) : (
                        <>
                          <td colSpan="2">-</td>
                          <td>{en.grade?.remarks ?? "-"}</td>
                          <td>-</td>
                        </>
                      )}
                    </tr>
                  );
                })}
              </tbody>
            </table>
          </div>
        </>
      )}
    </div>
  );
}