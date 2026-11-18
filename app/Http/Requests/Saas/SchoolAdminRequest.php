<?php

namespace App\Http\Requests\Saas;

use App\Helpers\Qs;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SchoolAdminRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check() && Qs::userIsSuperAdmin();
    }

    public function rules()
    {
        return [
            'school_id' => 'required|exists:schools,id',
            'name' => 'required|string|min:2|max:150',
            'username' => 'required|string|min:3|max:100|alpha_dash|unique:users,username',
            'email' => 'required|email:rfc|max:190|unique:users,email',
            'phone' => 'nullable|string|max:30',
            'password' => 'required|string|min:12|confirmed',
            'role_ends_at' => 'nullable|date|after:now',
        ];
    }
}
