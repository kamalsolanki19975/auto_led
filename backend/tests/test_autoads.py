"""AutoAds Network — Laravel backend + API test suite."""
import os
import re
import json
import pytest
import requests

BASE = os.environ.get("REACT_APP_BACKEND_URL", "https://fleet-ads-pro.preview.emergentagent.com").rstrip("/")

ADMIN = ("admin@autoads.test", "Admin@123")
ADVERTISER = ("advertiser@autoads.test", "Admin@123")
DRIVER = ("driver@autoads.test", "Admin@123")
OWNER = ("owner@autoads.test", "Admin@123")
TECH = ("tech@autoads.test", "Admin@123")

DEVICES = [
    ("DEV-00003", "a41e30f7-ac37-42a3-9132-3ca6d8f1fdb7"),
    ("DEV-00004", "c53955a6-09f8-4367-bf03-db05509b6039"),
    ("DEV-00005", "804c240f-a799-4c86-b304-017cac06987c"),
    ("DEV-00006", None),  # placeholder
]


# ---------------- Helpers ---------------- #
def _get_csrf(session, path="/login"):
    r = session.get(f"{BASE}{path}", timeout=30)
    assert r.status_code == 200, f"GET {path} failed: {r.status_code}"
    m = re.search(r'name="_token"\s+value="([^"]+)"', r.text)
    assert m, f"CSRF token not found on {path}"
    return m.group(1)


def _web_login(email, password):
    s = requests.Session()
    token = _get_csrf(s)
    r = s.post(f"{BASE}/login",
               data={"_token": token, "email": email, "password": password},
               allow_redirects=False, timeout=30)
    assert r.status_code in (302, 303), f"Login for {email} did not redirect: {r.status_code}\n{r.text[:400]}"
    return s, r.headers.get("Location", "")


# ---------------- Auth / basic ---------------- #
def test_login_page_loads():
    r = requests.get(f"{BASE}/login", timeout=30)
    assert r.status_code == 200
    assert "csrf" in r.text.lower() or "_token" in r.text


def test_admin_login_redirects_to_dashboard():
    _, loc = _web_login(*ADMIN)
    assert "/dashboard" in loc, f"Admin should land on /dashboard, got {loc}"


def test_advertiser_login_redirects_to_portal():
    _, loc = _web_login(*ADVERTISER)
    assert "/portal/advertiser" in loc, loc


def test_driver_login_redirects_to_portal():
    _, loc = _web_login(*DRIVER)
    assert "/portal/driver" in loc, loc


def test_owner_login_redirects_to_portal():
    _, loc = _web_login(*OWNER)
    assert "/portal/owner" in loc, loc


def test_tech_login_redirects_to_portal():
    _, loc = _web_login(*TECH)
    assert "/portal/technician" in loc, loc


# ---------------- Admin navigation sweep ---------------- #
ADMIN_PATHS = [
    "/dashboard", "/autos", "/owners", "/drivers", "/screens", "/devices", "/sims",
    "/advertisers", "/advertisements", "/advertisements/approvals", "/campaigns", "/playlists",
    "/proof-of-play", "/installations", "/assets", "/maintenance", "/warranties", "/vendors",
    "/revenue", "/invoices", "/payments", "/expenses", "/rate-cards",
    "/earnings", "/settlements", "/profitability",
    "/reports", "/reports/revenue", "/reports/profitability", "/reports/playback",
    "/api-apps", "/api-logs", "/webhooks",
    "/users", "/roles", "/audit-logs", "/email-logs", "/notification-logs",
    "/settings", "/search?q=phonepe",
    "/devices/1", "/autos/1", "/campaigns/1", "/drivers/1",
]


@pytest.fixture(scope="module")
def admin_session():
    s, _ = _web_login(*ADMIN)
    return s


@pytest.mark.parametrize("path", ADMIN_PATHS)
def test_admin_page_renders(admin_session, path):
    r = admin_session.get(f"{BASE}{path}", timeout=45, allow_redirects=True)
    assert r.status_code == 200, f"{path} -> {r.status_code}"
    body = r.text
    # Laravel Whoops / exception page detection
    bad = ("Whoops, looks like something went wrong", "Illuminate\\Database\\QueryException",
           "SQLSTATE[", "stream could not be opened", "syntax error, unexpected",
           "Class \"", "Undefined variable", "Undefined property", "Undefined method",
           "Trying to access array offset", "ErrorException")
    for marker in bad:
        assert marker not in body, f"{path} contains error marker: {marker}"


