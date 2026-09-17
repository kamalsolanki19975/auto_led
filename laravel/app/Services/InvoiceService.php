<?php

namespace App\Services;

use App\Models\Advertiser;
use App\Models\Campaign;
use App\Models\Invoice;
use App\Models\Revenue;
use App\Support\Codes;
use Carbon\Carbon;

class InvoiceService
{
    public function createForCampaign(Campaign $campaign, array $data = [], ?int $userId = null): Invoice
    {
        return \DB::transaction(function () use ($campaign, $data, $userId) {
            $amount = (float) ($data['amount'] ?? $campaign->budget);
            $taxPercent = (float) ($data['tax_percent'] ?? 18);
            $taxAmount = round($amount * $taxPercent / 100, 2);
            $total = round($amount + $taxAmount, 2);

            $invoice = Invoice::create([
                'number' => Codes::next('invoices', 'INV', 5, 'number'),
                'advertiser_id' => $campaign->advertiser_id,
                'campaign_id' => $campaign->id,
                'invoice_date' => $data['invoice_date'] ?? now()->toDateString(),
                'due_date' => $data['due_date'] ?? now()->addDays(15)->toDateString(),
                'amount' => $amount,
                'tax_percent' => $taxPercent,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'amount_paid' => 0,
                'status' => 'issued',
                'created_by' => $userId,
                'notes' => $data['notes'] ?? null,
            ]);

            $invoice->items()->create([
                'description' => 'Campaign: '.$campaign->name.' ('.$campaign->code.')',
                'quantity' => 1,
                'rate' => $amount,
                'amount' => $amount,
            ]);

            AuditService::log('invoice.created', $invoice);

            if ($campaign->advertiser && $campaign->advertiser->email) {
                app(EmailService::class)->send('invoice.created', $campaign->advertiser->email, [
                    'advertiser_name' => $campaign->advertiser->company_name,
                    'invoice_number' => $invoice->number,
                    'amount' => $invoice->total,
                    'campaign_name' => $campaign->name,
                ]);
            }

            return $invoice;
        });
    }

    public function markOverdueInvoices(): int
    {
        return Invoice::whereIn('status', ['issued', 'partially_paid'])
            ->whereDate('due_date', '<', now())
            ->update(['status' => 'overdue']);
    }
}
