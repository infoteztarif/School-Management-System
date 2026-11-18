<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolDepartment extends Model
{
    protected $fillable = ['school_id', 'name', 'code', 'active'];

    public function teams()
    {
        return $this->hasMany(SchoolTeam::class, 'department_id');
    }
}
