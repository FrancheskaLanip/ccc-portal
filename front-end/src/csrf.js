// Helpers for talking to the Laravel back-end (session cookie + CSRF token).

export async function ensureCsrf() {
  // Any web route sets the XSRF-TOKEN cookie; /api/me is the lightest one.
  await fetch("/api/me", {
    headers: { Accept: "application/json" },
    credentials: "same-origin",
  });
}

export function xsrfHeaders() {
  const m = document.cookie.match(/(?:^|; )XSRF-TOKEN=([^;]+)/);
  return m ? { "X-XSRF-TOKEN": decodeURIComponent(m[1]) } : {};
}

// api("/students")
// api("/students", { method: "POST", body: { ... } })
// Returns the JSON (or null for an empty response). Throws an Error with
// err.errors (Laravel validation messages) when the request fails.
export async function api(path, { method = "GET", body } = {}) {
  const send = () =>
    fetch("/api" + path, {
      method,
      credentials: "same-origin",
      headers: {
        Accept: "application/json",
        ...(body ? { "Content-Type": "application/json" } : {}),
        ...xsrfHeaders(),
      },
      body: body ? JSON.stringify(body) : undefined,
    });

  if (method !== "GET" && !document.cookie.includes("XSRF-TOKEN")) {
    await ensureCsrf();
  }

  let res = await send();

  // Expired CSRF token: get a fresh one and try once more
  if (res.status === 419) {
    await ensureCsrf();
    res = await send();
  }

  // Not logged in (session expired)
  if (res.status === 401) {
    localStorage.clear();
    window.location.href = "/";
    return null;
  }

  if (res.status === 204) return null;

  const data = await res.json().catch(() => null);
  if (!res.ok) {
    const err = new Error(data?.message || "Request failed.");
    err.errors = data?.errors || {};
    throw err;
  }
  return data;
}

// Turns a failed request into one readable sentence
export function errorText(err) {
  const list = Object.values(err.errors || {}).flat();
  return list.length ? list.join(" ") : err.message;
}
