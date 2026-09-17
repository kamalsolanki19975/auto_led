<?php

namespace App\Services;

use App\Models\Advertisement;
use App\Models\Advertiser;
use App\Models\Auto;
use App\Models\Campaign;
use App\Models\Device;
use App\Models\Driver;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\MaintenanceTicket;
use App\Models\Owner;
use App\Models\Payment;
use App\Models\Screen;
use App\Models\Sim;

class SearchService
{
    public function search(string $q): array
    {
        $q = trim($q);
        if ($q === '') {
            return [];
        }
        $like = '%'.$q.'%';
        $results = [];

        $add = function ($type, $rows, $route, callable $label, callable $sub) use (&$results) {
            foreach ($rows as $r) {
                $results[] = [
                    'type' => $type,
                    'label' => $label($r),
                    'sub' => $sub($r),
                    'url' => route($route, $r),
                ];
            }
        };

        $add('Auto', Auto::where('registration_number', 'like', $like)->orWhere('code', 'like', $like)->limit(5)->get(),
            'autos.show', fn ($r) => $r->registration_number, fn ($r) => $r->code);
        $add('Owner', Owner::where('name', 'like', $like)->orWhere('code', 'like', $like)->orWhere('mobile', 'like', $like)->limit(5)->get(),
            'owners.show', fn ($r) => $r->name, fn ($r) => $r->code);
        $add('Driver', Driver::where('name', 'like', $like)->orWhere('code', 'like', $like)->orWhere('mobile', 'like', $like)->limit(5)->get(),
            'drivers.show', fn ($r) => $r->name, fn ($r) => $r->code);
        $add('Screen', Screen::where('serial_number', 'like', $like)->orWhere('code', 'like', $like)->limit(5)->get(),
            'screens.show', fn ($r) => $r->serial_number, fn ($r) => $r->code);
        $add('Device', Device::where('device_uuid', 'like', $like)->orWhere('code', 'like', $like)->orWhere('serial_number', 'like', $like)->limit(5)->get(),
            'devices.show', fn ($r) => $r->code, fn ($r) => $r->device_uuid);
        $add('SIM', Sim::where('iccid', 'like', $like)->orWhere('mobile_number', 'like', $like)->orWhere('code', 'like', $like)->limit(5)->get(),
            'sims.show', fn ($r) => $r->mobile_number ?: $r->iccid, fn ($r) => $r->code);
        $add('Advertiser', Advertiser::where('company_name', 'like', $like)->orWhere('code', 'like', $like)->limit(5)->get(),
            'advertisers.show', fn ($r) => $r->company_name, fn ($r) => $r->code);
        $add('Advertisement', Advertisement::where('title', 'like', $like)->orWhere('code', 'like', $like)->limit(5)->get(),
            'advertisements.show', fn ($r) => $r->title, fn ($r) => $r->code);
        $add('Campaign', Campaign::where('name', 'like', $like)->orWhere('code', 'like', $like)->limit(5)->get(),
            'campaigns.show', fn ($r) => $r->name, fn ($r) => $r->code);
        $add('Invoice', Invoice::where('number', 'like', $like)->limit(5)->get(),
            'invoices.show', fn ($r) => $r->number, fn ($r) => '₹'.number_format($r->total, 2));
        $add('Ticket', MaintenanceTicket::where('code', 'like', $like)->orWhere('issue', 'like', $like)->limit(5)->get(),
            'maintenance.show', fn ($r) => $r->code, fn ($r) => $r->issue);

        return $results;
    }
}
