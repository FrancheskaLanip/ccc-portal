import { useEffect, useState } from "react";
import { Link, useLocation } from "react-router-dom";
import { api } from "../csrf";

function Stat({ label, value, tone }) {
  return (
    <div className={"stat" + (tone ? " " + tone : "")}>
      <span className="stat-label">{label}</span>
      <strong className="stat-value">{value}</strong>
    </div>
  );
}

function classOf(s) {
  return `${s.program?.code || ""} ${s.year_level || ""}${s.section ? "-" + s.section : ""}`.trim();
}

function StaffDashboard({ role }) {
  const [students, setStudents] = useState([]);
  const [programs, setPrograms] = useState([]);
  const [users, setUsers] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    Promise.all([
      api("/students").catch(() => []),
      api("/programs").catch(() => []),
      role === "admin" ? api("/users").catch(() => []) : Promise.resolve([]),
    ]).then(([s, p, u]) => {
      setStudents(s || []);
      setPrograms(p || []);
      setUsers(u || []);
      setLoading(false);
    });
  }, [role]);

  if (loading) return <p className="muted">Loading...</p>;

  const enrolled = students.filter((s) => s.enrollment_status === "Enrolled").length;
  const waiting = students.filter((s) => s.enrollment_status !== "Enrolled");

  const perProgram = programs.map((p) => ({
    code: p.code,
    count: students.filter((s) => s.program_id === p.id).length,
  }));
  const max = Math.max(1, ...perProgram.map((p) => p.count));

  return (
    <>
      <div className="stats">
        <Stat label="Total students" value={students.length} />
        <Stat label="Enrolled" value={enrolled} tone="good" />
        <Stat label="Not enrolled" value={waiting.length} tone="warn" />
        <Stat label="Programs" value={programs.length} />
        {role === "admin" && <Stat label="User accounts" value={users.length} />}
      </div>

      <div className="panel-grid">
        <section className="dash-panel">
          <h3>Students per program</h3>
          {perProgram.length === 0 && <p className="muted">No programs yet.</p>}
          {perProgram.map((p) => (
            <div className="bar-row" key={p.code}>
              <span className="bar-code">{p.code}</span>
              <div className="bar-track">
                <div className="bar-fill" style={{ width: (p.count / max) * 100 + "%" }} />
              </div>
              <span className="bar-count">{p.count}</span>
            </div>
          ))}
        </section>

        <section className="dash-panel">
          <div className="dash-panel-head">
            <h3>Waiting for enrollment</h3>
            <Link to="/students" className="dash-link">
              Open students
            </Link>
          </div>
          {waiting.length === 0 ? (
            <p className="muted">Everyone is enrolled.</p>
          ) : (
            <ul className="mini-list">
              {waiting.slice(0, 6).map((s) => (
                <li key={s.id}>
                  <div>
                    <strong>
                      {s.last_name}, {s.first_name}
                    </strong>
                    <small>{s.student_number}</small>
                  </div>
                  <span className="badge warn">{classOf(s) || "No class"}</span>
                </li>
              ))}
            </ul>
          )}
        </section>
      </div>
    </>
  );
}

function StudentDashboard({ user }) {
  const [student, setStudent] = useState(null);
  const [subjects, setSubjects] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    Promise.all([api("/my-student").catch(() => null), api("/my-subjects").catch(() => [])])
      .then(([s, subs]) => {
        setStudent(s);
        setSubjects(subs || []);
      })
      .finally(() => setLoading(false));
  }, []);

  if (loading) return <p className="muted">Loading...</p>;
  if (!student) return <p className="muted">No student record is linked to this account.</p>;

  const enrolled = student.enrollment_status === "Enrolled";

  return (
    <>
      <div className="panel-grid">
        <section className="dash-panel">
          <h3>My profile</h3>
          <dl className="info-list">
            <div>
              <dt>Student number</dt>
              <dd>{student.student_number}</dd>
            </div>
            <div>
              <dt>Name</dt>
              <dd>
                {student.last_name}, {student.first_name}
              </dd>
            </div>
            <div>
              <dt>Email</dt>
              <dd>{user.email || "-"}</dd>
            </div>
            <div>
              <dt>Program</dt>
              <dd>{student.program?.name || student.program?.code || "-"}</dd>
            </div>
            <div>
              <dt>Class</dt>
              <dd>{classOf(student) || "-"}</dd>
            </div>
            <div>
              <dt>Contact number</dt>
              <dd>{student.contact_number || "-"}</dd>
            </div>
          </dl>
        </section>

        <section className="dash-panel">
          <h3>Enrollment status</h3>
          <span className={"badge big " + (enrolled ? "good" : "warn")}>{student.enrollment_status}</span>
          <p className="muted" style={{ marginTop: 14 }}>
            {enrolled
              ? "You are officially enrolled."
              : "Please wait for the registrar to officially enroll you."}
          </p>
        </section>
      </div>

      <section className="dash-panel" style={{ marginTop: 18 }}>
        <h3>My subjects</h3>
        <div className="table-wrap">
          <table>
            <thead>
              <tr>
                <th>Subject</th>
                <th>Units</th>
                <th>Section</th>
                <th>Term</th>
                <th>Schedule</th>
                <th>Faculty</th>
                <th>Status</th>
                <th>Grade</th>
                <th>Remarks</th>
              </tr>
            </thead>
            <tbody>
              {subjects.length === 0 && (
                <tr>
                  <td colSpan="9">You are not enrolled in any subject yet.</td>
                </tr>
              )}
              {subjects.map((en) => (
                <tr key={en.id}>
                  <td>
                    {en.class_section.subject.code} - {en.class_section.subject.title}
                  </td>
                  <td>{en.class_section.subject.units}</td>
                  <td>{en.class_section.section_name}</td>
                  <td>
                    {en.class_section.school_year} {en.class_section.semester}
                  </td>
                  <td>{en.class_section.schedule}</td>
                  <td>{en.class_section.faculty.name}</td>
                  <td>{en.status}</td>
                  <td>{en.grade?.grade ?? "-"}</td>
                  <td>{en.grade?.remarks ?? "-"}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </section>
    </>
  );
}

function FacultyDashboard({ user }) {
  return (
    <div className="panel-grid">
      <section className="dash-panel">
        <h3>My account</h3>
        <dl className="info-list">
          <div>
            <dt>Name</dt>
            <dd>{user.name}</dd>
          </div>
          <div>
            <dt>Email</dt>
            <dd>{user.email}</dd>
          </div>
          <div>
            <dt>Role</dt>
            <dd style={{ textTransform: "capitalize" }}>{user.role}</dd>
          </div>
        </dl>
      </section>

      <section className="dash-panel">
        <div className="dash-panel-head">
          <h3>My classes</h3>
          <Link to="/my-classes" className="dash-link">
            Open my classes
          </Link>
        </div>
        <p className="muted">See your class lists and encode grades.</p>
      </section>
    </div>
  );
}

export default function Dashboard() {
  const user = JSON.parse(localStorage.getItem("user") || "{}");
  const location = useLocation();
  const message = location.state?.message;

  return (
    <div>
      <div className="welcome">
        <h1>Welcome, {user.name}</h1>
        <p style={{ textTransform: "capitalize" }}>Signed in as {user.role}</p>
      </div>

      {message && <div className="success">{message}</div>}

      {(user.role === "admin" || user.role === "registrar") && <StaffDashboard role={user.role} />}
      {user.role === "student" && <StudentDashboard user={user} />}
      {user.role === "faculty" && <FacultyDashboard user={user} />}
    </div>
  );
}