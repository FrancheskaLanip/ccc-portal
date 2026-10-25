import { Link } from "react-router-dom";

const ROLES = [
  {
    key: "student",
    title: "Student",
    desc: "Access your student records and campus services.",
  },
  {
    key: "faculty",
    title: "Faculty",
    desc: "Access your faculty tools and information.",
  },
  {
    key: "staff",
    title: "Staff",
    desc: "Registrar and administrator tools for managing the portal.",
  },
];

export default function Home() {
  return (
    <div className="hero">
      <div className="hero-inner">
        <img src="/logo.png" alt="Calauan Community College logo" className="hero-logo" />
        <span className="hero-tag">Official Portal</span>
        <h1>
          Calauan Community <span>College</span>
        </h1>
        <p className="lead">
          Student Information and Campus Services Portal. Choose how you
          would like to sign in.
        </p>

        <div className="role-cards">
          {ROLES.map((r) => (
            <Link key={r.key} to={"/login?portal=" + r.key} className="role-card">
              <h3>{r.title}</h3>
              <p>{r.desc}</p>
              <span className="role-link">Sign in &rarr;</span>
            </Link>
          ))}
        </div>
      </div>
      <div className="site-footer">
        &copy; 2026 Calauan Community College. All rights reserved.
      </div>
    </div>
  );
}