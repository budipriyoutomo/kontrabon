<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\TukarFakturStatus;
use App\Models\TukarFaktur;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        // Pengguna tanpa akses data tukar faktur tetap boleh membuka dashboard,
        // hanya saja ringkasannya tidak diisi.
        if ($request->user()->cannot('viewAny', TukarFaktur::class)) {
            return view('dashboard', [
                'ringkasan' => null,
                'terbaru' => collect(),
            ]);
        }

        $jumlahPerStatus = TukarFaktur::query()
            ->selectRaw('status, count(*) as jumlah')
            ->groupBy('status')
            ->pluck('jumlah', 'status');

        $ringkasan = [
            'total' => (int) $jumlahPerStatus->sum(),
            'pending' => (int) $jumlahPerStatus->get(TukarFakturStatus::Pending->value, 0),
            'menungguVerifikasi' => (int) $jumlahPerStatus->get(TukarFakturStatus::EmailSent->value, 0),
            'terverifikasi' => (int) $jumlahPerStatus->get(TukarFakturStatus::Verified->value, 0),
            'billing' => (int) $jumlahPerStatus->get(TukarFakturStatus::Billing->value, 0),
        ];

        $terbaru = TukarFaktur::query()
            ->latest('tanggal_tukar')
            ->latest('id')
            ->limit(8)
            ->get();

        return view('dashboard', compact('ringkasan', 'terbaru'));
    }
}
