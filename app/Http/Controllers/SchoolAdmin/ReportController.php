<?php
namespace App\Http\Controllers\SchoolAdmin;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class ReportController extends Controller {
 public function __construct(){ $this->middleware(['auth','subscription.active','permission:import_export.manage']); }
 private function dates(Request $r){
  $v=$r->validate(['from'=>'nullable|date','to'=>'nullable|date|after_or_equal:from']);
  $from=Carbon::parse($v['from']??now()->startOfMonth())->startOfDay();$to=Carbon::parse($v['to']??now())->endOfDay();
  abort_if($from->diffInDays($to)>366,422,'Date range cannot exceed 366 days.');
  return [$from,$to];
 }
 public function index(Request $r){
  [$from,$to]=$this->dates($r);$sid=$r->user()->school_id;abort_unless($sid,403);
  $metrics=[
   'students'=>DB::table('student_records')->where('school_id',$sid)->whereBetween('created_at',[$from,$to])->count(),
   'payments'=>DB::table('payment_records')->where('school_id',$sid)->whereBetween('created_at',[$from,$to])->sum('amt_paid'),
   'receipts'=>DB::table('receipts')->where('school_id',$sid)->whereBetween('created_at',[$from,$to])->count(),
   'audit_events'=>DB::table('tenant_audit_logs')->where('school_id',$sid)->whereBetween('created_at',[$from,$to])->count(),
  ];
  $daily=DB::table('payment_records')->where('school_id',$sid)->whereBetween('created_at',[$from,$to])->selectRaw('DATE(created_at) as day, SUM(amt_paid) as total, COUNT(*) as transactions')->groupBy(DB::raw('DATE(created_at)'))->orderBy('day')->get();
  if($r->expectsJson())return response()->json(compact('metrics','daily','from','to'));
  return view('pages.admin.reports',compact('metrics','daily','from','to'));
 }
 public function export(Request $r,$type){
  [$from,$to]=$this->dates($r);$sid=$r->user()->school_id;abort_unless(in_array($type,['payments','audit']),404);
  $query=$type==='payments'
   ? DB::table('payment_records')->where('school_id',$sid)->whereBetween('created_at',[$from,$to])->select('id','student_id','payment_id','amt_paid','balance','paid','created_at')
   : DB::table('tenant_audit_logs')->where('school_id',$sid)->whereBetween('created_at',[$from,$to])->select('id','user_id','method','route_name','path','status_code','ip_address','created_at');
  $columns=$type==='payments'?['id','student_id','payment_id','amt_paid','balance','paid','created_at']:['id','user_id','method','route_name','path','status_code','ip_address','created_at'];
  return response()->streamDownload(function()use($query,$columns){$out=fopen('php://output','w');fputcsv($out,$columns);$query->orderBy('id')->chunk(500,function($rows)use($out,$columns){foreach($rows as $row)fputcsv($out,array_map(fn($c)=>$row->$c,$columns));});fclose($out);},$type.'-'.$from->format('Ymd').'-'.$to->format('Ymd').'.csv',['Content-Type'=>'text/csv']);
 }
}
