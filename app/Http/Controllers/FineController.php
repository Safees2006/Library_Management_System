<?php

namespace App\Http\Controllers;

use App\Models\FinePayment;
use App\Models\Loan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class FineController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate(['q' => ['nullable', 'string', 'max:100']]);

        $query = Loan::with(['member', 'copy.book', 'finePayments.receiver'])
            ->where(function (Builder $loans): void {
                $loans->where('fine_amount', '>', 0)
                    ->orWhere(fn (Builder $active) => $active->where('status', 'active')->whereDate('due_date', '<', today()));
            });

        if ($search = trim((string) $request->query('q'))) {
            $query->where(fn (Builder $loans) => $loans
                ->whereHas('member', fn (Builder $members) => $members->where('name', 'like', "%{$search}%")
                    ->orWhere('membership_number', 'like', "%{$search}%"))
                ->orWhereHas('copy.book', fn (Builder $books) => $books->where('title', 'like', "%{$search}%")));
        }

        $loans = $query->latest('due_date')->paginate(12)->withQueryString();

        return view('admin.fines.index', compact('loans'));
    }

    public function storePayment(Request $request, Loan $loan): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'max:99999999'],
            'paid_at' => ['required', 'date_format:Y-m-d'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($data, $loan): void {
            $lockedLoan = Loan::whereKey($loan->id)->lockForUpdate()->firstOrFail();
            $lockedLoan->load('finePayments');

            if ($lockedLoan->status !== 'returned') {
                throw ValidationException::withMessages(['amount' => 'An overdue loan must be returned before its fine can be paid.']);
            }

            $amount = round((float) $data['amount'], 2);
            if ($amount > $lockedLoan->outstandingFine()) {
                throw ValidationException::withMessages(['amount' => 'Payment exceeds the unpaid balance.']);
            }

            FinePayment::create([
                'loan_id' => $lockedLoan->id,
                'received_by' => auth()->id(),
                'amount' => $amount,
                'paid_at' => $data['paid_at'],
                'note' => $data['note'] ?? null,
            ]);
        });

        return back()->with('status', 'Fine payment recorded in the payment history.');
    }
}
