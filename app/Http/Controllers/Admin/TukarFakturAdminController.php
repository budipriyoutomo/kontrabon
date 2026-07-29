<?php 

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Perusahaan;
use App\Models\TukarFaktur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use App\Mail\TukarFakturMail;
use Symfony\Component\HttpFoundation\StreamedResponse;


class TukarFakturAdminController extends Controller
{
    /*
    public function index()
    {
        $data = TukarFaktur::latest()->paginate(10);
        return view('admin.tukarfaktur.index', compact('data'));
    }*/

    /**
     * Query terfilter untuk tabel index & export.
     * Dipakai bersama supaya isi file export selalu sama persis
     * dengan yang tampil di layar.
     */
    private function filteredQuery(Request $request)
    {
        $query = TukarFaktur::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('no_kwitansi', 'like', "%{$search}%") ;
            });
        }
        // Tanggal Tukar Range Filter
        if ($request->filled('start_date')) {
            $query->whereDate('tanggal_tukar', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('tanggal_tukar', '<=', $request->end_date);
        }

        //Tanggal Bayar Range Filter
        if ($request->filled('start_bayar')) {
            $query->whereDate('tanggal_pembayaran', '>=', $request->start_bayar);
        }

        if ($request->filled('end_bayar')) {
            $query->whereDate('tanggal_pembayaran', '<=', $request->end_bayar);
        }


        // PT Tujuan Filter
        if ($request->filled('pt_tujuan')) {
            $query->where('pt_tujuan', $request->pt_tujuan);
        }

        // Status Filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Perusahaan Filter
        if ($request->filled('perusahaan')) {
            $query->where('perusahaan_pengaju', $request->perusahaan);
        }

        // Sorting
        $sortable = [
            'tanggal_tukar', 'tanggal_pembayaran', 'pt_tujuan',
            'perusahaan_pengaju', 'no_kwitansi', 'jumlah_rupiah', 'status',
        ];

        $sort = $request->get('sort', 'tanggal_tukar');
        $direction = $request->get('direction', 'desc');

        if (! in_array($sort, $sortable)) {
            $sort = 'tanggal_tukar';
        }
        if (! in_array($direction, ['asc', 'desc'])) {
            $direction = 'desc';
        }

        return $query->orderBy($sort, $direction);
    }

    public function index(Request $request)
    {
        $query = $this->filteredQuery($request);

        // Get options for dropdown filters
        $ptTujuanOptions = TukarFaktur::distinct()->pluck('pt_tujuan');

        // Master data + nama lama yang belum terdaftar di master.
        $perusahaanOptions = Perusahaan::orderBy('nama')->pluck('nama')
            ->merge(TukarFaktur::distinct()->pluck('perusahaan_pengaju'))
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $data = $query->paginate(20)->withQueryString();

        return view('admin.tukarfaktur.index', compact('data', 'ptTujuanOptions', 'perusahaanOptions'));
    }

    /**
     * Export data terfilter ke CSV (UTF-8 BOM) yang siap dibuka di Excel.
     */
    public function export(Request $request): StreamedResponse
    {
        $query = $this->filteredQuery($request)
            ->with('perusahaan:id,nama,top')
            ->orderBy('id'); // tie-breaker supaya chunk() konsisten

        $fileName = 'tukar-faktur-' . now()->format('Ymd-His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Cache-Control'       => 'no-store, no-cache',
        ];

        $columns = [
            'Tanggal Tukar',
            'PT Tujuan',
            'Perusahaan Pengaju',
            'TOP (hari)',
            'No Kwitansi',
            'Jumlah Rupiah',
            'Nama PIC',
            'Email Penerima',
            'Status',
            'Tanggal Pembayaran',
            'Tanggal Input',
        ];

        return response()->stream(function () use ($query, $columns) {
            $out = fopen('php://output', 'w');

            // BOM + hint separator: bikin Excel baca UTF-8 dan pisah kolom
            // dengan ";" tanpa tergantung regional setting Windows.
            fwrite($out, "\xEF\xBB\xBF");
            fwrite($out, "sep=;\n");

            fputcsv($out, $columns, ';');

            $query->chunk(500, function ($rows) use ($out) {
                foreach ($rows as $row) {
                    fputcsv($out, [
                        $row->tanggal_tukar
                            ? \Carbon\Carbon::parse($row->tanggal_tukar)->format('d/m/Y')
                            : '',
                        $row->pt_tujuan,
                        $row->perusahaan_pengaju,
                        optional($row->perusahaan)->top ?? '',
                        $row->no_kwitansi,
                        // Format angka lokal ID tanpa pemisah ribuan
                        // supaya tetap terbaca sebagai angka di Excel.
                        number_format((float) $row->jumlah_rupiah, 2, ',', ''),
                        $row->nama_pic,
                        $row->email_penerima,
                        $row->status,
                        $row->tanggal_pembayaran
                            ? \Carbon\Carbon::parse($row->tanggal_pembayaran)->format('d/m/Y')
                            : '',
                        optional($row->created_at)->format('d/m/Y H:i'),
                    ], ';');
                }

                flush();
            });

            fclose($out);
        }, 200, $headers);
    }

    public function show($id)
    {
        $data = TukarFaktur::findOrFail($id);
        return view('admin.tukarfaktur.show', compact('data'));
    }

    public function updatePaymentDate(Request $request, string $id)
    {
        $request->validate([
            'tanggal_pembayaran' => ['required', 'date'],
        ]);

        $data = TukarFaktur::findOrFail($id);

        // 🚫 Cegah double kirim
        if ($data->status === 'email_sent') {
            return back()->with('info', 'Email pembayaran sudah pernah dikirim.');
        }

        // 1️⃣ Update tanggal pembayaran
        $data->update([
            'tanggal_pembayaran' => $request->tanggal_pembayaran,
        ]);

        $pdf = PDF::loadView('pdf.tukar-faktur', ['data' => $data]);

        $fileName = sprintf(
            'tukar-faktur-%s-%s.pdf',
            $data->no_kwitansi,
            now()->timestamp
        );

        $filePath = 'tukar-faktur/' . $fileName;

        Storage::put($filePath, $pdf->output());

        clearstatcache();
        usleep(300000);

        if (!Storage::exists($filePath)) {
            return back()->withErrors('Gagal membuat file PDF.');
        }

        // 4️⃣ Queue email (ASYNC)
        Mail::to($data->email_penerima)
            ->queue(new TukarFakturMail(
                (string) $data->id, // UUID
                $filePath
            ));

        // ⚠️ Jangan klaim email terkirim
        return back()->with(
            'success',
            'Tanggal pembayaran disimpan. Email sedang diproses untuk dikirim.'
        );
    }

    public function destroy($id)
    {
        $data = TukarFaktur::findOrFail($id);

        // Optional: proteksi data tertentu
        if ($data->status === 'email_sent') {
            return back()->with('error', 'Data yang sudah dikirim email tidak dapat dihapus.');
        }

        $data->delete();

        return redirect()
            ->route('admin.tukar-faktur.index')
            ->with('success', 'Data tukar faktur berhasil dihapus.');
    }

    public function update(Request $request, $id)
    {
        $data = TukarFaktur::findOrFail($id);
         
        $validated = $request->validate([
            'jumlah_rupiah' => 'required|numeric|min:0', 
            'pt_tujuan' => 'required|string|max:255',
            'perusahaan_pengaju' => 'required|string|max:255',
            'tanggal_tukar' => 'required|date',
            'no_kwitansi' => 'required|string|max:255',
            'nama_pic' => 'required|string|max:255',
            'email_penerima' => 'required|email',
            'tanggal_pembayaran' => 'nullable|date',
        ]);
 
        $data->update($validated);

        return redirect()
            ->route('admin.tukar-faktur.show', $id)
            ->with('success', 'Data berhasil diperbarui');
    }

}
