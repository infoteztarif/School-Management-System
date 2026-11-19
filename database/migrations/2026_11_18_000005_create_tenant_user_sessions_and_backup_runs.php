<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
class CreateTenantUserSessionsAndBackupRuns extends Migration {
 public function up(){
  Schema::create('tenant_user_sessions',function(Blueprint $table){$table->id();$table->foreignId('school_id')->nullable()->constrained()->cascadeOnDelete();$table->unsignedInteger('user_id'); $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();$table->string('session_id',120)->unique();$table->string('ip_address',45)->nullable();$table->string('user_agent',500)->nullable();$table->timestamp('last_activity_at');$table->timestamp('revoked_at')->nullable();$table->timestamps();$table->index(['school_id','user_id','last_activity_at'],'tenant_user_session_lookup');});
  Schema::create('backup_runs',function(Blueprint $table){$table->id();$table->foreignId('school_id')->nullable()->constrained()->nullOnDelete();$table->unsignedInteger('requested_by'); $table->foreign('requested_by')->references('id')->on('users');$table->string('scope',30);$table->string('status',30);$table->string('disk',60)->nullable();$table->string('path')->nullable();$table->unsignedBigInteger('size_bytes')->nullable();$table->text('error')->nullable();$table->timestamps();});
 }
 public function down(){Schema::dropIfExists('backup_runs');Schema::dropIfExists('tenant_user_sessions');}
}
