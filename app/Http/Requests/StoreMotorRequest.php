<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMotorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_motor' => ['required', 'string', 'max:100'],
            'id_jenis' => ['required', 'exists:jenis_motor,id'],
            'harga_jual' => ['required', 'integer', 'min:0'],
            'berat_gram' => ['required', 'integer', 'min:10000'],
            'deskripsi_motor' => ['nullable', 'string'],
            'warna' => ['nullable', 'string', 'max:50'],
            'kapasitas_mesin' => ['nullable', 'string', 'max:10'],
            'tahun_produksi' => ['nullable', 'integer', 'min:2000', 'max:2030'],
            'foto1' => ['nullable', 'image', 'max:2048'],
            'foto2' => ['nullable', 'image', 'max:2048'],
            'foto3' => ['nullable', 'image', 'max:2048'],
            'stok' => ['required', 'integer', 'min:0'],
        ];
    }
}
