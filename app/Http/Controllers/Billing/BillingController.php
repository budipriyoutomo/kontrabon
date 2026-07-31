<?php

namespace App\Http\Controllers\Billing;

use App\Enums\TukarFakturStatus;
use App\Http\Controllers\Controller;
use App\Models\Perusahaan;
use App\Models\TukarFaktur;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Rekap pembayaran.
 *
 * Sumbernya HANYA data yang sudah lolos verifikasi (status `verified` dan
 * `billing`). Data `pending` maupun `email_sent` tidak boleh bocor ke sini
 * karena angkanya belum diperiksa siapa pun.
 */
class BillingController extends Controller
{
    /** Status yang boleh muncul di modul ini. */
    private const STATUS_TAMPIL = [
        TukarFakturStatus::Verified,
        TukarFakturStatus::Billing,
    ];

    /**
     * Query terfilter, dipakai bersama oleh tabel, ringkasan, rekap, dan
     * export supaya angkanya selalu konsisten.
     */
    private function filteredQuery(Request $request)
    {
        $query = TukarFaktur::query()->terverifikasi();

        // Penyempitan status, tetap di dalam batas STATUS_TAMPIL.
        $status = TukarFakturStatus::tryFrom((string) $request->get('status'));

        if ($status && in_array($status, self::STATUS_TAMPIL, true)) {
            $query->where('status', $status);
        }

        // Sumbu utama modul ini: tanggal bayar.
        if ($request->filled('start_bayar')) {
            $query->whereDate('tanggal_pembayaran', '>=', $request->start_bayar);
        }

        if ($request->filled('end_bayar')) {
            $query->whereDate('tanggal_pembayaran', '<=', $request->end_bayar);
        }

        if ($request->filled('pt_tujuan')) {
            $query->where('pt_tujuan', $request->pt_tujuan);
        }

        if ($request->filled('perusahaan')) {
            $query->where('perusahaan_pengaju', $request->perusahaan);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('no_kwitansi', 'like', "%{$search}%")
                    ->orWhere('perusahaan_pengaju', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    private function urutkan($query, Request $request)
    {
        $sortable = [
            'tanggal_pembayaran', 'tanggal_tukar', 'pt_tujuan',
            'perusahaan_pengaju', 'jumlah_rupiah', 'status',
        ];

        $sort = $request->get('sort', 'tanggal_pembayaran');
        $direction = $request->get('direction', 'asc');

        if (! in_array($sort, $sortable, true)) {
            $sort = 'tanggal_pembayaran';
        }
        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        return $query->orderBy($sort, $direction);
    }

    /** Ringkasan nominal atas query yang sama dengan tabelnya. */
    private function ringkasan(Request $request): array
    {
        $total = $this->filteredQuery($request)
            ->selectRaw('COUNT(*) as jumlah_dokumen, COALESCE(SUM(jumlah_rupiah), 0) as total_rupiah')
            ->first();

        $perPt = $this->filteredQuery($request)
            ->select('pt_tujuan')
            ->selectRaw('COUNT(*) as jumlah_dokumen, COALESCE(SUM(jumlah_rupiah), 0) as total_rupiah')
            ->groupBy('pt_tujuan')
            ->orderByDesc(DB::raw('SUM(jumlah_rupiah)'))
            ->get();

        $perTanggal = $this->filteredQuery($request)
            ->select('tanggal_pembayaran')
            ->selectRaw('COUNT(*) as jumlah_dokumen, COALESCE(SUM(jumlah_rupiah), 0) as total_rupiah')
            ->groupBy('tanggal_pembayaran')
            ->orderBy('tanggal_pembayaran')
            ->get();

        return [
            'jumlahDokumen' => (int) ($total->jumlah_dokumen ?? 0),
            'totalRupiah' => (float) ($total->total_rupiah ?? 0),
            'perPt' => $perPt,
            'perTanggal' => $perTanggal,
        ];
    }

    private function opsiFilter(): array
    {
        return [
            'ptTujuanOptions' => TukarFaktur::terverifikasi()
                ->distinct()->pluck('pt_tujuan')->filter()->sort()->values(),

            // Termasuk nama lama dari data sebelum master perusahaan ada.
            'perusahaanOptions' => Perusahaan::orderBy('nama')->pluck('nama')
                ->merge(TukarFaktur::terverifikasi()->distinct()->pluck('perusahaan_pengaju'))
                ->filter()->unique()->sort()->values(),

            'statusOptions' => collect(self::STATUS_TAMPIL)
                ->mapWithKeys(fn (TukarFakturStatus $s) => [$s->value => $s->label()])
                ->all(),
        ];
    }

    public function index(Request $request)
    {
        $this->authorize('viewBilling', TukarFaktur::class);

        $data = $this->urutkan($this->filteredQuery($request), $request)
            ->with(['perusahaan:id,nama,top', 'verifier:id,name', 'biller:id,name'])
            ->paginate(25)
            ->withQueryString();

        return view('billing.index', array_merge($this->opsiFilter(), [
            'data' => $data,
            'ringkasan' => $this->ringkasan($request),
            'jumlahSiapBilling' => TukarFaktur::siapBilling()->count(),
        ]));
    }

    /**
     * Kunci yang menyatukan satu baris rekap dengan dokumen penyusunnya.
     *
     * Tanggal dinormalkan ke Y-m-d karena sisi agregat dan sisi dokumen bisa
     * mengembalikan bentuk yang berbeda (date vs datetime) tergantung driver.
     */
    private static function kunciRekap(?string $ptTujuan, $tanggal): string
    {
        return ($ptTujuan ?? '') . '|' . ($tanggal ? \Carbon\Carbon::parse($tanggal)->toDateString() : '');
    }

    /** Rekap jadwal bayar: per PT tujuan, lalu per tanggal bayar. */
    public function rekap(Request $request)
    {
        $this->authorize('viewBilling', TukarFaktur::class);

        $baris = $this->filteredQuery($request)
            ->select('pt_tujuan', 'tanggal_pembayaran')
            ->selectRaw('COUNT(*) as jumlah_dokumen, COALESCE(SUM(jumlah_rupiah), 0) as total_rupiah')
            ->groupBy('pt_tujuan', 'tanggal_pembayaran')
            ->orderBy('pt_tujuan')
            ->orderBy('tanggal_pembayaran')
            ->get()
            // Dilepas dari model supaya baris agregat tidak disangka record utuh
            // dan supaya kunci penghubung ke dokumen ikut terbawa.
            ->map(fn ($row) => (object) [
                'pt_tujuan' => $row->pt_tujuan,
                'tanggal_pembayaran' => $row->tanggal_pembayaran,
                'jumlah_dokumen' => (int) $row->jumlah_dokumen,
                'total_rupiah' => (float) $row->total_rupiah,
                'kunci' => self::kunciRekap($row->pt_tujuan, $row->tanggal_pembayaran),
            ])
            ->groupBy('pt_tujuan');

        // Dokumen mentah penyusun tiap baris, untuk panel expand di rekap.
        // Kolomnya dibatasi karena seluruh hasil filter dimuat sekaligus.
        $dokumen = $this->filteredQuery($request)
            ->select([
                'id', 'pt_tujuan', 'tanggal_pembayaran', 'tanggal_tukar',
                'perusahaan_id', 'perusahaan_pengaju', 'no_kwitansi',
                'jumlah_rupiah', 'status',
            ])
            ->with('perusahaan:id,nama,top')
            ->orderBy('perusahaan_pengaju')
            ->orderBy('no_kwitansi')
            ->get()
            ->groupBy(fn ($row) => self::kunciRekap($row->pt_tujuan, $row->tanggal_pembayaran));

        return view('billing.rekap', array_merge($this->opsiFilter(), [
            'rekap' => $baris,
            'dokumen' => $dokumen,
            'ringkasan' => $this->ringkasan($request),
        ]));
    }

    public function proses(Request $request, string $id)
    {
        $data = TukarFaktur::findOrFail($id);

        $this->authorize('processBilling', $data);

        if (! $data->status->canTransitionTo(TukarFakturStatus::Billing)) {
            return back()->with(
                'error',
                'Data berstatus ' . $data->status->label() . ' tidak bisa diproses billing. '
                . 'Hanya data yang sudah terverifikasi yang bisa diproses.'
            );
        }

        $data->tandaiMasukBilling($request->user());

        return back()->with('success', 'Kwitansi ' . $data->no_kwitansi . ' masuk proses billing.');
    }

    public function prosesMassal(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['string'],
        ]);

        // Hanya yang benar-benar siap yang diproses; sisanya dilewati supaya
        // satu baris nyasar tidak membatalkan seluruh batch.
        $kandidat = TukarFaktur::whereIn('id', $validated['ids'])
            ->siapBilling()
            ->get();

        foreach ($kandidat as $item) {
            $this->authorize('processBilling', $item);

            $item->tandaiMasukBilling($request->user());
        }

        $diproses = $kandidat->count();
        $dilewati = count($validated['ids']) - $diproses;

        $pesan = $diproses . ' data masuk proses billing.';

        if ($dilewati > 0) {
            $pesan .= ' ' . $dilewati . ' data dilewati karena statusnya sudah berubah.';
        }

        return back()->with($diproses > 0 ? 'success' : 'error', $pesan);
    }

    /** Export CSV mengikuti filter aktif. */
    public function exportCsv(Request $request): StreamedResponse
    {
        $this->authorize('viewBilling', TukarFaktur::class);

        $query = $this->urutkan($this->filteredQuery($request), $request)
            ->with(['perusahaan:id,nama,top', 'verifier:id,name', 'biller:id,name'])
            ->orderBy('id'); // tie-breaker supaya chunk() konsisten

        $fileName = 'billing-' . now()->format('Ymd-His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Cache-Control' => 'no-store, no-cache',
        ];

        $columns = [
            'Tanggal Bayar',
            'PT Tujuan',
            'Supplier',
            'TOP (hari)',
            'No Kwitansi',
            'Jumlah Rupiah',
            'Tanggal Tukar',
            'Status',
            'Diverifikasi Oleh',
            'Tanggal Verifikasi',
            'Diproses Billing Oleh',
            'Tanggal Proses Billing',
        ];

        return response()->stream(function () use ($query, $columns) {
            $out = fopen('php://output', 'w');

            // BOM + hint separator supaya Excel membaca UTF-8 dan memisah
            // kolom dengan ";" tanpa tergantung regional setting Windows.
            fwrite($out, "\xEF\xBB\xBF");
            fwrite($out, "sep=;\n");

            fputcsv($out, $columns, ';');

            $query->chunk(500, function ($rows) use ($out) {
                foreach ($rows as $row) {
                    fputcsv($out, [
                        $row->tanggal_pembayaran
                            ? \Carbon\Carbon::parse($row->tanggal_pembayaran)->format('d/m/Y')
                            : '',
                        $row->pt_tujuan,
                        $row->perusahaan_pengaju,
                        optional($row->perusahaan)->top ?? '',
                        $row->no_kwitansi,
                        number_format((float) $row->jumlah_rupiah, 2, ',', ''),
                        $row->tanggal_tukar
                            ? \Carbon\Carbon::parse($row->tanggal_tukar)->format('d/m/Y')
                            : '',
                        $row->status?->label() ?? '',
                        optional($row->verifier)->name ?? '',
                        optional($row->verified_at)->format('d/m/Y H:i') ?? '',
                        optional($row->biller)->name ?? '',
                        optional($row->billed_at)->format('d/m/Y H:i') ?? '',
                    ], ';');
                }

                flush();
            });

            fclose($out);
        }, 200, $headers);
    }

    /** Export PDF rekap, mengikuti filter aktif. */
    public function exportPdf(Request $request)
    {
        $this->authorize('viewBilling', TukarFaktur::class);

        $data = $this->urutkan($this->filteredQuery($request), $request)
            ->with('perusahaan:id,nama,top')
            ->get();

        $pdf = Pdf::loadView('pdf.billing', [
            'data' => $data,
            'ringkasan' => $this->ringkasan($request),
            'filter' => [
                'start_bayar' => $request->get('start_bayar'),
                'end_bayar' => $request->get('end_bayar'),
                'pt_tujuan' => $request->get('pt_tujuan'),
                'perusahaan' => $request->get('perusahaan'),
            ],
        ])->setPaper('a4', 'landscape');

        return $pdf->download('billing-' . now()->format('Ymd-His') . '.pdf');
    }
}
