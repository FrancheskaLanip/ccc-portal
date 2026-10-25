import { useState } from "react";
import { Link } from "react-router-dom";
import { api, errorText } from "../csrf";

export default function ForgotPassword() {
  const [email, setEmail] = useState("");
  const [message, setMessage] = useState("");
  const [error, setError] = useState("");
  const [busy, setBusy] = useState(false);

  async function handleSubmit(e) {
    e.preventDefault();
    setMessage("");
    setError("");
    setBusy(true);

    try {
      const data = await api("/forgot-password", { method: "POST", body: { email } });
      setMessage(data?.message || "If the email is registered, we sent an email to it. Check your inbox.");
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
          <p>
            The first time, we email your login credentials. After that, we email a code so you
            can set a new password.
          </p>
        </div>

        <form className="auth-form" onSubmit={handleSubmit}>
          <h1>Forgot Password</h1>
          <p className="sub">Enter your registered email below.</p>

          {message && <div className="success">{message}</div>}
          {error && <div className="alert">{error}</div>}

          <div className="field">
            <label>Registered email</label>
            <input
              type="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              placeholder="name@email.com"
              required
            />
          </div>

          <button type="submit" disabled={busy}>
            {busy ? "Sending..." : "Send email"}
          </button>

          {message && (
            <Link to={"/reset-password?email=" + encodeURIComponent(email)} className="back-link">
              The email has a code? Enter it here
            </Link>
          )}
          <Link to="/login" className="back-link">&larr; Back to login</Link>
        </form>
      </div>
    </div>
  );
}