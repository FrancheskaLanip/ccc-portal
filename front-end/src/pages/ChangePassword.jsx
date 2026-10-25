import { useState } from "react";
import { Link, useNavigate } from "react-router-dom";
import { api, errorText } from "../csrf";

export default function ChangePassword() {
  const navigate = useNavigate();
  const [form, setForm] = useState({
    current_password: "",
    password: "",
    password_confirmation: "",
  });
  const [error, setError] = useState("");
  const [busy, setBusy] = useState(false);

  function change(e) {
    setForm({ ...form, [e.target.name]: e.target.value });
  }

  async function save(e) {
    e.preventDefault();
    setError("");
    setBusy(true);

    try {
      await api("/password/change", { method: "POST", body: form });
      navigate("/dashboard", { state: { message: "Password changed." } });
    } catch (err) {
      setError(errorText(err));
    } finally {
      setBusy(false);
    }
  }

  return (
    <div>
      <h1>Change Password</h1>

      {error && <div className="alert">{error}</div>}

      <form className="form-panel narrow" onSubmit={save}>
        <div className="field">
          <label>Current password</label>
          <input type="password" name="current_password" value={form.current_password} onChange={change} required />
        </div>
        <div className="field">
          <label>New password</label>
          <input type="password" name="password" value={form.password} onChange={change} required />
        </div>
        <div className="field">
          <label>Confirm new password</label>
          <input type="password" name="password_confirmation" value={form.password_confirmation} onChange={change} required />
        </div>
        <div className="actions">
          <button type="submit" disabled={busy}>
            {busy ? "Saving..." : "Save password"}
          </button>
        </div>
      </form>

      <p>
        <Link to="/dashboard" className="back-link">&larr; Back to dashboard</Link>
      </p>
    </div>
  );
}
