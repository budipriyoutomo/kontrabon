<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TukarFakturStatus;
use App\Http\Controllers\Controller;
use App\Models\Perusahaan;
use App\Models\TukarFaktur;
use Illuminate\Http\Request;

/**
 * Verifikasi tukar faktur.
 *
 * Yang diverifikasi adalah data yang emailnya SUDAH terkirim ke supplier
 * (status `email_sent`) — verifikasi berada setelah pengiriman email, bukan
 * sebelumnya. Hasilnya menjadi sumber data modul billing.
 */
class VerifikasiController extends Controller
{
    /** Status yang boleh ditampilkan di modul ini. */
    private const STATUS_TAMPIL = [
        TukarFakturStatus::EmailSent,
        TukarFakturStatus::Verified,
        TukarFakturStatus::Billing,
    ];

    public function index(Request $request)
    {
        // Default: yang menunggu diverifikasi.
        $status = $request->get('status', TukarFakturStatus::EmailSent->value);

        $statusTerpilih = TukarFakturStatus::tryFrom($status);

        if (! $statusTerpilih || ! in_array($statusTerpilih, self::STATUS_TAMPIL, true)) {
            $statusTerpilih = TukarFakturStatus::EmailSent;
        }

        $query = TukarFaktur::query()
            ->where('status', $statusTerpilih)
            ->with(['perusahaan:id,nama,top', 'verifier:id,name']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('no_kwitansi', 'like', "%{$search}%")
                    ->orWhere('perusahaan_pengaju', 'like', "%{$search}%");
            });
        }

        if ($request->filled('pt_tujuan')) {
            $query->where('pt_tujuan', $request->pt_tujuan);
        }

        if ($request->filled('perusahaan')) {
            $query->where('perusahaan_pengaju', $request->perusahaan);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('tanggal_tukar', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('tanggal_tukar', '<=', $request->end_date);
        }

        $sortable = ['tanggal_tukar', 'tanggal_pembayaran', 'pt_tujuan', 'perusahaan_pengaju', 'jumlah_rupiah', 'verified_at'];
        $sort = $request->get('sort', 'tanggal_tukar');
        $direction = $request->get('direction', 'desc');

        if (! in_array($sort, $sortable, true)) {
            $sort = 'tanggal_tukar';
        }
        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'desc';
        }

        $data = $query->orderBy($sort, $direction)->paginate(20)->withQueryString();

        $ptTujuanOptions = TukarFaktur::distinct()->pluck('pt_tujuan')->filter()->sort()->values();

        $perusahaanOptions = Perusahaan::orderBy('nama')->pluck('nama')
            ->merge(TukarFaktur::distinct()->pluck('perusahaan_pengaju'))
            ->filter()
            ->unique()
            ->sort()
            ->values();

        return view('admin.verifikasi.index', [
            'data' => $data,
            'statusTerpilih' => $statusTerpilih,
            'statusOptions' => collect(self::STATUS_TAMPIL)
                ->mapWithKeys(fn (TukarFakturStatus $s) => [$s->value => $s->label()])
                ->all(),
            'ptTujuanOptions' => $ptTujuanOptions,
            'perusahaanOptions' => $perusahaanOptions,
            'jumlahMenunggu' => TukarFaktur::menungguVerifikasi()->count(),
        ]);
    }

    public function verify(Request $request, string $id)
    {
        $request->validate([
            'verified_note' => ['nullable', 'string', 'max:255'],
        ]);

        $data = TukarFaktur::findOrFail($id);

        $this->authorize('verify', $data);

        if (! $data->status->canTransitionTo(TukarFakturStatus::Verified)) {
            return back()->with(
                'error',
                'Data berstatus ' . $data->status->label() . ' tidak bisa diverifikasi. '
                . 'Hanya data yang emailnya sudah terkirim yang bisa diverifikasi.'
            );
        }

        $data->tandaiTerverifikasi($request->user(), $request->verified_note);

        return back()->with('success', 'Kwitansi ' . $data->no_kwitansi . ' berhasil diverifikasi.');
    }

    public function bulkVerify(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['string'],
            'verified_note' => ['nullable', 'string', 'max:255'],
        ]);

        // Hanya data yang memang menunggu verifikasi yang diproses; sisanya
        // dilewati diam-diam supaya satu baris nyasar tidak membatalkan
        // seluruh batch.
        $kandidat = TukarFaktur::whereIn('id', $validated['ids'])
            ->menungguVerifikasi()
            ->get();

        foreach ($kandidat as $item) {
            $this->authorize('verify', $item);

            $item->tandaiTerverifikasi($request->user(), $validated['verified_note'] ?? null);
        }

        $diproses = $kandidat->count();
        $dilewati = count($validated['ids']) - $diproses;

        $pesan = $diproses . ' data berhasil diverifikasi.';

        if ($dilewati > 0) {
            $pesan .= ' ' . $dilewati . ' data dilewati karena statusnya sudah berubah.';
        }

        return back()->with($diproses > 0 ? 'success' : 'error', $pesan);
    }
}
