"""Phase 1 tests: public marketing site, contact/lead flow, admin leads inbox,
branding settings, admin in-app notification, and regressions.
"""
import os
import re
import time
import pytest
import requests

BASE = os.environ.get("REACT_APP_BACKEND_URL", "https://fleet-ads-pro.preview.emergentagent.com").rstrip("/")

ADMIN = ("admin@autoads.test", "Admin@123")
PORTALS = {
    "advertiser": ("advertiser@autoads.test", "/portal/advertiser"),
    "driver": ("driver@autoads.test", "/portal/driver"),
    "owner": ("owner@autoads.test", "/portal/owner"),
    "tech": ("tech@autoads.test", "/portal/technician"),
}

BAD_MARKERS = (
    "Whoops, looks like something went wrong",
    "Illuminate\\Database\\QueryException",
    "SQLSTATE[",
    "stream could not be opened",
    "syntax error, unexpected",
    "Undefined variable",
    "Undefined property",
    "Undefined method",
    "ErrorException",
)


def _csrf(session, path="/login"):
    r = session.get(f"{BASE}{path}", timeout=30)
    assert r.status_code == 200, f"GET {path} -> {r.status_code}"
    m = re.search(r'name="_token"\s+value="([^"]+)"', r.text)
    assert m, f"CSRF not found on {path}"
    return m.group(1)


def _login(email, password="Admin@123"):
    s = requests.Session()
    tok = _csrf(s)
    r = s.post(f"{BASE}/login", data={"_token": tok, "email": email, "password": password},
               allow_redirects=False, timeout=30)
    assert r.status_code in (302, 303), f"login {email} -> {r.status_code}"
    return s, r.headers.get("Location", "")


@pytest.fixture(scope="module")
def admin_session():
    s, _ = _login(*ADMIN)
    return s


# ---------------- Public site pages ---------------- #
PUBLIC_PATHS = [
    "/", "/how-it-works", "/for-advertisers", "/auto-owners", "/network",
    "/advertising-solutions", "/technology", "/analytics", "/developers",
    "/about", "/pricing", "/faq", "/contact",
    "/legal/privacy", "/legal/terms", "/legal/cookie",
]


@pytest.mark.parametrize("path", PUBLIC_PATHS)
def test_public_page_renders(path):
    r = requests.get(f"{BASE}{path}", timeout=45)
    assert r.status_code == 200, f"{path} -> {r.status_code}"
    for m in BAD_MARKERS:
        assert m not in r.text, f"{path} contains error marker: {m}"


def test_sitemap_xml():
    r = requests.get(f"{BASE}/sitemap.xml", timeout=30)
    assert r.status_code == 200
    ct = r.headers.get("Content-Type", "").lower()
    assert "xml" in ct, f"Content-Type: {ct}"
    assert "<urlset" in r.text and "<loc>" in r.text


def test_robots_txt():
    r = requests.get(f"{BASE}/robots.txt", timeout=30)
    assert r.status_code == 200
    body = r.text
    assert "User-agent" in body or "User-Agent" in body
    assert "Sitemap" in body or "sitemap" in body


# ---------------- Home page markers ---------------- #
def test_home_has_hero_and_ctas():
    r = requests.get(f"{BASE}/", timeout=30)
    assert r.status_code == 200
    body = r.text
    assert "Turn Every Auto Ride Into an Advertising Opportunity" in body
    assert 'data-testid="header-start-advertising-btn"' in body
    assert "/contact?type=advertiser" in body
    # animated stat counters section - the four labels
    for lbl in ["Active Autos", "Screens Online", "Coverage Cities", "Active Campaigns"]:
        assert lbl in body, f"home missing stat label: {lbl}"


def test_home_mobile_menu_markers():
    r = requests.get(f"{BASE}/", timeout=30)
    body = r.text
    assert 'data-testid="mobile-menu-open"' in body
    assert 'data-testid="mobile-menu-close"' in body or 'mobile-menu-close' in body


def test_footer_legal_links():
    r = requests.get(f"{BASE}/", timeout=30)
    body = r.text
    assert "/legal/privacy" in body
    assert "/legal/terms" in body
    assert "/legal/cookie" in body


