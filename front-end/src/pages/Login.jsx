import { ensureCsrf, xsrfHeaders } from "../csrf";
import { useState } from "react";
import { Link, useNavigate, useSearchParams } from "react-router-dom";

export default function Login() {
  const navigate = useNavigate();
  const [params] = useSearchParams();
  const portal = params.get("portal") || "student";

  const [login, setLogin] = useState("");
  const [password, setPassword] = useState("");
  const [error, setError] = useState("");

  const titles = {
    student: "Student Login",
    faculty: "Faculty Login",
    staff: "Staff Login",
  };

  async function handleSubmit(e) {
    e.preventDefault();
    setError("");

    try {
      await ensureCsrf();
      const res = await fetch("/api/login", {
        method: "POST",
        credentials: "same-origin",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
          ...xsrfHeaders(),
        },
        body: JSON.stringify({ login, password, portal }),
      });
      const data = await res.json();

      if (!res.ok) {
        setError(data.errors?.login?.[0] || data.message || "Login failed.");
        return;
      }

      localStorage.setItem("token", "session");
      localStorage.setItem("user", JSON.stringify(data));
      navigate("/dashboard");
    } catch (err) {
      setError("Cannot reach the server. Make sure Laravel is running.");
    }
  }

  return (
    <div className="auth-wrap">
      <div className="auth-card">
        <div className="auth-side">
          <img src="/logo.png" alt="Calauan Community College logo" className="login-logo" />
          <h2>Calauan Community College</h2>
          <p>
            Student Information and Campus Services Portal. Sign in to
            continue to your account.
          </p>
        </div>

        <form className="auth-form" onSubmit={handleSubmit}>
          <h1>{titles[portal] || titles.student}</h1>
          <p className="sub">Enter your credentials below.</p>

          {error && <div className="alert">{error}</div>}

          <div className="field">
            <label>{portal === "student" ? "Student number" : "Email"}</label>
            <input
              value={login}
              onChange={(e) => setLogin(e.target.value)}
              placeholder={portal === "student" ? "CCC-2026-0001" : "name@email.com"}
            />
          </div>

          <div className="field">
            <label>Password</label>
            <input
              type="password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              placeholder="Your password"
            />
          </div>

          <button type="submit">Login</button>
          <Link to="/forgot-password" className="back-link">Forgot password?</Link>
          <Link to="/" className="back-link">&larr; Back to home</Link>
        </form>
      </div>
    </div>
  );
}