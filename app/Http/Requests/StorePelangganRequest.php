<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePelangganRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_pelanggan' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email'],
            'no_ktp' => ['required', 'string', 'size:16', 'unique:pelanggan,no_ktp'],
            'no_telp' => ['required', 'string', 'max:12', 'regex:/^\d{1,12}$/'],
            'alamat' => ['nullable', 'string'],
            'kota1' => ['nullable', 'string'],
            'propinsi1' => ['nullable', 'string'],
            'kodepos1' => ['nullable', 'string'],
            'foto' => ['nullable', 'image', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'no_telp.regex' => 'Nomor telepon hanya boleh berisi angka maksimal 12 digit.',
        ];
    }
}
