<?php

namespace App\Http\Controllers;

use App\Models\Perusahaan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Sumber data dropdown pencarian supplier (Tom Select).
 *
 * Endpoint ini dipakai form publik /kontrabon yang TIDAK login, jadi
 * kolom kontak (nama PIC, email) hanya dikirim untuk pengguna yang sudah
 * terautentikasi. Tanpa pembatasan itu, siapa pun bisa memanen seluruh
 * kontak supplier hanya dengan menelusuri abjad.
 */
class PerusahaanSearchController extends Controller
{
    private const BATAS_HASIL = 20;

    public function __invoke(Request $request): JsonResponse
    {
        $q = trim((string) $request->get('q', ''));

        // Kueri terlalu pendek = mendekati "ambil semua". Tolak lebih awal.
        if (mb_strlen($q) < 2) {
            return response()->json(['data' => []]);
        }

        $perusahaan = Perusahaan::active()
            ->where('nama', 'like', $q . '%')
            ->orderBy('nama')
            ->limit(self::BATAS_HASIL)
            ->get();

        // Jika awalan tidak menghasilkan apa-apa, baru cari di tengah nama.
        if ($perusahaan->isEmpty()) {
            $perusahaan = Perusahaan::active()
                ->where('nama', 'like', '%' . $q . '%')
                ->orderBy('nama')
                ->limit(self::BATAS_HASIL)
                ->get();
        }

        $terautentikasi = $request->user() !== null;

        return response()->json([
            'data' => $perusahaan->map(function (Perusahaan $p) use ($terautentikasi) {
                $baris = [
                    'id' => $p->id,
                    'nama' => $p->nama,
                    'top' => $p->top,
                ];

                if ($terautentikasi) {
                    $baris['nama_pic'] = $p->nama_pic;
                    $baris['email'] = $p->email;
                }

                return $baris;
            })->values(),
        ]);
    }
}
