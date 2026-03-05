<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Expense;

class ExpenseObserver
{
    public function created(Expense $expense): void
    {
        ActivityLog::log(
            action: 'expense_created',
            subject: $expense,
            description: "Nouvelle dépense enregistrée : {$expense->amount} MAD ({$expense->category})",
            companyId: $expense->company_id
        );
    }
}
