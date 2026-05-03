<?php

namespace App\Http\Requests;

use App\Models\Pelanggan;
use Illuminate\Foundation\Http\FormRequest;

class StorePengajuanKreditRequest extends FormRequest
{
    public const DOCUMENT_MAX_KB = 2048;

    private const DOCUMENT_FIELDS = [
        'url_kk' => 'File KK',
        'url_ktp' => 'File KTP',
        'url_npwp' => 'File NPWP',
        'url_slip_gaji' => 'File slip gaji',
        'url_foto' => 'File foto',
    ];

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->user()?->role === 'customer') {
            $pelanggan = Pelanggan::where('email', $this->user()->email)->first();

            if ($pelanggan) {
                $this->merge(['id_pelanggan' => $pelanggan->id]);
            }
        }
    }

    public function rules(): array
    {
        $documentRules = ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:' . self::DOCUMENT_MAX_KB];
        $rules = [
            'id_motor' => ['required', 'exists:motor,id'],
            'harga_cash' => ['required', 'integer', 'min:0'],
            'dp' => ['required', 'integer', 'min:0'],
            'id_jenis_cicilan' => ['required', 'exists:jenis_cicilan,id'],
            'id_metode_bayar' => ['required', 'exists:metode_bayar,id'],
            'id_asuransi' => ['nullable', 'exists:asuransi,id'],
            'url_kk' => $documentRules,
            'url_ktp' => $documentRules,
            'url_npwp' => $documentRules,
            'url_slip_gaji' => $documentRules,
            'url_foto' => $documentRules,
        ];

        $rules['id_pelanggan'] = ['required', 'exists:pelanggan,id'];

        return $rules;
    }

    public function messages(): array
    {
        $messages = [
            'id_motor.required' => 'Motor wajib dipilih.',
            'id_motor.exists' => 'Motor yang dipilih tidak valid.',
            'harga_cash.required' => 'Harga cash belum terisi. Pilih motor terlebih dahulu.',
            'dp.required' => 'DP belum terisi. Pilih motor terlebih dahulu.',
            'id_jenis_cicilan.required' => 'Tenor cicilan wajib dipilih.',
            'id_metode_bayar.required' => 'Metode bayar wajib dipilih.',
            'id_metode_bayar.exists' => 'Metode bayar yang dipilih tidak valid.',
            'id_pelanggan.required' => 'Pelanggan wajib dipilih atau profil pelanggan wajib dilengkapi.',
            'id_pelanggan.exists' => 'Lengkapi data pribadi terlebih dahulu sebelum membuat pengajuan kredit.',
            'nama_pelanggan.required' => 'Nama lengkap wajib diisi.',
            'no_ktp.required' => 'Nomor KTP wajib diisi.',
            'no_ktp.size' => 'Nomor KTP harus 16 digit.',
            'no_telp.required' => 'Nomor telepon wajib diisi.',
            'alamat.required' => 'Alamat lengkap wajib diisi.',
        ];

        foreach (self::DOCUMENT_FIELDS as $field => $label) {
            $messages["{$field}.file"] = "{$label} harus berupa file yang valid.";
            $messages["{$field}.mimes"] = "{$label} harus berformat JPG, PNG, atau PDF.";
            $messages["{$field}.max"] = "{$label} maksimal 2 MB.";
        }

        return $messages;
    }

    public function attributes(): array
    {
        return [
            'id_motor' => 'motor',
            'harga_cash' => 'harga cash',
            'dp' => 'DP',
            'id_jenis_cicilan' => 'tenor cicilan',
            'id_metode_bayar' => 'metode bayar',
            'id_asuransi' => 'asuransi',
            'id_pelanggan' => 'pelanggan',
            'nama_pelanggan' => 'nama lengkap',
            'no_ktp' => 'nomor KTP',
            'no_telp' => 'nomor telepon',
            'alamat' => 'alamat lengkap',
            ...self::DOCUMENT_FIELDS,
        ];
    }
}
