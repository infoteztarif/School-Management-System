@extends('layouts.master')
@section('page_title', 'SaaS Dashboard')

@section('content')
<style>
    .saas-shell { max-width: 1440px; margin: 0 auto; }
    .saas-header { display:flex; align-items:center; justify-content:space-between; gap:1rem; margin-bottom:1.25rem; }
    .saas-header h1 { font-size:1.6rem; margin:0; }
    .saas-header p { margin:.35rem 0 0; color:#667085; }
    .saas-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:1rem; }
    .metric-card { background:#fff; border:1px solid #e4e7ec; border-radius:14px; padding:1.1rem; box-shadow:0 3px 14px rgba(16,24,40,.05); min-width:0; }
    .metric-label { color:#667085; font-size:.82rem; text-transform:uppercase; letter-spacing:.04em; }
    .metric-value { color:#101828; font-size:1.9rem; font-weight:700; line-height:1.2; margin-top:.45rem; }
    .metric-note { color:#667085; font-size:.82rem; margin-top:.35rem; }
    .saas-panel { background:#fff; border:1px solid #e4e7ec; border-radius:14px; margin-top:1rem; overflow:hidden; }
    .panel-head { display:flex; justify-content:space-between; align-items:center; gap:1rem; padding:1rem 1.1rem; border-bottom:1px solid #eaecf0; }
    .panel-head h2 { font-size:1.1rem; margin:0; }
    .table-scroll { overflow-x:auto; -webkit-overflow-scrolling:touch; }
    .saas-table { width:100%; min-width:720px; margin:0; }
    .saas-table th,.saas-table td { padding:.85rem 1rem; vertical-align:middle; white-space:nowrap; }
    .status-pill { display:inline-flex; padding:.25rem .65rem; border-radius:999px; font-size:.78rem; font-weight:600; }
    .status-active { background:#ecfdf3; color:#027a48; }
    .status-suspended,.status-cancelled { background:#fef3f2; color:#b42318; }
    .empty-state { padding:2rem; text-align:center; color:#667085; }
    @media (max-width:1199.98px) { .saas-grid { grid-template-columns:repeat(2,minmax(0,1fr)); } }
    @media (max-width:767.98px) {
        .content { padding:1rem !important; }
        .saas-header { align-items:flex-start; flex-direction:column; }
        .saas-header h1 { font-size:1.3rem; }
        .saas-grid { grid-template-columns:1fr; gap:.75rem; }
        .metric-card { padding:1rem; }
        .metric-value { font-size:1.55rem; }
        .panel-head { align-items:flex-start; flex-direction:column; }
    }
</style>

<div class="saas-shell">
    <div class="saas-header">
        <div>
            <h1>Super Admin SaaS Dashboard</h1>
            <p>Schools, subscriptions, users and licensed modules overview.</p>
        </div>
        <a href="{{ route('saas.schools.index') }}" class="btn btn-primary">Manage Schools</a>
    </div>

    <form method="GET" class="metric-card mb-3" style="display:grid;grid-template-columns:1fr 1fr auto;gap:.75rem">
        <label>From<input class="form-control" type="date" name="from" value="{{ $from->format('Y-m-d') }}"></label>
        <label>To<input class="form-control" type="date" name="to" value="{{ $to->format('Y-m-d') }}"></label>
        <button class="btn btn-primary align-self-end">Apply filter</button>
    </form>

    <div class="saas-grid">
        <div class="metric-card">
            <div class="metric-label">Schools</div>
            <div class="metric-value">{{ $metrics['schools']['total'] }}</div>
            <div class="metric-note">{{ $metrics['schools']['active'] }} active · {{ $metrics['schools']['suspended'] }} suspended</div>
        </div>
        <div class="metric-card">
            <div class="metric-label">Tenant Users</div>
            <div class="metric-value">{{ $metrics['users']['tenant_users'] }}</div>
            <div class="metric-note">{{ $metrics['users']['school_admins'] }} school admins</div>
        </div>
        <div class="metric-card">
            <div class="metric-label">Active Subscriptions</div>
            <div class="metric-value">{{ $metrics['subscriptions']['active'] }}</div>
            <div class="metric-note">{{ $metrics['subscriptions']['expiring_in_30_days'] }} expire within 30 days</div>
        </div>
        <div class="metric-card">
            <div class="metric-label">Licensed Modules</div>
            <div class="metric-value">{{ $metrics['licensed_modules'] }}</div>
            <div class="metric-note">Currently enabled licenses</div>
        </div>
    </div>

    <section class="saas-panel">
        <div class="panel-head">
            <h2>Schools</h2>
            <span class="text-muted">Responsive table—swipe horizontally on mobile</span>
        </div>
        @if($schools->count())
            <div class="table-scroll">
                <table class="table saas-table">
                    <thead>
                    <tr><th>School</th><th>Code</th><th>Status</th><th>Timezone</th><th>Subscription ends</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                    @foreach($schools as $school)
                        @php($subscription = $school->subscriptions->first())
                        <tr>
                            <td><strong>{{ $school->name }}</strong></td>
                            <td>{{ $school->code }}</td>
                            <td><span class="status-pill status-{{ $school->status }}">{{ ucfirst($school->status) }}</span></td>
                            <td>{{ $school->timezone }}</td>
                            <td>{{ $subscription && $subscription->ends_at ? $subscription->ends_at->format('d M Y') : 'Not assigned' }}</td>
                            <td><a class="btn btn-sm btn-outline-primary" href="{{ route('saas.schools.show', $school) }}">View</a></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-3">{{ $schools->links() }}</div>
        @else
            <div class="empty-state">No schools have been onboarded yet.</div>
        @endif
    </section>
</div>
@endsection
