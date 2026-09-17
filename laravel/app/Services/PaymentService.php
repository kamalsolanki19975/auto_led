<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Revenue;
use App\Support\Codes;

class PaymentService
{
    /** Record an advertiser payment against an invoice. */
    public function recordReceived(array $data, ?int $userId = null): Payment
    {
        return \DB::transaction(function () use ($data, $userId) {
            $invoice = isset($data['invoice_id']) ? Invoice::find($data['invoice_id']) : null;

            $payment = Payment::create([
                'code' => Codes::next('payments', 'PAY'),
                'type' => 'received',
                'advertiser_id' => $data['advertiser_id'] ?? $invoice?->advertiser_id,
                'invoice_id' => $invoice?->id,
                'amount' => $data['amount'],
                'method' => $data['method'] ?? 'bank_transfer',
                'reference' => $data['reference'] ?? null,
                'bank' => $data['bank'] ?? null,
                'upi' => $data['upi'] ?? null,
                'gateway' => $data['gateway'] ?? null,
                'paid_at' => $data['paid_at'] ?? now(),
                'notes' => $data['notes'] ?? null,
                'created_by' => $userId,
            ]);

            if ($invoice) {
                $invoice->amount_paid = round($invoice->amount_paid + $payment->amount, 2);
                $invoice->status = $invoice->amount_paid >= $invoice->total ? 'paid'
                    : ($invoice->amount_paid > 0 ? 'partially_paid' : $invoice->status);
                $invoice->save();

                // Recognise revenue on receipt
                Revenue::create([
                    'code' => Codes::next('revenues', 'REV'),
                    'source' => 'campaign',
                    'campaign_id' => $invoice->campaign_id,
                    'advertiser_id' => $invoice->advertiser_id,
                    'invoice_id' => $invoice->id,
                    'amount' => $payment->amount,
                    'date' => now()->toDateString(),
                    'notes' => 'Payment '.$payment->code.' for invoice '.$invoice->number,
                ]);
            }

            AuditService::log('payment.received', $payment);
            app(WebhookService::class)->dispatch('payment.received', ['code' => $payment->code, 'amount' => $payment->amount]);
            return $payment;
        });
    }

    public function recordMade(array $data, ?int $userId = null): Payment
    {
        $payment = Payment::create([
            'code' => Codes::next('payments', 'PAY'),
            'type' => 'made',
            'vendor_id' => $data['vendor_id'] ?? null,
            'amount' => $data['amount'],
            'method' => $data['method'] ?? 'bank_transfer',
            'reference' => $data['reference'] ?? null,
            'paid_at' => $data['paid_at'] ?? now(),
            'notes' => $data['notes'] ?? null,
            'created_by' => $userId,
        ]);
        AuditService::log('payment.made', $payment);
        return $payment;
    }
}
