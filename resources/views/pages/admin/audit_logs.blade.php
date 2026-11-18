@extends('layouts.master')
@section('page_title','Audit Logs')
@section('content')
<style>.audit-card{background:#fff;border:1px solid #e4e7ec;border-radius:14px;overflow:hidden;box-shadow:0 3px 14px rgba(16,24,40,.05)}.audit-scroll{overflow-x:auto}.audit-table{min-width:850px;margin:0}.method{font-weight:700;font-size:.75rem;border-radius:999px;background:#f2f4f7;padding:.2rem .5rem}@media(max-width:575.98px){.content{padding:1rem!important}}</style>
<div class="d-flex justify-content-between flex-wrap mb-3"><div><h2>Audit Logs</h2><p class="text-muted">School activity, changes and security history.</p></div></div>
<div class="audit-card"><div class="audit-scroll"><table class="table audit-table"><thead><tr><th>Date</th><th>User</th><th>Method</th><th>Route</th><th>Action</th><th>Status</th><th>IP</th></tr></thead><tbody>
@forelse($logs as $log)<tr><td>{{$log->created_at}}</td><td>{{$log->user_name??'System'}}</td><td><span class="method">{{$log->method}}</span></td><td>{{$log->route_name??$log->path}}</td><td>{{$log->action}}</td><td>{{$log->status_code}}</td><td>{{$log->ip_address}}</td></tr>@empty<tr><td colspan="7" class="text-center text-muted p-4">No audit activity yet.</td></tr>@endforelse
</tbody></table></div><div class="p-3">{{$logs->links()}}</div></div>
@endsection
