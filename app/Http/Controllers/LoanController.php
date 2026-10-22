<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Loan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoanController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['all', 'active', 'returned', 'overdue'])],
            'member' => ['nullable', 'integer', Rule::exists('users', 'id')->where('role', 'member')],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $query = Loan::with(['member', 'copy.book', 'finePayments']);
        $status = (string) $request->query('status', 'all');

        if ($status === 'active' || $status === 'returned') {
            $query->where('status', $status);
        } elseif ($status === 'overdue') {
            $query->where('status', 'active')->whereDate('due_date', '<', today());
        }

        if ($memberId = $request->integer('member')) {
            $query->where('member_id', $memberId);
        }

        if ($search = trim((string) $request->query('q'))) {
            $query->where(fn (Builder $loans) => $loans
                ->whereHas('member', fn (Builder $members) => $members->where('name', 'like', "%{$search}%")
                    ->orWhere('membership_number', 'like', "%{$search}%"))
                ->orWhereHas('copy.book', fn (Builder $books) => $books->where('title', 'like', "%{$search}%"))
                ->orWhereHas('copy', fn (Builder $copies) => $copies->where('barcode', 'like', "%{$search}%")));
        }

        if ($request->filled('from')) {
            $query->whereDate('issue_date', '>=', $request->query('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('issue_date', '<=', $request->query('to'));
        }

        $loans = $query->latest('issue_date')->paginate(12)->withQueryString();

        return view('admin.loans.index', compact('loans', 'status'));
    }

    public function create(): View
    {
        $members = User::where('role', 'member')->where('status', 'active')->orderBy('name')->get();
        $books = Book::active()
            ->withCount(['copies as available_copies_count' => fn ($copies) => $copies->where('status', BookCopy::AVAILABLE)])
            ->whereHas('copies', fn ($copies) => $copies->where('status', BookCopy::AVAILABLE))
            ->orderBy('title')->get();
        $issueDate = today()->toDateString();
        $dueDate = today()->addDays(config('library.loan_duration_days'))->toDateString();

        return view('admin.loans.create', compact('members', 'books', 'issueDate', 'dueDate'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'member_id' => ['required', 'integer', Rule::exists('users', 'id')->where('role', 'member')],
            'book_id' => ['required', 'integer', 'exists:books,id'],
            'issue_date' => ['required', 'date_format:Y-m-d'],
            'due_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:issue_date'],
        ]);

        DB::transaction(function () use ($data): void {
            $member = User::whereKey($data['member_id'])->lockForUpdate()->firstOrFail();

            if ($member->status !== 'active') {
                throw ValidationException::withMessages(['member_id' => 'This member account is inactive.']);
            }

            $activeLoans = Loan::where('member_id', $member->id)->where('status', 'active')->count();
            if ($activeLoans >= config('library.borrowing_limit')) {
                throw ValidationException::withMessages(['member_id' => 'This member has reached the borrowing limit.']);
            }

            $book = Book::active()->whereKey($data['book_id'])->first();
            if (! $book) {
                throw ValidationException::withMessages(['book_id' => 'That title is not available in the active catalog.']);
            }

            $copy = BookCopy::where('book_id', $book->id)
                ->where('status', BookCopy::AVAILABLE)
                ->lockForUpdate()
                ->first();

            if (! $copy) {
                throw ValidationException::withMessages(['book_id' => 'There are no available copies of this title.']);
            }

            $copy->update(['status' => BookCopy::CHECKED_OUT]);
            Loan::create([
                'member_id' => $member->id,
                'book_copy_id' => $copy->id,
                'issued_by' => auth()->id(),
                'issue_date' => $data['issue_date'],
                'due_date' => $data['due_date'],
                'status' => 'active',
                'fine_rate' => config('library.fine_rate_per_day'),
                'fine_amount' => 0,
            ]);
        });

        return redirect()->route('admin.loans.index')->with('status', 'Book issued and due date recorded.');
    }

    public function returnBook(Loan $loan): RedirectResponse
    {
        DB::transaction(function () use ($loan): void {
            $loan = Loan::whereKey($loan->id)->lockForUpdate()->firstOrFail();

            if ($loan->status !== 'active' || $loan->return_date !== null) {
                throw ValidationException::withMessages(['loan' => 'This loan has already been returned.']);
            }

            $returnDate = today();
            $dueDate = Carbon::parse($loan->due_date)->startOfDay();
            $overdueDays = $returnDate->startOfDay()->lessThanOrEqualTo($dueDate)
                ? 0
                : (int) $dueDate->diffInDays($returnDate->copy()->startOfDay());

            $loan->update([
                'return_date' => $returnDate->toDateString(),
                'returned_by' => auth()->id(),
                'status' => 'returned',
                'fine_amount' => round($overdueDays * (float) $loan->fine_rate, 2),
            ]);

            $copy = BookCopy::whereKey($loan->book_copy_id)->lockForUpdate()->firstOrFail();
            if ($copy->status === BookCopy::CHECKED_OUT) {
                $copy->update(['status' => BookCopy::AVAILABLE]);
            }
        });

        return back()->with('status', 'Return recorded. Any overdue fine is now finalized.');
    }
}
