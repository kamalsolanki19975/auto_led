"""AutoAds Network — SEC-001..004 + rate limit + docs gating regression."""
import os
import re
import time
import pytest
import requests

BASE = os.environ.get("REACT_APP_BACKEND_URL",
                      "https://fleet-ads-pro.preview.emergentagent.com").rstrip("/")

ADMIN = ("admin@autoads.test", "Admin@123")
ADVERTISER = ("advertiser@autoads.test", "Admin@123")
DRIVER = ("driver@autoads.test", "Admin@123")


# --------- helpers --------- #
def _get_csrf(session, path="/login"):
    r = session.get(f"{BASE}{path}", timeout=30)
    assert r.status_code == 200, f"GET {path} -> {r.status_code}"
    m = re.search(r'name="_token"\s+value="([^"]+)"', r.text)
    assert m, f"CSRF not found on {path}"
    return m.group(1)


def _web_login(email, password):
    s = requests.Session()
    tok = _get_csrf(s)
    r = s.post(f"{BASE}/login", data={"_token": tok, "email": email, "password": password},
               allow_redirects=False, timeout=30)
    assert r.status_code in (302, 303), f"login {email} -> {r.status_code}"
    return s


def _api_login(email, password):
    # Retry on 429 (rate limit tests may consume bucket)
    for attempt in range(8):
        r = requests.post(f"{BASE}/api/v1/auth/login",
                          json={"email": email, "password": password},
                          headers={"Accept": "application/json"}, timeout=30)
        if r.status_code == 200:
            j = r.json()
            return j.get("token") or j.get("data", {}).get("token")
        if r.status_code == 429:
            time.sleep(20)
            continue
        break
    assert r.status_code == 200, f"api login {email} -> {r.status_code}: {r.text[:300]}"


def _auth(tok):
    return {"Authorization": f"Bearer {tok}", "Accept": "application/json"}


# --------- SEC-001: REST API authorization --------- #
class TestSec001Authorization:
    def test_advertiser_forbidden_on_owners(self):
        tok = _api_login(*ADVERTISER)
        r = requests.get(f"{BASE}/api/v1/owners", headers=_auth(tok), timeout=30)
        assert r.status_code == 403, f"expected 403 got {r.status_code}: {r.text[:200]}"

    def test_advertiser_forbidden_on_drivers(self):
        tok = _api_login(*ADVERTISER)
        r = requests.get(f"{BASE}/api/v1/drivers", headers=_auth(tok), timeout=30)
        assert r.status_code == 403

    def test_advertiser_forbidden_on_payments(self):
        tok = _api_login(*ADVERTISER)
        r = requests.get(f"{BASE}/api/v1/payments", headers=_auth(tok), timeout=30)
        # Advertiser is scoped for payments (advertiser_id) → should be 200 (only own)
        # but /payments require permission finance.payment.view — advertiser lacks it → 403
        assert r.status_code == 403, f"expected 403 got {r.status_code}"

    def test_advertiser_forbidden_on_settlements(self):
        tok = _api_login(*ADVERTISER)
        r = requests.get(f"{BASE}/api/v1/settlements", headers=_auth(tok), timeout=30)
        assert r.status_code == 403

    def test_advertiser_campaigns_scoped(self):
        tok = _api_login(*ADVERTISER)
        r = requests.get(f"{BASE}/api/v1/campaigns?per_page=100",
                         headers=_auth(tok), timeout=30)
        assert r.status_code == 200, r.text[:200]
        j = r.json()
        assert j.get("success") is True
        rows = j.get("data") or []
        # advertiser user has advertiser_id=1
        for c in rows:
            assert c.get("advertiser_id") == 1, f"leak: campaign {c.get('id')} advertiser_id={c.get('advertiser_id')}"

    def test_advertiser_cannot_read_other_campaign(self):
        tok = _api_login(*ADVERTISER)
        # campaign id 1 belongs to advertiser_id=8 (not 1)
        r = requests.get(f"{BASE}/api/v1/campaigns/1", headers=_auth(tok), timeout=30)
        assert r.status_code == 404, f"expected 404 got {r.status_code}"

    def test_driver_settlements_scoped(self):
        tok = _api_login(*DRIVER)
        r = requests.get(f"{BASE}/api/v1/settlements?per_page=100",
                         headers=_auth(tok), timeout=30)
        assert r.status_code == 200, r.text[:200]
        for row in (r.json().get("data") or []):
            assert row.get("driver_id") == 1, f"leak: settlement {row.get('id')} driver_id={row.get('driver_id')}"

    def test_driver_forbidden_on_autos(self):
        tok = _api_login(*DRIVER)
        r = requests.get(f"{BASE}/api/v1/autos", headers=_auth(tok), timeout=30)
        assert r.status_code == 403

    def test_admin_has_full_access(self):
        tok = _api_login(*ADMIN)
        r = requests.get(f"{BASE}/api/v1/owners", headers=_auth(tok), timeout=30)
        assert r.status_code == 200


