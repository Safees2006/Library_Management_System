@extends('layouts.app')

@section('title', 'Fine register')

@section('content')
<section class="page-heading"><span class="eyebrow">Accounts & payment history</span><h1>Fine <em>register.</em></h1><p>Review finalized fines, estimated overdue balances, and every payment received.</p></section>
<section class="panel card list-panel"><form class="list-toolbar" method="GET"><label class="inline-search"><span>⌕</span><input type="search" name="q" value="{{ request('q') }}" placeholder="Search member or title" class="form-control"></label><button class="btn btn-outline-success button button-outline button-small" type="submit">Search</button><span class="muted">Rates are stored with each loan</span></form>
    @if($loans->isEmpty())<div class="empty-state"><span class="empty-icon">₨</span><h3>All clear</h3><p>No overdue estimates or fine balances to review.</p></div>@else
    <div class="fine-list">
    @foreach($loans as $loan)
        @php($balance = $loan->outstandingFine())
        <article class="fine-card">
            <div class="fine-card-main"><div class="fine-book-mark">₨</div><div><span class="eyebrow">{{ $loan->member->membership_number }}</span><h2>{{ $loan->copy->book->title }}</h2><p>{{ $loan->member->name }} · Due {{ $loan->due_date->format('M j, Y') }} @if(!$loan->return_date)<span class="status-pill status-overdue">{{ $loan->overdueDays() }} days late · estimate</span>@else<span class="status-pill status-returned">Returned {{ $loan->return_date->format('M j, Y') }}</span>@endif</p></div></div>
            <div class="fine-card-side"><div class="fine-balance"><small>{{ $loan->return_date ? 'Unpaid balance' : 'Running estimate' }}</small><strong>{{ config('library.currency') }} {{ number_format($balance, 2) }}</strong><span>Total fine {{ config('library.currency') }} {{ number_format($loan->estimatedFine(), 2) }}</span></div>
                @if($loan->return_date && $balance > 0)<form class="payment-form" method="POST" action="{{ route('admin.fines.payments.store', $loan) }}"><span class="eyebrow">Record payment</span>@csrf<div class="payment-inputs"><label><span class="sr-only">Payment amount</span><input name="amount" type="number" min="0.01" max="{{ number_format($balance, 2, '.', '') }}" step="0.01" placeholder="Amount" required class="form-control"></label><label><span class="sr-only">Payment date</span><input name="paid_at" type="date" value="{{ today()->toDateString() }}" required class="form-control"></label><button class="btn btn-success button button-dark button-small" type="submit">Record</button></div><input type="text" class="payment-note" name="note" maxlength="255" placeholder="Optional note / receipt reference"></form>@endif
            </div>
            @if($loan->finePayments->isNotEmpty())<details class="payment-history"><summary>Payment history · {{ $loan->finePayments->count() }}</summary><div class="table-scroll table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>Date</th><th>Amount</th><th>Received by</th><th>Note</th></tr></thead><tbody>@foreach($loan->finePayments->sortByDesc('paid_at') as $payment)<tr><td>{{ $payment->paid_at->format('M j, Y') }}</td><td>{{ config('library.currency') }} {{ number_format($payment->amount, 2) }}</td><td>{{ $payment->receiver->name }}</td><td>{{ $payment->note ?: '—' }}</td></tr>@endforeach</tbody></table></div></details>@endif
        </article>
    @endforeach
    </div><div class="pagination-wrap">{{ $loans->links() }}</div>@endif
</section>
@endsection
