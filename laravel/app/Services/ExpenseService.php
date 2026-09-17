<?php

namespace App\Services;

use App\Models\Expense;
use App\Support\Codes;

class ExpenseService
{
    public function record(array $data, ?int $userId = null): Expense
    {
        $expense = Expense::create(array_merge([
            'code' => Codes::next('expenses', 'EXP'),
            'created_by' => $userId,
        ], $data));
        AuditService::log('expense.created', $expense);
        return $expense;
    }
}