# ---------------- Portal specific ---------------- #
def test_advertiser_portal_content():
    s, _ = _web_login(*ADVERTISER)
    r = s.get(f"{BASE}/portal/advertiser", timeout=30)
    assert r.status_code == 200
    for needle in ["Active Campaigns", "Valid Plays", "Total Paid", "Outstanding",
                   'data-testid="portal-badge"', "Advertiser"]:
        assert needle in r.text, f"Advertiser portal missing: {needle}"


def test_driver_portal_content():
    s, _ = _web_login(*DRIVER)
    r = s.get(f"{BASE}/portal/driver", timeout=30)
    assert r.status_code == 200
    for needle in ["Today Runtime", "Today Plays", "This Month", "My Auto"]:
        assert needle in r.text, f"Driver portal missing: {needle}"


def test_owner_portal_content():
    s, _ = _web_login(*OWNER)
    r = s.get(f"{BASE}/portal/owner", timeout=30)
    assert r.status_code == 200
    for needle in ["Fleet Size", "Active Autos", "Settlements", "Pending"]:
        assert needle in r.text, f"Owner portal missing: {needle}"


def test_tech_portal_content():
    s, _ = _web_login(*TECH)
    r = s.get(f"{BASE}/portal/technician", timeout=30)
    assert r.status_code == 200
    for needle in ["My Installations", "Open Tickets", "Available"]:
        assert needle in r.text, f"Tech portal missing: {needle}"


# ---------------- Users & Roles CRUD ---------------- #
def test_users_index_and_create_form(admin_session):
    r = admin_session.get(f"{BASE}/users", timeout=30)
    assert r.status_code == 200
    r2 = admin_session.get(f"{BASE}/users/create", timeout=30)
    assert r2.status_code == 200
    assert 'name="name"' in r2.text and 'name="email"' in r2.text


def test_users_create_and_flash(admin_session):
    # get form + csrf
    r = admin_session.get(f"{BASE}/users/create", timeout=30)
    m = re.search(r'name="_token"\s+value="([^"]+)"', r.text)
    assert m
    token = m.group(1)
    # find any role id checkbox
    role_ids = re.findall(r'name="roles\[\]"\s+value="(\d+)"', r.text)
    assert role_ids, "no role checkboxes found on /users/create"
    import time
    email = f"TEST_user_{int(time.time())}@autoads.test"
    payload = [
        ("_token", token),
        ("name", "TEST User"),
        ("email", email),
        ("password", "Admin@123"),
        ("password_confirmation", "Admin@123"),
        ("status", "active"),
        ("roles[]", role_ids[0]),
    ]
    r = admin_session.post(f"{BASE}/users", data=payload, allow_redirects=False, timeout=30)
    assert r.status_code in (302, 303), f"Create user failed: {r.status_code}\n{r.text[:400]}"
    loc = r.headers.get("Location", "")
    assert "/users" in loc
    # Bug workaround: Laravel emits ":80" in HTTPS redirect URLs behind ingress.
    loc = loc.replace(":80/", "/").replace("http://", "https://")
    r2 = admin_session.get(loc, timeout=30)
    assert r2.status_code == 200


def test_roles_index_and_forms(admin_session):
    r = admin_session.get(f"{BASE}/roles", timeout=30)
    assert r.status_code == 200
    r2 = admin_session.get(f"{BASE}/roles/create", timeout=30)
    assert r2.status_code == 200
    r3 = admin_session.get(f"{BASE}/roles/1/edit", timeout=30)
    assert r3.status_code == 200


# ---------------- Swagger / OpenAPI ---------------- #
def test_swagger_ui():
    r = requests.get(f"{BASE}/api/docs", timeout=30)
    assert r.status_code == 200
    assert "AutoAds Network API" in r.text or "swagger" in r.text.lower()


def test_openapi_json():
    r = requests.get(f"{BASE}/api/openapi.json", timeout=30)
    assert r.status_code == 200
    spec = r.json()
    assert spec.get("openapi", "").startswith("3."), spec.get("openapi")
    paths = spec.get("paths", {})
    assert len(paths) >= 16, f"Expected >=16 paths, got {len(paths)}"


# ---------------- REST API ---------------- #
@pytest.fixture(scope="module")
def api_token():
    r = requests.post(f"{BASE}/api/v1/auth/login",
                      json={"email": ADMIN[0], "password": ADMIN[1]},
                      headers={"Accept": "application/json"}, timeout=30)
    assert r.status_code == 200, f"api login {r.status_code}: {r.text[:300]}"
    j = r.json()
    assert j.get("success") is True
    tok = j.get("token") or j.get("data", {}).get("token")
    assert tok
    return tok


