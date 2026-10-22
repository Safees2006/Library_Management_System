@extends('layouts.app')

@section('title', 'Manage members')

@section('content')
<section class="page-heading page-heading-row"><div><span class="eyebrow">Community accounts</span><h1>Library <em>members.</em></h1><p>Create accounts, update member details, and manage borrowing eligibility.</p></div><a class="btn btn-success button button-dark" href="{{ route('admin.members.create') }}">Add a member <i class="bi bi-plus-lg" aria-hidden="true"></i></a></section>
<section class="panel card list-panel"><div class="list-toolbar"><form class="inline-search" method="GET"><i class="bi bi-search" aria-hidden="true"></i><input type="search" name="q" value="{{ request('q') }}" placeholder="Search name, email, member number" class="form-control"><button class="btn btn-outline-success button button-outline button-small" type="submit">Search</button></form><span class="muted">{{ $members->total() }} members</span></div>
    @if($members->isEmpty())<div class="empty-state"><span class="empty-icon"><i class="bi bi-people" aria-hidden="true"></i></span><h3>No members found</h3><p>Create an account for a library member.</p></div>@else
    <div class="table-scroll table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>Member</th><th>Membership no.</th><th>Contact</th><th>Active loans</th><th>Account</th><th></th></tr></thead><tbody>
    @foreach($members as $member)<tr><td><strong>{{ $member->name }}</strong><small>{{ $member->email }}</small></td><td>{{ $member->membership_number }}</td><td>{{ $member->phone ?: '—' }}</td><td>{{ $member->active_loans_count }}</td><td><span @class(['status-pill', 'status-active' => $member->status === 'active', 'status-muted' => $member->status !== 'active'])>{{ ucfirst($member->status) }}</span></td><td class="table-actions"><a class="btn button button-quiet button-small" href="{{ route('admin.members.edit', $member) }}">Edit</a></td></tr>@endforeach
    </tbody></table></div><div class="pagination-wrap">{{ $members->links('components.pagination') }}</div>@endif
</section>
@endsection
