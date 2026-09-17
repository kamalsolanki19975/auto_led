<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    AuthController, DashboardController, PortalController, SearchController,
    AutoController, OwnerController, DriverController, AutoGroupController,
    ScreenController, DeviceController, SimController,
    AdvertiserController, AdvertisementController, CampaignController, PlaylistController, ProofOfPlayController,
    VendorController, AssetController, MaintenanceController, WarrantyController, InstallationController,
    RateCardController, ExpenseController, InvoiceController, PaymentController, SettlementController,
    EarningController, RevenueController, ProfitabilityController,
    ReportController, NotificationController, LogController, SettingsController,
    UserController, RoleController, ApiApplicationController, WebhookController,
    PublicController, LeadController
};
use App\Http\Controllers\Api\ApiDocsController;

/* ---------------- Guest ---------------- */
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
    Route::get('/forgot-password', [AuthController::class, 'showForgot'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendReset'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showReset'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'reset'])->name('password.update');
});

/* ---------------- Public Website ---------------- */
Route::controller(PublicController::class)->group(function () {
    Route::get('/', 'home')->name('site.home');
    Route::get('/how-it-works', 'howItWorks')->name('site.how');
    Route::get('/for-advertisers', 'advertisers')->name('site.advertisers');
    Route::get('/auto-owners', 'autoOwners')->name('site.owners');
    Route::get('/network', 'network')->name('site.network');
    Route::get('/advertising-solutions', 'solutions')->name('site.solutions');
    Route::get('/technology', 'technology')->name('site.technology');
    Route::get('/analytics', 'analytics')->name('site.analytics');
    Route::get('/developers', 'developers')->name('site.developers');
    Route::get('/about', 'about')->name('site.about');
    Route::get('/pricing', 'pricing')->name('site.pricing');
    Route::get('/faq', 'faq')->name('site.faq');
    Route::get('/contact', 'contact')->name('site.contact');
    Route::post('/contact', 'contactSubmit')->name('site.contact.submit')->middleware('throttle:8,1');
    Route::get('/legal/{doc}', 'legal')->name('site.legal')->where('doc', 'privacy|terms|cookie');
    Route::get('/site/stats.json', 'stats')->name('site.stats');
    Route::get('/sitemap.xml', 'sitemap');
    Route::get('/robots.txt', 'robots');
});

