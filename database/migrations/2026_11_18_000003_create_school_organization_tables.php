<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSchoolOrganizationTables extends Migration
{
    public function up()
    {
        Schema::create('school_departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('code', 40);
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->unique(['school_id', 'code']);
            $table->unique(['school_id', 'name']);
        });

        Schema::create('school_teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->constrained('school_departments')->cascadeOnDelete();
            $table->string('name', 120);
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->unique(['school_id', 'department_id', 'name'], 'school_department_team_unique');
        });

        Schema::create('school_team_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_id')->constrained('school_teams')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_by')->constrained('users');
            $table->timestamps();
            $table->unique(['school_id', 'team_id', 'user_id'], 'school_team_user_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('school_team_users');
        Schema::dropIfExists('school_teams');
        Schema::dropIfExists('school_departments');
    }
}
