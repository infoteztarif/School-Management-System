<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolTeam extends Model
{
    protected $fillable = ['school_id', 'department_id', 'name', 'active'];

    public function department()
    {
        return $this->belongsTo(SchoolDepartment::class, 'department_id');
    }
}
