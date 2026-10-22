<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Loan extends Model
{
    protected $fillable = [
        'member_id', 'book_copy_id', 'issued_by', 'returned_by', 'issue_date', 'due_date',
        'return_date', 'status', 'fine_rate', 'fine_amount',
    ];

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'due_date' => 'date',
            'return_date' => 'date',
            'fine_rate' => 'decimal:2',
            'fine_amount' => 'decimal:2',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(User::class, 'member_id');
    }

    public function copy(): BelongsTo
    {
        return $this->belongsTo(BookCopy::class, 'book_copy_id');
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function returner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'returned_by');
    }

    public function finePayments(): HasMany
    {
        return $this->hasMany(FinePayment::class);
    }

    public function overdueDays(?Carbon $asOf = null): int
    {
        $endDate = $this->return_date
            ? Carbon::parse($this->return_date)->startOfDay()
            : ($asOf ? $asOf->copy()->startOfDay() : today()->startOfDay());
        $dueDate = Carbon::parse($this->due_date)->startOfDay();

        return $endDate->lessThanOrEqualTo($dueDate) ? 0 : (int) $dueDate->diffInDays($endDate);
    }

    public function estimatedFine(?Carbon $asOf = null): float
    {
        if ($this->return_date) {
            return (float) $this->fine_amount;
        }

        return round($this->overdueDays($asOf) * (float) $this->fine_rate, 2);
    }

    public function outstandingFine(?Carbon $asOf = null): float
    {
        $paid = (float) ($this->relationLoaded('finePayments')
            ? $this->finePayments->sum('amount')
            : $this->finePayments()->sum('amount'));

        return max(0, round($this->estimatedFine($asOf) - $paid, 2));
    }
}