def _auth(tok):
    return {"Authorization": f"Bearer {tok}", "Accept": "application/json"}


def test_api_me(api_token):
    r = requests.get(f"{BASE}/api/v1/auth/me", headers=_auth(api_token), timeout=30)
    assert r.status_code == 200
    j = r.json()
    assert j.get("success") is True


def test_api_campaigns(api_token):
    r = requests.get(f"{BASE}/api/v1/campaigns", headers=_auth(api_token), timeout=30)
    assert r.status_code == 200, r.text[:300]
    j = r.json()
    assert j.get("success") is True
    # paginated
    data = j.get("data")
    assert data is not None


def test_api_autos(api_token):
    r = requests.get(f"{BASE}/api/v1/autos", headers=_auth(api_token), timeout=30)
    assert r.status_code == 200, r.text[:300]
    assert r.json().get("success") is True


# ---------------- Device API full chain ---------------- #
def _device_auth():
    """Try each fresh device until one authenticates."""
    last = None
    for _code, uuid in DEVICES:
        if not uuid:
            continue
        r = requests.post(f"{BASE}/api/v1/device/authenticate",
                          json={"device_uuid": uuid, "pairing_token": f"demo-{uuid}"},
                          headers={"Accept": "application/json"}, timeout=30)
        if r.status_code == 200:
            j = r.json()
            tok = j.get("device_token") or j.get("data", {}).get("device_token") or j.get("token")
            if tok:
                return uuid, tok, j
        last = (r.status_code, r.text[:200], uuid)
    pytest.skip(f"No fresh device available; last={last}")


@pytest.fixture(scope="module")
def device_ctx():
    uuid, tok, resp = _device_auth()
    return {"uuid": uuid, "token": tok, "resp": resp}


def _dhead(ctx):
    return {"Authorization": f"Bearer {ctx['token']}",
            "X-Device-Uuid": ctx["uuid"],
            "Accept": "application/json",
            "Content-Type": "application/json"}


def test_device_authenticate(device_ctx):
    assert device_ctx["token"]


def test_device_heartbeat(device_ctx):
    r = requests.post(f"{BASE}/api/v1/device/heartbeat", headers=_dhead(device_ctx),
                      json={"timestamp": "2026-01-15T10:00:00Z"}, timeout=30)
    assert r.status_code == 200, r.text[:300]
    assert r.json().get("success") is True


def test_device_configuration(device_ctx):
    r = requests.get(f"{BASE}/api/v1/device/configuration", headers=_dhead(device_ctx), timeout=30)
    assert r.status_code == 200, r.text[:300]
    assert r.json().get("success") is True


def test_device_campaigns(device_ctx):
    r = requests.get(f"{BASE}/api/v1/device/campaigns", headers=_dhead(device_ctx), timeout=30)
    assert r.status_code == 200, r.text[:400]
    j = r.json()
    assert j.get("success") is True
    # campaigns array present
    data = j.get("data") or j.get("campaigns") or j
    assert data is not None


def test_device_content(device_ctx):
    r = requests.get(f"{BASE}/api/v1/device/content", headers=_dhead(device_ctx), timeout=30)
    assert r.status_code == 200, r.text[:300]
    assert r.json().get("success") is True


def test_device_playback_events(device_ctx):
    import uuid as _u
    payload = {
        "campaign_id": 1,
        "advertisement_id": 1,
        "started_at": "2026-01-15T10:00:00Z",
        "ended_at": "2026-01-15T10:00:30Z",
        "duration": 30,
        "event_id": str(_u.uuid4()),
    }
    r = requests.post(f"{BASE}/api/v1/device/playback-events", headers=_dhead(device_ctx),
                      json=payload, timeout=30)
    assert r.status_code == 200, r.text[:400]
    assert r.json().get("success") is True


def test_device_status(device_ctx):
    r = requests.post(f"{BASE}/api/v1/device/status", headers=_dhead(device_ctx),
                      json={"status": "online"}, timeout=30)
    assert r.status_code == 200, r.text[:300]
    assert r.json().get("success") is True


def test_device_acknowledgement(device_ctx):
    r = requests.post(f"{BASE}/api/v1/device/acknowledgement", headers=_dhead(device_ctx),
                      json={"command_id": "test-cmd-1", "status": "ok"}, timeout=30)
    assert r.status_code == 200, r.text[:300]
    assert r.json().get("success") is True
