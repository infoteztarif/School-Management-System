<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
class AddPermissionCatalogAndTenantAuditLogs extends Migration {
 public function up(){
  Schema::create('tenant_audit_logs',function(Blueprint $table){$table->id();$table->foreignId('school_id')->nullable()->constrained()->nullOnDelete();$table->unsignedInteger('user_id')->nullable(); $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();$table->string('action',120);$table->string('method',10);$table->string('route_name')->nullable();$table->string('path',255);$table->unsignedSmallInteger('status_code')->nullable();$table->string('ip_address',45)->nullable();$table->string('user_agent',500)->nullable();$table->json('metadata')->nullable();$table->timestamps();$table->index(['school_id','created_at']);});
  $now=now(); $items=[
   ['students.view','View students','students'],['students.create','Create students','students'],['students.update','Update students','students'],['students.delete','Delete students','students'],
   ['academics.manage','Manage classes, sections and subjects','academics'],['timetable.manage','Manage timetables','timetable'],['exams.manage','Manage exams and grades','exams'],['marks.manage','Manage marks','marks'],
   ['payments.view','View payments','payments'],['payments.manage','Manage payments','payments'],['users.manage','Manage school users','users'],['organization.manage','Manage departments, teams and roles','organization'],
   ['attendance.manage','Manage attendance','attendance'],['leave.manage','Manage leave','leave'],['payroll.manage','Manage payroll','payroll'],['transport.manage','Manage transport','transport'],['inventory.manage','Manage inventory','inventory'],['lms.manage','Manage LMS','lms'],
   ['audit.view','View audit logs','audit'],['import_export.manage','Import and export data','data'],['backup.manage','Manage backups','backup']
  ]; foreach($items as $i){DB::table('permissions')->updateOrInsert(['code'=>$i[0]],['name'=>$i[1],'module_code'=>$i[2],'created_at'=>$now,'updated_at'=>$now]);}
  $permissionIds=DB::table('permissions')->pluck('id');
  $adminRoleIds=DB::table('school_roles')->where('name','School Admin')->pluck('id');
  foreach($adminRoleIds as $roleId){foreach($permissionIds as $permissionId){DB::table('role_permissions')->insertOrIgnore(['school_role_id'=>$roleId,'permission_id'=>$permissionId]);}}
 }
 public function down(){Schema::dropIfExists('tenant_audit_logs');}
}
