@extends('layouts.master')
@section('page_title','Active Sessions')
@section('content')
<style>.session-grid{display:grid;gap:.75rem}.session-card{background:#fff;border:1px solid #e4e7ec;border-radius:12px;padding:1rem;display:flex;justify-content:space-between;gap:1rem;align-items:center}.session-meta{color:#667085;font-size:.85rem}@media(max-width:575.98px){.session-card{align-items:flex-start;flex-direction:column}.content{padding:1rem!important}}</style>
<h2>Active Sessions</h2><p class="text-muted">Review devices and revoke access you do not recognize.</p>
@if(session('success'))<div class="alert alert-success">{{session('success')}}</div>@endif
<div class="session-grid">@forelse($sessions as $session)<div class="session-card"><div><strong>{{$session->ip_address??'Unknown IP'}}</strong><div class="session-meta">{{$session->user_agent}}</div><div class="session-meta">Last active: {{$session->last_activity_at}} @if($session->revoked_at) · Revoked@endif</div></div>@if(!$session->revoked_at)<form method="POST" action="{{route('sessions.revoke',$session->id)}}">@csrf @method('DELETE')<button class="btn btn-outline-danger btn-sm">Revoke</button></form>@endif</div>@empty<div class="text-muted">No tracked sessions yet.</div>@endforelse</div><div class="mt-3">{{$sessions->links()}}</div>
@endsection
