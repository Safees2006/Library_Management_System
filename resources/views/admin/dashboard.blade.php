@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="library-dashboard">
    <section class="library-metric-grid" aria-label="Library overview">
        <a class="library-metric-card" href="{{ route('admin.books.index') }}">
            <span class="library-metric-copy"><small>COLLECTION TITLES</small><strong data-count-target="{{ $stats['titles'] }}">{{ number_format($stats['titles']) }}</strong><em>{{ number_format($stats['copies']) }} physical copies</em></span>
            <span class="library-metric-icon metric-icon-green" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M5 4.5h11.8A2.2 2.2 0 0 1 19 6.7V20H7a2 2 0 0 1-2-2V4.5Z"/><path d="M5 17.5c0-1.1.9-2 2-2h12M9 8h6M9 11h6"/></svg></span>
        </a>
        <a class="library-metric-card" href="{{ route('admin.books.index') }}">
            <span class="library-metric-copy"><small>AVAILABLE COPIES</small><strong data-count-target="{{ $stats['available'] }}">{{ number_format($stats['available']) }}</strong><em>{{ number_format($stats['low_stock']) }} titles running low</em></span>
            <span class="library-metric-icon metric-icon-blue" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="m5 12 4.2 4.2L19 6.5"/><path d="M20 12a8 8 0 1 1-2.3-5.6"/></svg></span>
        </a>
        <a class="library-metric-card" href="{{ route('admin.members.index') }}">
            <span class="library-metric-copy"><small>REGISTERED MEMBERS</small><strong data-count-target="{{ $stats['members'] }}">{{ number_format($stats['members']) }}</strong><em>Active library accounts</em></span>
            <span class="library-metric-icon metric-icon-violet" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3"/><path d="M3.5 19v-1.4A4.6 4.6 0 0 1 8.1 13h1.8a4.6 4.6 0 0 1 4.6 4.6V19ZM16 5.5a3 3 0 0 1 0 5.8M17 13.2a4.4 4.4 0 0 1 3.5 4.3V19h-3"/></svg></span>
        </a>
        <a class="library-metric-card" href="{{ route('admin.loans.index', ['status' => 'active']) }}">
            <span class="library-metric-copy"><small>BOOKS ON LOAN</small><strong data-count-target="{{ $stats['active_loans'] }}">{{ number_format($stats['active_loans']) }}</strong><em>{{ number_format($stats['overdue']) }} currently overdue</em></span>
            <span class="library-metric-icon metric-icon-rose" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 5.5h13.5A2.5 2.5 0 0 1 20 8v11H6a2 2 0 0 1-2-2V5.5Z"/><path d="M4 16.5h16M8 9h8M8 12h5"/></svg></span>
        </a>
    </section>

    <section class="admin-panel card activity-panel" aria-labelledby="activity-title">
        <div class="activity-panel-heading">
            <div><span class="admin-section-kicker">CIRCULATION</span><h1 id="activity-title">7-Day Library Activity</h1><p>Books issued and returned over the past week.</p></div>
            <div class="activity-legend"><span><i class="legend-issued"></i>Issued</span><span><i class="legend-returned"></i>Returned</span></div>
        </div>
        <figure class="circulation-chart">
            <svg viewBox="0 0 1000 350" preserveAspectRatio="none" role="img" aria-labelledby="chart-title chart-description">
                <title id="chart-title">Book issues and returns during the past seven days</title>
                <desc id="chart-description">A line chart comparing the number of books issued and returned each day.</desc>
                <defs><linearGradient id="issued-area" x1="0" x2="0" y1="0" y2="1"><stop offset="0%" stop-color="#3b82f6" stop-opacity=".16"/><stop offset="100%" stop-color="#3b82f6" stop-opacity="0"/></linearGradient></defs>
                @foreach ($trend['grid'] as $line)
                    <line class="chart-grid-line" x1="76" y1="{{ $line['y'] }}" x2="922" y2="{{ $line['y'] }}" />
                    <text class="chart-axis-label" x="58" y="{{ $line['y'] + 4 }}" text-anchor="end">{{ $line['label'] }}</text>
                @endforeach
                <polygon class="issued-area" points="{{ $trend['issued_area_points'] }}" />
                <polyline class="chart-line chart-line-issued" points="{{ $trend['issued_points'] }}" />
                <polyline class="chart-line chart-line-returned" points="{{ $trend['returned_points'] }}" />
                @foreach ($trend['days'] as $day)
                    <circle class="chart-point chart-point-issued" cx="{{ $day['x'] }}" cy="{{ $day['issued_y'] }}" r="4" data-chart-date="{{ $day['date'] }}" data-chart-issued="{{ $day['issued'] }}" data-chart-returned="{{ $day['returned'] }}" tabindex="0" aria-label="{{ $day['date'] }}: {{ $day['issued'] }} issued, {{ $day['returned'] }} returned" />
                    <circle class="chart-point chart-point-returned" cx="{{ $day['x'] }}" cy="{{ $day['returned_y'] }}" r="4" data-chart-date="{{ $day['date'] }}" data-chart-issued="{{ $day['issued'] }}" data-chart-returned="{{ $day['returned'] }}" tabindex="0" aria-label="{{ $day['date'] }}: {{ $day['issued'] }} issued, {{ $day['returned'] }} returned" />
                    <text class="chart-day-label" x="{{ $day['x'] }}" y="315" text-anchor="middle">{{ $day['date'] }}</text>
                @endforeach
            </svg>
            <div class="chart-tooltip" data-chart-tooltip role="tooltip" hidden>
                <strong data-chart-tooltip-date></strong>
                <span class="chart-tooltip-issued"><i>Issued</i><b data-chart-tooltip-issued></b></span>
                <span class="chart-tooltip-returned"><i>Returned</i><b data-chart-tooltip-returned></b></span>
            </div>
        </figure>
    </section>

    <section class="dashboard-lower-grid row g-3" aria-label="Recent loans and quick actions">
        <div class="admin-panel card recent-loans-panel col-12 col-xl-8">
            <div class="admin-panel-heading"><div><span class="admin-section-kicker">LATEST ACTIVITY</span><h2>Recent loans</h2></div><a class="admin-panel-link" href="{{ route('admin.loans.index') }}">View all <span aria-hidden="true">&rarr;</span></a></div>
            @if ($recentLoans->isEmpty())
                <div class="empty-inline">No loans have been recorded yet.</div>
            @else
                <div class="table-scroll table-responsive"><table class="table table-hover align-middle mb-0">
                    <thead><tr><th>Member</th><th>Book</th><th>Due date</th><th>Status</th></tr></thead>
                    <tbody>
                        @foreach ($recentLoans as $loan)
                            <tr>
                                <td><strong>{{ $loan->member->name }}</strong><small>{{ $loan->member->membership_number }}</small></td>
                                <td>{{ $loan->copy->book->title }}<small>{{ $loan->copy->barcode }}</small></td>
                                <td>{{ $loan->due_date->format('M j, Y') }}</td>
                                <td><span @class(['status-pill', 'status-overdue' => $loan->status === 'active' && $loan->due_date->lt(today()), 'status-active' => $loan->status === 'active' && ! $loan->due_date->lt(today()), 'status-returned' => $loan->status === 'returned'])>{{ $loan->status === 'returned' ? 'Returned' : ($loan->due_date->lt(today()) ? 'Overdue' : 'On loan') }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table></div>
            @endif
        </div>

        <aside class="admin-panel card dashboard-quick-panel col-12 col-xl-4">
            <div class="admin-panel-heading"><div><span class="admin-section-kicker">SHORTCUTS</span><h2>Quick actions</h2></div></div>
            <a class="dashboard-quick-action" href="{{ route('admin.loans.create') }}"><span class="quick-action-mark">+</span><span><strong>Issue a book</strong><small>Check out a copy to a member</small></span><b aria-hidden="true">&rarr;</b></a>
            <a class="dashboard-quick-action" href="{{ route('admin.books.create') }}"><span class="quick-action-mark">+</span><span><strong>Add a book</strong><small>Update the library collection</small></span><b aria-hidden="true">&rarr;</b></a>
            <a class="dashboard-quick-action" href="{{ route('admin.members.create') }}"><span class="quick-action-mark">+</span><span><strong>Register a member</strong><small>Create a library account</small></span><b aria-hidden="true">&rarr;</b></a>
            <a class="dashboard-quick-action" href="{{ route('admin.fines.index') }}"><span class="quick-action-mark">&#163;</span><span><strong>Review fine balances</strong><small>{{ config('library.currency') }} {{ number_format($stats['outstanding_fines'], 0) }} outstanding</small></span><b aria-hidden="true">&rarr;</b></a>
        </aside>
    </section>
</div>
@endsection
