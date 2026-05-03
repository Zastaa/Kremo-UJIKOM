<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'no_telp' => ['nullable', 'string', 'max:12', 'regex:/^\d{1,12}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'no_telp.regex' => 'Nomor telepon hanya boleh berisi angka maksimal 12 digit.',
        ];
    }
}
