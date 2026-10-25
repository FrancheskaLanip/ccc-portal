import { useState } from "react";
import { Link, useSearchParams } from "react-router-dom";
import { api, errorText } from "../csrf";

export default function ResetPassword() {
  const [params] = useSearchParams();

  const [form, setForm] = useState({
    email: params.get("email") || "",
    code: "",
    password: "",
    password_confirmation: "",
  });
  const [message, setMessage] = useState("");
  const [error, setError] = useState("");
  const [busy, setBusy] = useState(false);

  function change(e) {
    setForm({ ...form, [e.target.name]: e.target.value });
  }

  async function handleSubmit(e) {
    e.preventDefault();
    setMessage("");
    setError("");
    setBusy(true);

    try {
      const data = await api("/reset-password", { method: "POST", body: form });
      setMessage(data?.message || "Password changed. You can log in now.");
      setForm({ ...form, code: "", password: "", password_confirmation: "" });
    } catch (err) {
      setError(errorText(err));
    } finally {
      setBusy(false);
    }
  }

  return (
    <div className="auth-wrap">
      <div className="auth-card">
        <div className="auth-side">
          <img src="/logo.png" alt="Calauan Community College logo" className="login-logo" />
          <h2>Calauan Community College</h2>
          <p>Enter the code we emailed you and choose a new password.</p>
        </div>

        <form className="auth-form" onSubmit={handleSubmit}>
          <h1>Reset Password</h1>
          <p className="sub">The code is valid for 15 minutes.</p>

          {message && <div className="success">{message}</div>}
          {error && <div className="alert">{error}</div>}

          <div className="field">
            <label>Registered email</label>
            <input type="email" name="email" value={form.email} onChange={change} required />
          </div>

          <div className="field">
            <label>Code</label>
            <input
              name="code"
              value={form.code}
              onChange={change}
              inputMode="numeric"
              maxLength={6}
              placeholder="6-digit code"
              required
            />
          </div>

          <div className="field">
            <label>New password</label>
            <input type="password" name="password" value={form.password} onChange={change} required />
          </div>

          <div className="field">
            <label>Confirm new password</label>
            <input type="password" name="password_confirmation" value={form.password_confirmation} onChange={change} required />
          </div>

          <button type="submit" disabled={busy}>
            {busy ? "Saving..." : "Change password"}
          </button>

          <Link to="/forgot-password" className="back-link">Send me another email</Link>
          <Link to="/login" className="back-link">&larr; Back to login</Link>
        </form>
      </div>
    </div>
  );
}