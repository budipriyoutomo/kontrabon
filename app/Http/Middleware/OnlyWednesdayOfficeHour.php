<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;

class OnlyWednesdayOfficeHour
{
    public function handle(Request $request, Closure $next): Response
    {
        // Waktu lokal (Indonesia)
        $now = Carbon::now('Asia/Jakarta');

        // 1 = Monday, 3 = Wednesday
        $isWednesday = $now->dayOfWeek === Carbon::WEDNESDAY;

        // Jam kerja
        $startTime = $now->copy()->setTime(8, 0);
        $endTime   = $now->copy()->setTime(16, 0);

        $inOfficeHour = $now->between($startTime, $endTime);

        if (! $isWednesday || ! $inOfficeHour) {
            abort(403, 'Halaman ini hanya dapat diakses hari Rabu pukul 08.00 – 16.00 WIB');
        }

        return $next($request);
    }
}