/* ---------------- Authenticated ---------------- */
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/change-password', [AuthController::class, 'changePassword'])->name('password.change');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/search', [SearchController::class, 'index'])->name('search');

    // Notification center
    Route::get('/notifications', [NotificationController::class, 'index'])->middleware('permission:admin.notification.view')->name('notifications.index');
    Route::get('/notifications/dropdown', [NotificationController::class, 'dropdown'])->name('notifications.dropdown');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.readAll');

    // Portals
    Route::get('/portal/advertiser', [PortalController::class, 'advertiser'])->name('portal.advertiser');
    Route::get('/portal/driver', [PortalController::class, 'driver'])->name('portal.driver');
    Route::get('/portal/owner', [PortalController::class, 'owner'])->name('portal.owner');
    Route::get('/portal/technician', [PortalController::class, 'technician'])->name('portal.technician');
    Route::post('/portal/settlements/{settlement}/dispute', [PortalController::class, 'raiseDispute'])->name('portal.dispute');

    // Network
    Route::middleware('permission:network.auto.view')->group(function () {
        Route::resource('autos', AutoController::class);
    });
    Route::resource('auto-groups', AutoGroupController::class)->middleware('permission:network.autogroup.view');
    Route::resource('owners', OwnerController::class)->middleware('permission:network.owner.view');
    Route::resource('drivers', DriverController::class)->middleware('permission:network.driver.view');
    Route::resource('screens', ScreenController::class)->middleware('permission:network.screen.view');

    Route::middleware('permission:network.device.view')->group(function () {
        Route::post('devices/{device}/activate', [DeviceController::class, 'activateDevice'])->name('devices.activate');
        Route::post('devices/{device}/token', [DeviceController::class, 'regenerateToken'])->name('devices.token');
        Route::post('devices/{device}/command', [DeviceController::class, 'command'])->name('devices.command');
        Route::post('devices/{device}/status', [DeviceController::class, 'setStatus'])->name('devices.setStatus');
        Route::resource('devices', DeviceController::class);
    });
    Route::resource('sims', SimController::class)->middleware('permission:network.sim.view');

    // Advertising
    Route::resource('advertisers', AdvertiserController::class)->middleware('permission:advertising.advertiser.view');
    Route::middleware('permission:advertising.advertisement.view')->group(function () {
        Route::get('advertisements/approvals', [AdvertisementController::class, 'approvals'])->name('advertisements.approvals');
        Route::post('advertisements/{advertisement}/submit', [AdvertisementController::class, 'submit'])->name('advertisements.submit');
        Route::post('advertisements/{advertisement}/approve', [AdvertisementController::class, 'approve'])->name('advertisements.approve');
        Route::post('advertisements/{advertisement}/reject', [AdvertisementController::class, 'reject'])->name('advertisements.reject');
        Route::resource('advertisements', AdvertisementController::class);
    });
    Route::middleware('permission:advertising.campaign.view')->group(function () {
        Route::post('campaigns/{campaign}/assign', [CampaignController::class, 'assign'])->name('campaigns.assign');
        Route::post('campaigns/{campaign}/approve', [CampaignController::class, 'approve'])->name('campaigns.approve');
        Route::post('campaigns/{campaign}/activate', [CampaignController::class, 'activate'])->name('campaigns.activate');
        Route::post('campaigns/{campaign}/pause', [CampaignController::class, 'pause'])->name('campaigns.pause');
        Route::post('campaigns/{campaign}/invoice', [CampaignController::class, 'invoice'])->name('campaigns.invoice');
        Route::resource('campaigns', CampaignController::class);
    });
    Route::resource('playlists', PlaylistController::class)->middleware('permission:advertising.playlist.view');
    Route::get('proof-of-play', [ProofOfPlayController::class, 'index'])->middleware('permission:advertising.pop.view')->name('pop.index');

    // Operations
    Route::resource('installations', InstallationController::class)->middleware('permission:operations.installation.view');
    Route::resource('assets', AssetController::class)->middleware('permission:operations.asset.view');
    Route::resource('maintenance', MaintenanceController::class)->parameters(['maintenance' => 'maintenance'])->middleware('permission:operations.maintenance.view');
    Route::resource('warranties', WarrantyController::class)->middleware('permission:operations.warranty.view');
    Route::resource('vendors', VendorController::class)->middleware('permission:operations.vendor.view');

    // Finance
    Route::get('revenue', [RevenueController::class, 'index'])->middleware('permission:finance.revenue.view')->name('revenue.index');
    Route::post('revenue', [RevenueController::class, 'store'])->middleware('permission:finance.revenue.create')->name('revenue.store');
    Route::middleware('permission:finance.invoice.view')->group(function () {
        Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');
        Route::get('invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
        Route::post('invoices', [InvoiceController::class, 'store'])->name('invoices.store');
        Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
        Route::post('invoices/{invoice}/payment', [InvoiceController::class, 'recordPayment'])->name('invoices.payment');
    });
    Route::middleware('permission:finance.payment.view')->group(function () {
        Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::get('payments/create', [PaymentController::class, 'create'])->name('payments.create');
        Route::post('payments', [PaymentController::class, 'store'])->name('payments.store');
    });
    Route::resource('expenses', ExpenseController::class)->middleware('permission:finance.expense.view');
    Route::resource('rate-cards', RateCardController::class)->middleware('permission:finance.ratecard.view');
    Route::get('earnings', [EarningController::class, 'index'])->middleware('permission:finance.earning.view')->name('earnings.index');
    Route::middleware('permission:finance.settlement.view')->group(function () {
        Route::get('settlements', [SettlementController::class, 'index'])->name('settlements.index');
        Route::get('settlements/create', [SettlementController::class, 'create'])->name('settlements.create');
        Route::post('settlements', [SettlementController::class, 'store'])->name('settlements.store');
        Route::get('settlements/{settlement}', [SettlementController::class, 'show'])->name('settlements.show');
        Route::post('settlements/{settlement}/approve', [SettlementController::class, 'approve'])->name('settlements.approve');
        Route::post('settlements/{settlement}/pay', [SettlementController::class, 'pay'])->name('settlements.pay');
    });
    Route::get('profitability', [ProfitabilityController::class, 'index'])->middleware('permission:finance.profitability.view')->name('profitability.index');

    // Reports
    Route::middleware('permission:reports.view')->group(function () {
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/{type}', [ReportController::class, 'show'])->name('reports.show');
    });

    // Integrations
    Route::middleware('permission:integrations.api.view')->group(function () {
        Route::get('api/docs', [ApiDocsController::class, 'ui'])->name('api.docs');
        Route::get('api/openapi.json', [ApiDocsController::class, 'spec'])->name('api.spec');
        Route::get('api-apps', [ApiApplicationController::class, 'index'])->name('api-apps.index');
        Route::post('api-apps', [ApiApplicationController::class, 'store'])->name('api-apps.store');
        Route::post('api-apps/{app}/keys', [ApiApplicationController::class, 'generateKey'])->name('api-apps.keys');
        Route::post('api-keys/{key}/revoke', [ApiApplicationController::class, 'revokeKey'])->name('api-apps.revoke');
        Route::get('api-logs', [LogController::class, 'api'])->name('api-logs.index');
    });
    Route::middleware('permission:integrations.webhook.view')->group(function () {
        Route::get('webhooks', [WebhookController::class, 'index'])->name('webhooks.index');
        Route::post('webhooks', [WebhookController::class, 'store'])->name('webhooks.store');
        Route::post('webhooks/{webhook}/test', [WebhookController::class, 'test'])->name('webhooks.test');
        Route::delete('webhooks/{webhook}', [WebhookController::class, 'destroy'])->name('webhooks.destroy');
    });

    // CRM
    Route::middleware('permission:crm.lead.view')->group(function () {
        Route::get('leads', [LeadController::class, 'index'])->name('leads.index');
        Route::get('leads/{lead}', [LeadController::class, 'show'])->name('leads.show');
        Route::put('leads/{lead}', [LeadController::class, 'update'])->name('leads.update');
    });

    // Administration
    Route::resource('users', UserController::class)->except(['show'])->middleware('permission:admin.user.view');
    Route::resource('roles', RoleController::class)->except(['show', 'destroy'])->middleware('permission:admin.role.view');
    Route::get('audit-logs', [LogController::class, 'audit'])->middleware('permission:admin.audit.view')->name('audit.index');
    Route::get('notification-logs', [LogController::class, 'notifications'])->middleware('permission:admin.notification.view')->name('notification-logs.index');
    Route::middleware('permission:admin.settings.view')->group(function () {
        Route::get('email-logs', [LogController::class, 'email'])->name('email-logs.index');
        Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::put('settings/{group}', [SettingsController::class, 'update'])->name('settings.update');
        Route::post('settings/test-email', [SettingsController::class, 'testEmail'])->name('settings.testEmail');
    });
});
