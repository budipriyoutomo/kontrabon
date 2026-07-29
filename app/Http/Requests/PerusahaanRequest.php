<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PerusahaanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('perusahaan');

        return [
            'kode' => [
                'nullable', 'string', 'max:50',
                Rule::unique('perusahaans', 'kode')->ignore($id)->whereNull('deleted_at'),
            ],
            'nama' => [
                'required', 'string', 'max:255',
                Rule::unique('perusahaans', 'nama')->ignore($id)->whereNull('deleted_at'),
            ],
            'npwp'     => ['nullable', 'string', 'max:50'],
            'top'      => ['nullable', 'integer', 'min:0', 'max:365'],
            'alamat'   => ['nullable', 'string', 'max:255'],
            'telepon'  => ['nullable', 'string', 'max:50'],
            'email'    => ['nullable', 'email', 'max:255'],
            'nama_pic' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'kode'     => 'kode perusahaan',
            'nama'     => 'nama perusahaan',
            'top'      => 'TOP (term of payment)',
            'nama_pic' => 'nama PIC',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'email'     => $this->email ? strtolower(trim($this->email)) : null,
        ]);
    }
}
