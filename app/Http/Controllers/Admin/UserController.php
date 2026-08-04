<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/**
 * Manajemen akun pengguna. Seluruh rute controller ini berada di bawah
 * middleware `role:admin`; pendaftaran mandiri sudah ditutup.
 */
class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'aktif');
        }

        $sort = $request->get('sort', 'name');
        $direction = $request->get('direction', 'asc');

        if (! in_array($sort, ['name', 'email', 'role', 'is_active', 'created_at'])) {
            $sort = 'name';
        }
        if (! in_array($direction, ['asc', 'desc'])) {
            $direction = 'asc';
        }

        $data = $query->orderBy($sort, $direction)->paginate(20)->withQueryString();

        return view('admin.users.index', [
            'data' => $data,
            'roleOptions' => UserRole::options(),
        ]);
    }

    public function create()
    {
        return view('admin.users.create', [
            'roleOptions' => UserRole::options(),
        ]);
    }

    public function store(UserRequest $request)
    {
        $validated = $request->validated();

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'is_active' => $request->boolean('is_active', true),
            'password' => $validated['password'],
            'email_verified_at' => now(),
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', [
            'user' => $user,
            'roleOptions' => UserRole::options(),
        ]);
    }

    public function update(UserRequest $request, User $user)
    {
        $validated = $request->validated();

        $payload = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ];

        // Admin tidak boleh menonaktifkan akunnya sendiri lewat form edit.
        if ($request->user()->can('toggleActive', $user)) {
            $payload['is_active'] = $request->boolean('is_active');
        }

        if (! empty($validated['password'])) {
            $payload['password'] = $validated['password'];
        }

        $user->update($payload);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->id === request()->user()->id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        $this->authorize('delete', $user);

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Pengguna berhasil dihapus.');
    }

    public function toggleActive(User $user)
    {
        if ($user->id === request()->user()->id) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun sendiri.');
        }

        $this->authorize('toggleActive', $user);

        $user->update(['is_active' => ! $user->is_active]);

        return back()->with(
            'success',
            $user->is_active
                ? 'Akun ' . $user->name . ' diaktifkan.'
                : 'Akun ' . $user->name . ' dinonaktifkan.'
        );
    }

    public function resetPassword(Request $request, User $user)
    {
        // Error bag dipisah per pengguna karena form reset password berada di
        // dalam tabel daftar: tanpa ini, satu baris yang gagal validasi akan
        // memunculkan pesan error di dialog semua baris.
        $request->validateWithBag(self::resetPasswordBag($user), [
            'password' => ['required', 'confirmed', Password::defaults()],
        ], [], [
            'password' => 'password baru',
        ]);

        $user->update(['password' => $request->password]);

        return back()->with('success', 'Password ' . $user->name . ' berhasil direset.');
    }

    /**
     * Nama error bag untuk form reset password satu pengguna. Dipakai bersama
     * oleh controller dan view daftar pengguna.
     */
    public static function resetPasswordBag(User $user): string
    {
        return 'resetPassword' . $user->id;
    }
}
