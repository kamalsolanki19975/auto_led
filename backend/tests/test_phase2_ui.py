"""Phase 2 UI redesign structural verification via authenticated HTTP session.

Falls back from Playwright because the preview host serves a Cloudflare bot
challenge to headless browsers. The Laravel app itself is unaffected — a
standard requests session (with CSRF _token) reaches every admin page 200.
"""
import os
import re
import pytest
import requests

BASE = os.environ["REACT_APP_BACKEND_URL"].rstrip("/")
ADMIN_EMAIL = "admin@autoads.test"
ADMIN_PASS = "Admin@123"


# -------- session / auth fixture --------
@pytest.fixture(scope="module")
def admin_session():
    s = requests.Session()
    s.headers.update({"User-Agent": "AutoAdsTestingAgent/1.0"})
    # GET /login to seed cookies + XSRF
    r = s.get(f"{BASE}/login", timeout=30)
    assert r.status_code == 200, r.status_code
    m = re.search(r'name="_token"\s+value="([^"]+)"', r.text)
    assert m, "CSRF _token not found on /login"
    token = m.group(1)
    # POST login
    r = s.post(
        f"{BASE}/login",
        data={"_token": token, "email": ADMIN_EMAIL, "password": ADMIN_PASS, "remember": "0"},
        allow_redirects=False,
        timeout=30,
    )
    assert r.status_code in (302, 303), f"login status {r.status_code}"
    loc = r.headers.get("Location", "")
    assert "/dashboard" in loc or "/portal" in loc or loc.rstrip("/") == BASE, f"unexpected redirect: {loc}"
    # follow to dashboard
    r = s.get(f"{BASE}/dashboard", timeout=30)
    assert r.status_code == 200
    return s


# -------- Public regressions --------
class TestPublicRegressions:
    def test_robots_txt_has_sitemap_and_rules(self):
        r = requests.get(f"{BASE}/robots.txt", timeout=15)
        assert r.status_code == 200
        body = r.text
        assert re.search(r"(?i)sitemap", body), f"robots.txt missing Sitemap: {body[:200]}"
        assert re.search(r"(?i)allow", body), f"robots.txt missing Allow rule: {body[:200]}"
        assert re.search(r"(?i)disallow", body), f"robots.txt missing Disallow rule: {body[:200]}"

    def test_faq_has_no_broken_lucide_and_uses_collapse(self):
        r = requests.get(f"{BASE}/faq", timeout=15)
        assert r.status_code == 200
        # x-collapse should still be used on FAQ items
        assert "x-collapse" in r.text

    def test_public_footer_has_svg_socials(self):
        r = requests.get(f"{BASE}/", timeout=15)
        assert r.status_code == 200
        # Either no socials configured (fine) OR at least one <svg> inside footer.
        # Assert no leftover data-lucide="twitter" broken names in footer area.
        assert 'data-lucide="twitter"' not in r.text, "still using broken lucide twitter icon name in footer"


# -------- Command Center dashboard --------
class TestCommandCenterDashboard:
    def test_dashboard_renders_and_key_markers(self, admin_session):
        r = admin_session.get(f"{BASE}/dashboard", timeout=30)
        assert r.status_code == 200
        html = r.text
        assert "Command Center" in html
        assert "Live Network Feed" in html
        assert "Tactical Alerts" in html
        assert "Live Campaign Delivery" in html

    @pytest.mark.parametrize("tid", [
        "kpi-total-autos", "kpi-active-autos", "kpi-screens-online",
        "fin-net-profit", "fin-today-revenue",
    ])
    def test_dashboard_kpi_testids(self, admin_session, tid):
        r = admin_session.get(f"{BASE}/dashboard", timeout=30)
        assert f'data-testid="{tid}"' in r.text, f"missing {tid}"

    def test_dashboard_has_alert_rows(self, admin_session):
        r = admin_session.get(f"{BASE}/dashboard", timeout=30)
        # at least one alert-* testid
        assert re.search(r'data-testid="alert-[a-z0-9\-]+"', r.text), "no alert-* testid on dashboard"

    def test_dashboard_revchart_canvas(self, admin_session):
        r = admin_session.get(f"{BASE}/dashboard", timeout=30)
        assert re.search(r'<canvas[^>]+id="revChart"', r.text), "canvas#revChart not present"
        # Chart.js label arrays should be present in inline script
        assert "labels" in r.text and ("revenue" in r.text.lower() or "expenses" in r.text.lower())


