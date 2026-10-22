<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinePayment extends Model
{
    protected $fillable = ['loan_id', 'received_by', 'amount', 'paid_at', 'note'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'paid_at' => 'date'];
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
