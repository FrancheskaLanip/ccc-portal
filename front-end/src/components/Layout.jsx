import { useEffect, useState } from "react";
import { NavLink, Navigate, Outlet, useLocation, useNavigate } from "react-router-dom";
import { api, xsrfHeaders } from "../csrf";

// Small line icons (no extra package needed)
function Icon({ name }) {
  const paths = {
    dashboard: (
      <>
        <rect x="3" y="3" width="7" height="7" rx="1" />
        <rect x="14" y="3" width="7" height="7" rx="1" />
        <rect x="14" y="14" width="7" height="7" rx="1" />
        <rect x="3" y="14" width="7" height="7" rx="1" />
      </>
    ),
    students: (
      <>
        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
        <circle cx="9" cy="7" r="4" />
        <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
        <path d="M16 3.13a4 4 0 0 1 0 7.75" />
      </>
    ),
    programs: (
      <>
        <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z" />
        <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z" />
      </>
    ),
    users: (
      <>
        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
        <circle cx="9" cy="7" r="4" />
        <path d="M16 11l2 2 4-4" />
      </>
    ),
    password: (
      <>
        <rect x="3" y="11" width="18" height="11" rx="2" />
        <path d="M7 11V7a5 5 0 0 1 10 0v4" />
      </>
    ),
    menu: (
      <>
        <path d="M3 6h18" />
        <path d="M3 12h18" />
        <path d="M3 18h18" />
      </>
    ),
  };
  return (
    <svg
      viewBox="0 0 24 24"
      width="20"
      height="20"
      fill="none"
      stroke="currentColor"
      strokeWidth="1.8"
      strokeLinecap="round"
      strokeLinejoin="round"
      aria-hidden="true"
    >
      {paths[name]}
    </svg>
  );
}

const DASH = { to: "/dashboard", label: "Dashboard", icon: "dashboard" };
const STUDENTS = { to: "/students", label: "Students", icon: "students" };
const PROGRAMS = { to: "/programs", label: "Programs", icon: "programs" };
const USERS = { to: "/users", label: "Users", icon: "users" };
const PASSWORD = { to: "/password/change", label: "Change password", icon: "password" };

const MENU = {
  student: [DASH],
  faculty: [DASH],
  registrar: [DASH, STUDENTS, PROGRAMS],
  admin: [DASH, STUDENTS, PROGRAMS, USERS],
};

export default function Layout() {
  const navigate = useNavigate();
  const { pathname } = useLocation();
  const [open, setOpen] = useState(() => window.innerWidth > 900); // open on big screens
  const user = JSON.parse(localStorage.getItem("user") || "null");

  // On small screens, close the sidebar after choosing a page
  useEffect(() => {
    if (window.innerWidth <= 900) setOpen(false);
  }, [pathname]);

  // Make sure the login session on the server is still valid
  useEffect(() => {
    if (!user) return;
    api("/me")
      .then((me) => {
        if (!me) {
          localStorage.clear();
          navigate("/");
        } else {
          localStorage.setItem("user", JSON.stringify(me));
        }
      })
      .catch(() => {});
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  if (!user) {
    return <Navigate to="/" />;
  }

  // Same rule as the back-end: each role can only open its own pages
  const menu = MENU[user.role] || [];
  const allowed = [...menu.map((m) => m.to), PASSWORD.to];
  if (!allowed.includes(pathname)) {
    return <Navigate to="/dashboard" replace />;
  }

  async function logout() {
    try {
      await fetch("/api/logout", {
        method: "POST",
        credentials: "same-origin",
        headers: {
          Accept: "application/json",
          ...xsrfHeaders(),
        },
      });
    } catch (err) {
      // ignore: the local session is cleared below anyway
    }
    localStorage.clear();
    navigate("/");
  }

  const link = (item) => (
    <NavLink
      key={item.to}
      to={item.to}
      className={({ isActive }) => "side-link" + (isActive ? " active" : "")}
    >
      <Icon name={item.icon} />
      <span>{item.label}</span>
    </NavLink>
  );

  return (
    <div className={"shell" + (open ? " nav-open" : "")}>
      <header className="topbar">
        <button className="menu-btn" aria-label="Toggle menu" onClick={() => setOpen((o) => !o)}>
          <Icon name="menu" />
        </button>
        <div className="topbar-brand">
          <img src="/logo.png" alt="" />
          <span>
            CCC <b>Portal</b>
          </span>
        </div>
        <span className="topbar-user">{user.name}</span>
      </header>

      <div className={"scrim" + (open ? " show" : "")} onClick={() => setOpen(false)} />

      <aside className={"sidebar" + (open ? " open" : "")}>
        <nav className="side-nav">
          <p className="side-label">Menu</p>
          {menu.map(link)}

          <p className="side-label">Account</p>
          {link(PASSWORD)}
        </nav>

        <div className="side-user">
          <div className="avatar">{(user.name || "?").charAt(0).toUpperCase()}</div>
          <div>
            <strong>{user.name}</strong>
            <small>{user.role}</small>
          </div>
          <button className="side-logout" onClick={logout}>
            Logout
          </button>
        </div>
      </aside>

      <div className="main">
        <main className="container">
          <Outlet />
        </main>
      </div>
    </div>
  );
}