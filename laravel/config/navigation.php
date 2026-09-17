<?php

return [
    ['group' => 'Dashboard', 'items' => [
        ['label' => 'Overview', 'route' => 'dashboard', 'icon' => 'layout-dashboard', 'perm' => 'dashboard.view'],
    ]],
    ['group' => 'Network', 'items' => [
        ['label' => 'Autos', 'route' => 'autos.index', 'icon' => 'car', 'perm' => 'network.auto.view'],
        ['label' => 'Auto Groups', 'route' => 'auto-groups.index', 'icon' => 'layers', 'perm' => 'network.autogroup.view'],
        ['label' => 'Owners', 'route' => 'owners.index', 'icon' => 'user-round', 'perm' => 'network.owner.view'],
        ['label' => 'Drivers', 'route' => 'drivers.index', 'icon' => 'steering-wheel', 'perm' => 'network.driver.view'],
        ['label' => 'Screens', 'route' => 'screens.index', 'icon' => 'monitor', 'perm' => 'network.screen.view'],
        ['label' => 'Devices', 'route' => 'devices.index', 'icon' => 'cpu', 'perm' => 'network.device.view'],
        ['label' => 'SIM Management', 'route' => 'sims.index', 'icon' => 'signal', 'perm' => 'network.sim.view'],
    ]],
    ['group' => 'Advertising', 'items' => [
        ['label' => 'Advertisers', 'route' => 'advertisers.index', 'icon' => 'briefcase', 'perm' => 'advertising.advertiser.view'],
        ['label' => 'Advertisements', 'route' => 'advertisements.index', 'icon' => 'image', 'perm' => 'advertising.advertisement.view'],
        ['label' => 'Approval Queue', 'route' => 'advertisements.approvals', 'icon' => 'badge-check', 'perm' => 'advertising.advertisement.approve'],
        ['label' => 'Campaigns', 'route' => 'campaigns.index', 'icon' => 'megaphone', 'perm' => 'advertising.campaign.view'],
        ['label' => 'Playlists', 'route' => 'playlists.index', 'icon' => 'list-video', 'perm' => 'advertising.playlist.view'],
        ['label' => 'Proof of Play', 'route' => 'pop.index', 'icon' => 'shield-check', 'perm' => 'advertising.pop.view'],
    ]],
    ['group' => 'Operations', 'items' => [
        ['label' => 'Installations', 'route' => 'installations.index', 'icon' => 'wrench', 'perm' => 'operations.installation.view'],
        ['label' => 'Assets', 'route' => 'assets.index', 'icon' => 'package', 'perm' => 'operations.asset.view'],
        ['label' => 'Maintenance', 'route' => 'maintenance.index', 'icon' => 'life-buoy', 'perm' => 'operations.maintenance.view'],
        ['label' => 'Warranty', 'route' => 'warranties.index', 'icon' => 'file-badge', 'perm' => 'operations.warranty.view'],
        ['label' => 'Vendors', 'route' => 'vendors.index', 'icon' => 'truck', 'perm' => 'operations.vendor.view'],
    ]],
    ['group' => 'Finance', 'items' => [
        ['label' => 'Revenue', 'route' => 'revenue.index', 'icon' => 'trending-up', 'perm' => 'finance.revenue.view'],
        ['label' => 'Invoices', 'route' => 'invoices.index', 'icon' => 'file-text', 'perm' => 'finance.invoice.view'],
        ['label' => 'Payments', 'route' => 'payments.index', 'icon' => 'wallet', 'perm' => 'finance.payment.view'],
        ['label' => 'Expenses', 'route' => 'expenses.index', 'icon' => 'receipt', 'perm' => 'finance.expense.view'],
        ['label' => 'Rate Cards', 'route' => 'rate-cards.index', 'icon' => 'sliders-horizontal', 'perm' => 'finance.ratecard.view'],
        ['label' => 'Driver Earnings', 'route' => 'earnings.index', 'icon' => 'hand-coins', 'perm' => 'finance.earning.view'],
        ['label' => 'Settlements', 'route' => 'settlements.index', 'icon' => 'banknote', 'perm' => 'finance.settlement.view'],
        ['label' => 'Profitability', 'route' => 'profitability.index', 'icon' => 'pie-chart', 'perm' => 'finance.profitability.view'],
    ]],
    ['group' => 'Reports', 'items' => [
        ['label' => 'Reports Hub', 'route' => 'reports.index', 'icon' => 'bar-chart-3', 'perm' => 'reports.view'],
    ]],
    ['group' => 'Integrations', 'items' => [
        ['label' => 'API Applications', 'route' => 'api-apps.index', 'icon' => 'plug', 'perm' => 'integrations.api.view'],
        ['label' => 'Webhooks', 'route' => 'webhooks.index', 'icon' => 'webhook', 'perm' => 'integrations.webhook.view'],
        ['label' => 'API Logs', 'route' => 'api-logs.index', 'icon' => 'scroll-text', 'perm' => 'integrations.api.view'],
    ]],
    ['group' => 'Administration', 'items' => [
        ['label' => 'Users', 'route' => 'users.index', 'icon' => 'users', 'perm' => 'admin.user.view'],
        ['label' => 'Roles', 'route' => 'roles.index', 'icon' => 'shield', 'perm' => 'admin.role.view'],
        ['label' => 'Notifications', 'route' => 'notifications.index', 'icon' => 'bell', 'perm' => 'admin.notification.view'],
        ['label' => 'Email Logs', 'route' => 'email-logs.index', 'icon' => 'mail', 'perm' => 'admin.settings.view'],
        ['label' => 'Audit Logs', 'route' => 'audit.index', 'icon' => 'history', 'perm' => 'admin.audit.view'],
        ['label' => 'Settings', 'route' => 'settings.index', 'icon' => 'settings', 'perm' => 'admin.settings.view'],
    ]],
];
