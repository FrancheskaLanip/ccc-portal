import { useEffect, useState } from "react";
import { useSearchParams } from "react-router-dom";
import { api, errorText } from "../csrf";

export default function MyClasses() {
  // The opened section is kept in the address (/my-classes?section=1), so the browser Back button works
  const [params, setParams] = useSearchParams();
  const sectionId = params.get("section");

  const [sections, setSections] = useState([]);
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

  // Load the class list whenever a section is opened
  useEffect(() => {
    if (sectionId) loadList(sectionId);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [sectionId]);

  function clearNotices() {
    setMessage("");
    setError("");
  }

  async function loadList(id) {
    try {
      const list = (await api("/my-classes/" + id)) || [];
      const typed = {};
      list.forEach((en) => {
        if (en.grade) typed[en.grade.id] = { grade: en.grade.grade ?? "", inc: en.grade.remarks === "INC" };
      });
      setRows(list);
      setEdits(typed);
    } catch (err) {
      setError(errorText(err));
    }
  }

  function openSection(section) {
    clearNotices();
    setRows([]);
    setParams({ section: section.id });
  }

  function back() {
    clearNotices();
    setParams({});
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
      await loadList(sectionId);
      setMessage("Grade saved.");
    } catch (err) {
      setError(errorText(err));
    }
  }

  const open = sectionId ? sections.find((c) => String(c.id) === String(sectionId)) : null;

  // ---------- Page 2: the class list of one section ----------
  if (sectionId) {
    return (
      <div>
        <div className="page-head">
          <h1>{open ? `${open.subject.code} - Section ${open.section_name}` : "Class list"}</h1>
          <button className="light" onClick={back}>
            Back to My Classes
          </button>
        </div>

        {open && (
          <p className="muted">
            {open.subject.title} | {open.school_year} {open.semester} | {open.schedule} | {open.enrolled_count} students
          </p>
        )}

        {message && <div className="success">{message}</div>}
        {error && <div className="alert">{error}</div>}

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
      </div>
    );
  }

  // ---------- Page 1: my class sections ----------
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
    </div>
  );
}