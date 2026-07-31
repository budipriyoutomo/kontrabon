<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Batasi rute ke peran tertentu: `->middleware('role:kontrabon,billing')`.
 *
 * Admin selalu lolos tanpa perlu ditulis di daftar peran, sejalan dengan
 * Gate::before di AuthServiceProvider. Jadi cukup sebut peran non-admin saja.
 */
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403, 'Anda harus login terlebih dahulu.');
        }

        if (! $user->is_active) {
            abort(403, 'Akun Anda dinonaktifkan.');
        }

        if ($user->isAdmin() || $user->hasRole(...$roles)) {
            return $next($request);
        }

        abort(403, 'Peran Anda tidak memiliki akses ke halaman ini.');
    }
}