# ---------------- Network / Pricing / FAQ / stats.json ---------------- #
def test_network_page_shows_stats_and_coverage():
    r = requests.get(f"{BASE}/network", timeout=30)
    assert r.status_code == 200
    body = r.text
    # At least a few of the stat labels the request calls out
    stat_labels = ["Autos", "Screens", "Devices", "SIMs", "Cities", "Campaigns"]
    hits = sum(1 for s in stat_labels if s in body)
    assert hits >= 4, f"only {hits} stat labels found on /network"


def test_pricing_shows_four_plans():
    r = requests.get(f"{BASE}/pricing", timeout=30)
    assert r.status_code == 200
    body = r.text
    for plan in ["Starter", "Growth", "City Domination", "Enterprise"]:
        assert plan in body, f"pricing missing plan: {plan}"


def test_faq_has_accordion_testids():
    r = requests.get(f"{BASE}/faq", timeout=30)
    assert r.status_code == 200
    ids = re.findall(r'data-testid="faq-(\d+)"', r.text)
    assert len(ids) >= 1, "no faq-<id> accordion items rendered"


def test_stats_json_endpoint():
    r = requests.get(f"{BASE}/site/stats.json", timeout=30)
    assert r.status_code == 200
    j = r.json()
    # Should contain numeric-ish keys we display on home
    assert isinstance(j, dict) and len(j) >= 3


# ---------------- Contact form → Lead creation + notification ---------------- #
@pytest.fixture(scope="module")
def submitted_lead_email():
    """POST /contact as a guest and return the email used."""
    s = requests.Session()
    tok = _csrf(s, "/contact")
    email = f"TEST_lead_{int(time.time())}@autoads.test"
    payload = {
        "_token": tok,
        "type": "advertiser",
        "name": "TEST Lead",
        "email": email,
        "company": "TEST Co",
        "phone": "+911234567890",
        "message": "Automated Phase 1 test lead",
    }
    r = s.post(f"{BASE}/contact", data=payload, allow_redirects=False, timeout=30)
    assert r.status_code in (302, 303), f"contact submit -> {r.status_code}: {r.text[:300]}"
    # Follow redirect, expect green flash
    loc = r.headers.get("Location", "").replace(":80/", "/").replace("http://", "https://")
    r2 = s.get(loc or f"{BASE}/contact", timeout=30)
    assert r2.status_code == 200
    assert 'data-testid="site-flash-success"' in r2.text, "green success flash not shown after contact submit"
    return email


def test_contact_form_creates_flash(submitted_lead_email):
    assert submitted_lead_email.startswith("TEST_lead_")


def test_lead_appears_in_admin_leads_inbox(admin_session, submitted_lead_email):
    r = admin_session.get(f"{BASE}/leads", timeout=30)
    assert r.status_code == 200
    for m in BAD_MARKERS:
        assert m not in r.text
    assert submitted_lead_email in r.text, "submitted lead email not visible in /leads"


def test_lead_detail_and_status_update(admin_session, submitted_lead_email):
    r = admin_session.get(f"{BASE}/leads", timeout=30)
    # Find first link to lead detail
    m = re.search(r'/leads/(\d+)', r.text) or re.search(r'data-testid="lead-row-(\d+)"', r.text)
    assert m, "no lead detail link found on /leads"
    lead_id = m.group(1)
    r2 = admin_session.get(f"{BASE}/leads/{lead_id}", timeout=30)
    assert r2.status_code == 200
    assert 'data-testid="lead-status-select"' in r2.text
    assert 'data-testid="lead-save"' in r2.text
    tok = re.search(r'name="_token"\s+value="([^"]+)"', r2.text).group(1)
    # Update status to 'contacted'
    payload = {"_token": tok, "_method": "PUT", "status": "contacted", "notes": "TEST update"}
    r3 = admin_session.post(f"{BASE}/leads/{lead_id}", data=payload,
                            allow_redirects=False, timeout=30)
    assert r3.status_code in (302, 303), f"lead update -> {r3.status_code}: {r3.text[:300]}"
    # verify persisted
    r4 = admin_session.get(f"{BASE}/leads/{lead_id}", timeout=30)
    assert r4.status_code == 200
    # 'contacted' should appear pre-selected in select
    assert re.search(r'value="contacted"\s+selected', r4.text) or 'selected>Contacted' in r4.text \
        or 'selected="selected" value="contacted"' in r4.text, \
        "status 'contacted' not persisted after update"


