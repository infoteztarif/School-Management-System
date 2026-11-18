<?php
namespace App\Http\Controllers\SchoolAdmin;
use App\Http\Controllers\Controller;
use App\Models\SchoolDepartment;
use App\Models\SchoolTeam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
class OrganizationController extends Controller {
 public function __construct(){ $this->middleware(['auth','subscription.active','teamSA']); }
 public function index(Request $request){
  $schoolId=$request->user()->school_id; abort_unless($schoolId,403,'A school workspace is required.');
  $departments=SchoolDepartment::where('school_id',$schoolId)->with(['teams'=>function($q)use($schoolId){$q->where('school_id',$schoolId)->orderBy('name');}])->orderBy('name')->get();
  $roles=DB::table('school_roles')->where('school_id',$schoolId)->orderBy('name')->get();
  $permissions=DB::table('permissions')->orderBy('module_code')->orderBy('name')->get();
  $rolePermissions=DB::table('role_permissions')->whereIn('school_role_id',$roles->pluck('id'))->get()->groupBy('school_role_id');
  if($request->expectsJson()) return response()->json(compact('departments','roles','permissions','rolePermissions'));
  return view('pages.admin.organization',compact('departments','roles','permissions','rolePermissions'));
 }
 public function storeDepartment(Request $request){
  $schoolId=$request->user()->school_id; abort_unless($schoolId,403);
  $data=$request->validate(['name'=>['required','string','max:120',Rule::unique('school_departments')->where('school_id',$schoolId)],'code'=>['required','string','max:40','alpha_dash',Rule::unique('school_departments')->where('school_id',$schoolId)]]);
  SchoolDepartment::create(['school_id'=>$schoolId,'name'=>trim($data['name']),'code'=>strtoupper($data['code']),'active'=>true]);
  return redirect()->route('school.organization.index')->with('success','Department created.');
 }
 public function storeTeam(Request $request){
  $schoolId=$request->user()->school_id; abort_unless($schoolId,403);
  $data=$request->validate(['department_id'=>['required',Rule::exists('school_departments','id')->where('school_id',$schoolId)],'name'=>'required|string|max:120']);
  SchoolTeam::firstOrCreate(['school_id'=>$schoolId,'department_id'=>$data['department_id'],'name'=>trim($data['name'])],['active'=>true]);
  return redirect()->route('school.organization.index')->with('success','Team created.');
 }
 public function storeRole(Request $request){
  $schoolId=$request->user()->school_id; abort_unless($schoolId,403);
  $data=$request->validate(['name'=>['required','string','max:120',Rule::unique('school_roles')->where('school_id',$schoolId)]]);
  DB::table('school_roles')->insert(['school_id'=>$schoolId,'name'=>trim($data['name']),'is_system'=>false,'created_at'=>now(),'updated_at'=>now()]);
  return redirect()->route('school.organization.index')->with('success','Role created.');
 }
 public function syncPermissions(Request $request,$roleId){
  $schoolId=$request->user()->school_id; $role=DB::table('school_roles')->where('id',$roleId)->where('school_id',$schoolId)->first(); abort_unless($role,404);
  $data=$request->validate(['permission_ids'=>'nullable|array','permission_ids.*'=>'integer|exists:permissions,id']);
  DB::transaction(function()use($roleId,$data){DB::table('role_permissions')->where('school_role_id',$roleId)->delete(); foreach(array_unique($data['permission_ids']??[]) as $id) DB::table('role_permissions')->insert(['school_role_id'=>$roleId,'permission_id'=>$id]);});
  return redirect()->route('school.organization.index')->with('success','Permissions updated.');
 }
}