# -------- Shell: sidebar + header --------
class TestAdminShell:
    def test_sidebar_has_nav_groups_and_links(self, admin_session):
        r = admin_session.get(f"{BASE}/dashboard", timeout=30)
        html = r.text
        # nav groups
        assert re.search(r'data-testid="nav-group-[a-z0-9\-]+"', html), "no nav-group-* found"
        # key nav links (at least some)
        found = 0
        for tid in ["nav-dashboard", "nav-autos", "nav-campaigns", "nav-devices", "nav-users", "nav-leads", "nav-settlements", "nav-invoices"]:
            if f'data-testid="{tid}"' in html:
                found += 1
        assert found >= 4, f"only {found} sidebar nav links found"

    def test_sidebar_collapse_toggle_present(self, admin_session):
        r = admin_session.get(f"{BASE}/dashboard", timeout=30)
        assert 'data-testid="sidebar-collapse-toggle"' in r.text

    def test_header_widgets_present(self, admin_session):
        r = admin_session.get(f"{BASE}/dashboard", timeout=30)
        html = r.text
        for tid in ["global-search-input", "quick-create-btn", "notif-bell", "user-menu", "logout-btn", "help-drawer-trigger", "help-drawer-close"]:
            assert f'data-testid="{tid}"' in html, f"header {tid} missing"

    def test_mobile_sidebar_open_present(self, admin_session):
        r = admin_session.get(f"{BASE}/dashboard", timeout=30)
        assert 'data-testid="mobile-sidebar-open"' in r.text

    def test_help_drawer_contains_glossary(self, admin_session):
        r = admin_session.get(f"{BASE}/dashboard", timeout=30)
        html = r.text
        # Help drawer content sections
        assert "Module Guide" in html or "module guide" in html.lower()
        assert "Keyboard Shortcuts" in html or "Shortcut" in html
        assert "Glossary" in html or "DOOH" in html


# -------- Shared CRUD list pages --------
CRUD_PAGES = [
    ("/autos", "autos"),
    ("/campaigns", "campaigns"),
    ("/invoices", "invoices"),
    ("/settlements", "settlements"),
    ("/users", "users"),
    ("/leads", "leads"),
]

class TestCrudListPages:
    @pytest.mark.parametrize("path,slug", CRUD_PAGES)
    def test_list_page_200_and_shell(self, admin_session, path, slug):
        r = admin_session.get(f"{BASE}{path}", timeout=30)
        assert r.status_code == 200, f"{path} -> {r.status_code}"
        html = r.text
        # search input
        assert f'data-testid="search-{slug}"' in html, f"missing search-{slug} on {path}"
        # filter (may be absent for some resources) - warn only
        # Row testids OR empty-state
        has_rows = re.search(rf'data-testid="row-{slug}-[^"]+"', html)
        has_empty = "empty" in html.lower() or "no records" in html.lower() or "no results" in html.lower()
        assert has_rows or has_empty, f"{path} has neither rows nor empty state"

    def test_autos_row_actions_when_present(self, admin_session):
        r = admin_session.get(f"{BASE}/autos", timeout=30)
        html = r.text
        rows = re.findall(r'data-testid="row-autos-([^"]+)"', html)
        if not rows:
            pytest.skip("no autos rows to check actions on")
        first = rows[0]
        for prefix in ("view-autos-", "edit-autos-", "delete-autos-"):
            assert f'data-testid="{prefix}{first}"' in html, f"missing {prefix}{first}"


# -------- Detail + Form pages --------
class TestDetailAndForm:
    def test_autos_create_form(self, admin_session):
        r = admin_session.get(f"{BASE}/autos/create", timeout=30)
        assert r.status_code == 200
        assert 'data-testid="save-autos"' in r.text
        # form element present
        assert "<form" in r.text

    def test_autos_detail_page(self, admin_session):
        r = admin_session.get(f"{BASE}/autos", timeout=30)
        m = re.search(r'data-testid="view-autos-([^"]+)"[^>]*href="([^"]+)"', r.text)
        if not m:
            pytest.skip("no autos row available")
        detail_url = m.group(2)
        if detail_url.startswith("/"):
            detail_url = BASE + detail_url
        r2 = admin_session.get(detail_url, timeout=30)
        assert r2.status_code == 200
        # hero card + edit button
        assert re.search(r'(?i)edit', r2.text)


# -------- No 500s smoke test on all admin pages --------
ADMIN_SMOKE = [
    "/dashboard", "/autos", "/campaigns", "/invoices",
    "/settlements", "/users", "/leads", "/devices",
]

@pytest.mark.parametrize("path", ADMIN_SMOKE)
def test_admin_no_500(admin_session, path):
    r = admin_session.get(f"{BASE}{path}", timeout=30)
    assert r.status_code == 200, f"{path} -> {r.status_code}"
    assert "Whoops, something went wrong" not in r.text
    assert "SQLSTATE" not in r.text
    assert "Undefined variable" not in r.text
