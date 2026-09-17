<?php

namespace App\Services;

use App\Models\Driver;
use App\Models\DriverEarning;
use App\Models\DriverPayment;
use App\Models\DriverSettlement;
use App\Support\Codes;
use Carbon\Carbon;

class SettlementService
{
    public function __construct(
        protected DriverEarningsService $earnings,
        protected NotificationService $notifications
    ) {}

    /**
     * Create a settlement for a driver over a period. Calculates earnings if
     * not already present and snapshots the calculation.
     */
    public function create(Driver $driver, Carbon $start, Carbon $end): DriverSettlement
    {
        return \DB::transaction(function () use ($driver, $start, $end) {
            $earning = $this->earnings->calculate($driver, $start, $end);

            $settlement = DriverSettlement::create([
                'code' => Codes::next('driver_settlements', 'STL'),
                'driver_id' => $driver->id,
                'owner_id' => $driver->owner_id,
                'period_start' => $start->toDateString(),
                'period_end' => $end->toDateString(),
                'total_earnings' => $earning->gross_amount + $earning->bonus,
                'total_adjustments' => $earning->adjustment - $earning->penalty,
                'net_payable' => $earning->net_amount,
                'status' => 'draft',
                'calculation_snapshot' => [
                    'basis' => $earning->calculation_basis,
                    'rate' => $earning->rate,
                    'quantity' => $earning->quantity,
                    'gross' => $earning->gross_amount,
                    'bonus' => $earning->bonus,
                    'penalty' => $earning->penalty,
                    'adjustment' => $earning->adjustment,
                    'net' => $earning->net_amount,
                    'valid_plays' => $earning->valid_plays,
                    'valid_runtime_seconds' => $earning->valid_runtime_seconds,
                    'version' => $earning->calculation_version,
                    'calculated_at' => now()->toIso8601String(),
                ],
            ]);

            $earning->update(['settlement_id' => $settlement->id, 'status' => 'settled']);

            AuditService::log('settlement.created', $settlement);
            app(WebhookService::class)->dispatch('settlement.created', ['code' => $settlement->code, 'driver_id' => $driver->id, 'net' => $settlement->net_payable]);

            return $settlement;
        });
    }

    public function approve(DriverSettlement $settlement, int $userId): void
    {
        if ($settlement->status === 'paid') {
            abort(422, 'A paid settlement cannot be modified.');
        }
        $settlement->update(['status' => 'approved', 'approved_by' => $userId, 'approved_at' => now()]);
        AuditService::log('settlement.approved', $settlement);
        app(WebhookService::class)->dispatch('settlement.approved', ['code' => $settlement->code]);

        if ($settlement->driver && $settlement->driver->email) {
            app(EmailService::class)->send('settlement.approved', $settlement->driver->email, [
                'user_name' => $settlement->driver->name,
                'settlement_number' => $settlement->code,
                'amount' => $settlement->net_payable,
            ]);
        }
    }

    public function pay(DriverSettlement $settlement, array $data, int $userId): DriverPayment
    {
        if ($settlement->status !== 'approved') {
            abort(422, 'Only approved settlements can be paid.');
        }
        return \DB::transaction(function () use ($settlement, $data, $userId) {
            $payment = DriverPayment::create([
                'code' => Codes::next('driver_payments', 'DPY'),
                'settlement_id' => $settlement->id,
                'driver_id' => $settlement->driver_id,
                'amount' => $data['amount'] ?? $settlement->net_payable,
                'method' => $data['method'] ?? 'bank_transfer',
                'reference' => $data['reference'] ?? null,
                'bank' => $data['bank'] ?? null,
                'upi' => $data['upi'] ?? null,
                'paid_at' => now(),
                'notes' => $data['notes'] ?? null,
                'created_by' => $userId,
            ]);
            $settlement->update(['status' => 'paid', 'paid_at' => now()]);

            // Record as an operating expense (driver payment)
            app(ExpenseService::class)->record([
                'date' => now()->toDateString(),
                'category' => 'driver_payment',
                'amount' => $payment->amount,
                'payment_status' => 'paid',
                'notes' => 'Driver settlement '.$settlement->code,
            ], $userId);

            AuditService::log('driver.payment', $payment);
            app(WebhookService::class)->dispatch('payment.completed', ['type' => 'driver', 'code' => $payment->code, 'amount' => $payment->amount]);
            return $payment;
        });
    }
}
