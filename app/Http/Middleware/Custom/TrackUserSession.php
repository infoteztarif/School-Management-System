<?php
namespace App\Http\Middleware\Custom;
use Closure;
use Illuminate\Support\Facades\DB;
class TrackUserSession {
 public function handle($request,Closure $next){
  $user=$request->user(); if($user && $request->hasSession()){
   $sessionId=$request->session()->getId();
   $revoked=DB::table('tenant_user_sessions')->where('session_id',$sessionId)->whereNotNull('revoked_at')->exists();
   if($revoked){auth()->logout();$request->session()->invalidate();return redirect()->route('login')->withErrors(['session'=>'This session was revoked.']);}
   DB::table('tenant_user_sessions')->updateOrInsert(['session_id'=>$sessionId],['school_id'=>$user->school_id,'user_id'=>$user->id,'ip_address'=>$request->ip(),'user_agent'=>substr((string)$request->userAgent(),0,500),'last_activity_at'=>now(),'updated_at'=>now(),'created_at'=>now()]);
  } return $next($request);
 }
}
