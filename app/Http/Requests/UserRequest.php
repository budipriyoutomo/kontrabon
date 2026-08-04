<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Otorisasi ditangani middleware `role:admin` pada grup rute.
        return true;
    }

    /**
     * Email disamakan ke huruf kecil sebelum divalidasi supaya admin tidak
     * ditolak hanya karena mengetik huruf kapital, sekaligus menjaga
     * pengecekan unik tetap konsisten.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('email')) {
            $this->merge([
                'email' => strtolower(trim((string) $this->input('email'))),
            ]);
        }
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'string', 'lowercase', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'role' => ['required', Rule::in(UserRole::values()), $this->tidakTurunkanPeranSendiri()],
            'is_active' => ['nullable', 'boolean'],
            // Saat edit, password kosong berarti "jangan diubah".
            'password' => [
                $this->isMethod('POST') ? 'required' : 'nullable',
                'confirmed',
                Password::defaults(),
            ],
        ];
    }

    /**
     * Melengkapi pengaman yang sudah ada di UserPolicy: admin tidak boleh
     * menonaktifkan atau menghapus akunnya sendiri, jadi mengganti peran
     * sendiri — yang efeknya sama-sama mengunci diri dari menu ini — juga
     * ditutup.
     */
    private function tidakTurunkanPeranSendiri(): callable
    {
        return function (string $attribute, mixed $value, callable $fail): void {
            $target = $this->route('user');

            if (! $target instanceof \App\Models\User || $target->id !== $this->user()?->id) {
                return;
            }

            if ($value !== $target->role?->value) {
                $fail('Anda tidak dapat mengubah peran akun sendiri.');
            }
        };
    }

    public function attributes(): array
    {
        return [
            'name' => 'nama',
            'email' => 'email',
            'role' => 'peran',
            'password' => 'password',
        ];
    }
}
