<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return auth()->user()->isAdmin()
            ? redirect()->route('admin.dashboard')
            : redirect()->route('member.account');
    }

    public function admin(): View
    {
        $stats = [
            'titles' => Book::active()->count(),
            'copies' => BookCopy::count(),
            'available' => BookCopy::where('status', BookCopy::AVAILABLE)->count(),
            'low_stock' => Book::active()
                ->withCount(['copies as available_copies_count' => fn ($copies) => $copies->where('status', BookCopy::AVAILABLE)])
                ->get()->filter(fn (Book $book): bool => $book->available_copies_count <= 1)->count(),
            'members' => User::where('role', 'member')->count(),
            'active_loans' => Loan::where('status', 'active')->count(),
            'overdue' => Loan::where('status', 'active')->whereDate('due_date', '<', today())->count(),
            'outstanding_fines' => Loan::with('finePayments')->get()
                ->sum(fn (Loan $loan): float => $loan->outstandingFine()),
        ];

        $recentLoans = Loan::with(['member', 'copy.book'])
            ->latest('issue_date')
            ->limit(6)
            ->get();

        $activityStart = today()->subDays(6);
        $issueCounts = Loan::query()
            ->selectRaw('DATE(issue_date) as activity_date, COUNT(*) as total')
            ->whereDate('issue_date', '>=', $activityStart->toDateString())
            ->whereDate('issue_date', '<=', today()->toDateString())
            ->groupBy('activity_date')
            ->pluck('total', 'activity_date');
        $returnCounts = Loan::query()
            ->selectRaw('DATE(return_date) as activity_date, COUNT(*) as total')
            ->whereNotNull('return_date')
            ->whereDate('return_date', '>=', $activityStart->toDateString())
            ->whereDate('return_date', '<=', today()->toDateString())
            ->groupBy('activity_date')
            ->pluck('total', 'activity_date');

        $dailyActivity = collect(range(6, 0))->map(function (int $daysAgo) use ($issueCounts, $returnCounts): array {
            $date = today()->subDays($daysAgo);
            $dateKey = $date->toDateString();

            return [
                'date' => $date->format('M j'),
                'issued' => (int) ($issueCounts[$dateKey] ?? 0),
                'returned' => (int) ($returnCounts[$dateKey] ?? 0),
            ];
        });

        $chartMax = max(4, (int) ceil(max($dailyActivity->max('issued'), $dailyActivity->max('returned')) * 1.2));
        $chartGrid = collect(range(0, 4))->map(fn (int $step): array => [
            'y' => 50 + ($step * 55),
            'label' => (string) round($chartMax * (1 - ($step / 4))),
        ]);
        $chartPoints = $dailyActivity->values()->map(function (array $day, int $index) use ($chartMax): array {
            return [
                ...$day,
                'x' => 76 + ($index * 141),
                'issued_y' => 270 - (int) round(($day['issued'] / $chartMax) * 220),
                'returned_y' => 270 - (int) round(($day['returned'] / $chartMax) * 220),
            ];
        });
        $issuedPoints = $chartPoints->map(fn (array $point): string => $point['x'].','.$point['issued_y'])->implode(' ');
        $returnedPoints = $chartPoints->map(fn (array $point): string => $point['x'].','.$point['returned_y'])->implode(' ');
        $trend = [
            'days' => $chartPoints,
            'grid' => $chartGrid,
            'issued_points' => $issuedPoints,
            'returned_points' => $returnedPoints,
            'issued_area_points' => '76,280 '.$issuedPoints.' 922,280',
        ];

        return view('admin.dashboard', compact('stats', 'recentLoans', 'trend'));
    }

    public function member(): View
    {
        $member = auth()->user();
        $loans = Loan::with(['copy.book', 'finePayments'])
            ->where('member_id', $member->id)
            ->latest('issue_date')
            ->paginate(8);

        $outstanding = Loan::with('finePayments')
            ->where('member_id', $member->id)
            ->get()
            ->sum(fn (Loan $loan): float => $loan->outstandingFine());

        return view('member.account', compact('member', 'loans', 'outstanding'));
    }
}