def test_admin_notification_for_new_lead(admin_session, submitted_lead_email):
    # bell dropdown
    r = admin_session.get(f"{BASE}/notifications/dropdown", timeout=30)
    assert r.status_code == 200, r.status_code
    body_drop = r.text
    # full list
    r2 = admin_session.get(f"{BASE}/notifications", timeout=30)
    assert r2.status_code == 200
    body_list = r2.text
    combined = body_drop + body_list
    assert "Website Enquiry" in combined or "website enquiry" in combined.lower() \
        or "New Website" in combined, "no 'New Website Enquiry' notification present"


# ---------------- Branding settings ---------------- #
def test_branding_settings_form_renders(admin_session):
    r = admin_session.get(f"{BASE}/settings?tab=branding", timeout=30)
    assert r.status_code == 200
    body = r.text
    for m in BAD_MARKERS:
        assert m not in body
    assert 'data-testid="set-logo"' in body
    assert 'data-testid="save-settings"' in body
    # brand name & tagline inputs
    assert re.search(r'name="brand[_\.]?name"', body) or 'name="name"' in body
    assert 'tagline' in body.lower()


def test_branding_settings_save_persists_tagline(admin_session):
    r = admin_session.get(f"{BASE}/settings?tab=branding", timeout=30)
    assert r.status_code == 200
    tok = re.search(r'name="_token"\s+value="([^"]+)"', r.text).group(1)
    # find tagline field name (may be tagline or brand_tagline)
    tag_name_m = re.search(r'name="(brand_tagline|tagline)"', r.text)
    assert tag_name_m, "tagline input not found in branding form"
    tag_name = tag_name_m.group(1)
    # find name field
    name_m = re.search(r'name="(brand_name|name|app_name)"', r.text)
    assert name_m, "brand name input not found"
    name_field = name_m.group(1)
    # current values (best-effort)
    cur_name_m = re.search(rf'name="{name_field}"[^>]*value="([^"]*)"', r.text)
    cur_name = cur_name_m.group(1) if cur_name_m else "AutoAds Network"

    new_tag = f"TEST tagline {int(time.time())}"
    payload = {
        "_token": tok,
        "_method": "PUT",
        name_field: cur_name,
        tag_name: new_tag,
    }
    r2 = admin_session.post(f"{BASE}/settings/branding", data=payload,
                            allow_redirects=False, timeout=30)
    assert r2.status_code in (302, 303), f"branding save -> {r2.status_code}: {r2.text[:300]}"
    r3 = admin_session.get(f"{BASE}/settings?tab=branding", timeout=30)
    assert r3.status_code == 200
    assert new_tag in r3.text, "new tagline not persisted"


# ---------------- Regressions ---------------- #
ADMIN_PATHS = ["/dashboard", "/autos", "/devices", "/campaigns",
               "/settlements", "/users", "/leads"]


@pytest.mark.parametrize("path", ADMIN_PATHS)
def test_admin_pages_still_work(admin_session, path):
    r = admin_session.get(f"{BASE}{path}", timeout=45)
    assert r.status_code == 200, f"{path} -> {r.status_code}"
    for m in BAD_MARKERS:
        assert m not in r.text


@pytest.mark.parametrize("email,expected", list(PORTALS.values()))
def test_portal_login_redirects(email, expected):
    _, loc = _login(email)
    assert expected in loc, f"{email} -> {loc}"


def test_admin_sidebar_has_website_leads_and_brand(admin_session):
    r = admin_session.get(f"{BASE}/dashboard", timeout=30)
    assert r.status_code == 200
    body = r.text
    assert "Website Leads" in body or "/leads" in body
    # brand lockup - either brand image or the brand text
    assert "AutoAds" in body or "brand" in body.lower()


# ---------------- Security regressions ---------------- #
def test_api_docs_gated_for_guest():
    r = requests.get(f"{BASE}/api/docs", timeout=30, allow_redirects=False)
    assert r.status_code in (401, 302, 403)


def test_advertiser_api_login_and_scope():
    r = requests.post(f"{BASE}/api/v1/auth/login",
                      json={"email": "advertiser@autoads.test", "password": "Admin@123"},
                      headers={"Accept": "application/json"}, timeout=30)
    assert r.status_code == 200, r.text[:300]
    tok = r.json().get("token") or r.json().get("data", {}).get("token")
    assert tok
    r2 = requests.get(f"{BASE}/api/v1/owners",
                      headers={"Authorization": f"Bearer {tok}", "Accept": "application/json"},
                      timeout=30)
    assert r2.status_code == 403, f"advertiser /owners -> {r2.status_code}"
