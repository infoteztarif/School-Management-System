<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Saas\SchoolAdminRequest;
use App\Models\School;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SchoolAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('super_admin');
    }

    public function index(School $school)
    {
        $admins = User::query()
            ->where('school_id', $school->id)
            ->whereHas('userType', function ($query) {
                $query->where('title', 'admin');
            })
            ->select(['id', 'school_id', 'name', 'username', 'email', 'phone', 'created_at'])
            ->orderBy('name')
            ->paginate(25);

        return response()->json($admins);
    }

    public function store(SchoolAdminRequest $request)
    {
        $admin = DB::transaction(function () use ($request) {
            $data = $request->validated();
            $adminTypeId = DB::table('user_types')->where('title', 'admin')->value('id');
            abort_unless($adminTypeId, 422, 'Admin user type is not configured.');

            $admin = User::create([
                'school_id' => $data['school_id'],
                'name' => $data['name'],
                'username' => $data['username'],
                'email' => strtolower($data['email']),
                'phone' => $data['phone'] ?? null,
                'password' => Hash::make($data['password']),
                'user_type' => $adminTypeId,
                'email_verified_at' => now(),
            ]);

            $roleId = DB::table('school_roles')->updateOrInsert(
                ['school_id' => $data['school_id'], 'name' => 'School Admin'],
                ['is_system' => true, 'updated_at' => now(), 'created_at' => now()]
            );

            $role = DB::table('school_roles')
                ->where('school_id', $data['school_id'])
                ->where('name', 'School Admin')
                ->first();

            DB::table('user_school_roles')->insert([
                'school_id' => $data['school_id'],
                'user_id' => $admin->id,
                'school_role_id' => $role->id,
                'starts_at' => now(),
                'ends_at' => $data['role_ends_at'] ?? null,
                'active' => true,
                'assigned_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return $admin;
        });

        return response()->json([
            'data' => $admin->only(['id', 'school_id', 'name', 'username', 'email', 'phone']),
        ], 201);
    }
}