# --------- SEC-002: Debug OFF (no stack trace) --------- #
class TestSec002DebugOff:
    BAD_MARKERS = ["/app/laravel", "Illuminate\\", "SQLSTATE[", "Whoops",
                   "Stack trace:", "APP_KEY=", "DB_PASSWORD"]

    def test_404_generic_page(self):
        r = requests.get(f"{BASE}/nonexistent-xyz-security-audit", timeout=30,
                         allow_redirects=False)
        assert r.status_code in (404, 302), f"got {r.status_code}"
        body = r.text
        for m in self.BAD_MARKERS:
            assert m not in body, f"debug leak: {m}"

    def test_500_generic_page_admin(self):
        s = _web_login(*ADMIN)
        r = s.get(f"{BASE}/users/99999/edit", timeout=30)
        # Either 404 or 500 both must be generic
        assert r.status_code >= 400
        for m in self.BAD_MARKERS:
            assert m not in r.text, f"debug leak: {m}"


# --------- SEC-003: Dispute IDOR --------- #
class TestSec003DisputeIDOR:
    def _get_dispute_csrf(self, session):
        # portal page has the form
        r = session.get(f"{BASE}/portal/driver", timeout=30)
        m = re.search(r'name="_token"\s+value="([^"]+)"', r.text)
        assert m, "csrf not found on portal/driver"
        return m.group(1)

    def test_driver_cannot_dispute_others_settlement(self):
        s = _web_login(*DRIVER)
        tok = self._get_dispute_csrf(s)
        # settlement id 2 belongs to driver_id=2 (not driver user's driver_id=1)
        r = s.post(f"{BASE}/portal/settlements/2/dispute",
                   data={"_token": tok, "reason": "SEC test", "disputed_amount": 10},
                   allow_redirects=False, timeout=30)
        assert r.status_code == 403, f"IDOR: expected 403, got {r.status_code}"

    def test_driver_can_dispute_own_settlement(self):
        s = _web_login(*DRIVER)
        tok = self._get_dispute_csrf(s)
        # settlement id 1 belongs to driver_id=1 — own
        r = s.post(f"{BASE}/portal/settlements/1/dispute",
                   data={"_token": tok, "reason": "SEC test own", "disputed_amount": 5},
                   allow_redirects=False, timeout=30)
        assert r.status_code in (302, 303), f"own dispute: expected 302, got {r.status_code}: {r.text[:200]}"


# --------- SEC-004: Mass assignment / user create --------- #
class TestSec004UserCreate:
    def test_admin_can_create_user(self):
        s = _web_login(*ADMIN)
        r = s.get(f"{BASE}/users/create", timeout=30)
        assert r.status_code == 200
        m = re.search(r'name="_token"\s+value="([^"]+)"', r.text)
        assert m
        tok = m.group(1)
        role_ids = re.findall(r'name="roles\[\]"\s+value="(\d+)"', r.text)
        assert role_ids, "no role checkboxes found"
        email = f"TEST_secuser_{int(time.time())}@autoads.test"
        payload = [
            ("_token", tok),
            ("name", "TEST SecUser"),
            ("email", email),
            ("password", "Admin@123"),
            ("password_confirmation", "Admin@123"),
            ("status", "active"),
            ("roles[]", role_ids[0]),
        ]
        r = s.post(f"{BASE}/users", data=payload, allow_redirects=False, timeout=30)
        assert r.status_code in (302, 303), f"user create -> {r.status_code}: {r.text[:300]}"
        # follow to /users and verify presence
        loc = r.headers.get("Location", "/users").replace(":80/", "/")
        r2 = s.get(loc, timeout=30)
        assert r2.status_code == 200
        # index page may or may not include the new email (pagination) but flash success or /users load
        assert "success" in r2.text.lower() or email in r2.text or "Users" in r2.text


# --------- API docs gating --------- #
class TestApiDocsGating:
    def test_docs_guest_401(self):
        r = requests.get(f"{BASE}/api/docs", timeout=30, allow_redirects=False)
        assert r.status_code in (401, 302, 403), f"got {r.status_code}"

    def test_openapi_guest_401(self):
        r = requests.get(f"{BASE}/api/openapi.json", timeout=30, allow_redirects=False,
                         headers={"Accept": "application/json"})
        assert r.status_code in (401, 302, 403), f"got {r.status_code}"

    def test_docs_admin_session_200(self):
        s = _web_login(*ADMIN)
        r = s.get(f"{BASE}/api/docs", timeout=30)
        assert r.status_code == 200
        assert "swagger" in r.text.lower() or "AutoAds" in r.text

    def test_openapi_admin_session_200(self):
        s = _web_login(*ADMIN)
        r = s.get(f"{BASE}/api/openapi.json", timeout=30)
        assert r.status_code == 200
        spec = r.json()
        assert len(spec.get("paths", {})) >= 16


# --------- Rate limiting --------- #
class TestRateLimit:
    def test_login_throttle_11th_returns_429(self):
        codes = []
        for _ in range(12):
            r = requests.post(f"{BASE}/api/v1/auth/login",
                              json={"email": "nope@autoads.test", "password": "bad"},
                              headers={"Accept": "application/json"}, timeout=20)
            codes.append(r.status_code)
            if r.status_code == 429:
                break
        assert 429 in codes, f"no 429 in {codes}"

    def test_device_authenticate_throttle(self):
        codes = []
        for i in range(12):
            r = requests.post(f"{BASE}/api/v1/device/authenticate",
                              json={"device_uuid": "00000000-0000-0000-0000-000000000000",
                                    "pairing_token": "bogus"},
                              headers={"Accept": "application/json"}, timeout=20)
            codes.append(r.status_code)
            if r.status_code == 429:
                break
        assert 429 in codes, f"no 429 in {codes}"
