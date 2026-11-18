<?php
namespace App\Http\Middleware\Custom;
use Closure;
use Illuminate\Support\Facades\DB;
class AuditTenantMutation {
 public function handle($request,Closure $next){
  $response=$next($request);
  if(in_array($request->method(),['POST','PUT','PATCH','DELETE']) && $request->user()){
   DB::table('tenant_audit_logs')->insert(['school_id'=>$request->user()->school_id,'user_id'=>$request->user()->id,'action'=>$request->route()?->getActionName()??'unknown','method'=>$request->method(),'route_name'=>$request->route()?->getName(),'path'=>substr($request->path(),0,255),'status_code'=>$response->getStatusCode(),'ip_address'=>$request->ip(),'user_agent'=>substr((string)$request->userAgent(),0,500),'metadata'=>json_encode(['input_keys'=>array_keys($request->except(['password','password_confirmation','token']))]),'created_at'=>now(),'updated_at'=>now()]);
  } return $response;
 }
}
