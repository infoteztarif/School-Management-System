<?php
namespace App\Http\Controllers\SchoolAdmin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class AuditLogController extends Controller {
 public function __construct(){ $this->middleware(['auth','subscription.active','permission:audit.view']); }
 public function index(Request $request){
  $logs=DB::table('tenant_audit_logs')->leftJoin('users','users.id','=','tenant_audit_logs.user_id')->where('tenant_audit_logs.school_id',$request->user()->school_id)
   ->select('tenant_audit_logs.*','users.name as user_name')->when($request->filled('method'),fn($q)=>$q->where('method',$request->method))->latest('tenant_audit_logs.created_at')->paginate(30);
  if($request->expectsJson())return response()->json($logs);
  return view('pages.admin.audit_logs',compact('logs'));
 }
}
