<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PerusahaanRequest;
use App\Models\Perusahaan;
use Illuminate\Http\Request;

class PerusahaanController extends Controller
{
    public function index(Request $request)
    {
        $query = Perusahaan::query()->withCount('tukarFakturs');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('kode', 'like', "%{$search}%")
                    ->orWhere('npwp', 'like', "%{$search}%")
                    ->orWhere('nama_pic', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'aktif');
        }

        $sort = $request->get('sort', 'nama');
        $direction = $request->get('direction', 'asc');

        if (! in_array($sort, ['nama', 'kode', 'is_active', 'created_at'])) {
            $sort = 'nama';
        }
        if (! in_array($direction, ['asc', 'desc'])) {
            $direction = 'asc';
        }

        $query->orderBy($sort, $direction);

        $data = $query->paginate(20)->withQueryString();

        return view('admin.perusahaan.index', compact('data'));
    }

    public function create()
    {
        return view('admin.perusahaan.create');
    }

    public function store(PerusahaanRequest $request)
    {
        Perusahaan::create($request->validated());

        return redirect()
            ->route('admin.perusahaan.index')
            ->with('success', 'Perusahaan berhasil ditambahkan.');
    }

    public function show($id)
    {
        $perusahaan = Perusahaan::withCount('tukarFakturs')->findOrFail($id);

        $tukarFakturs = $perusahaan->tukarFakturs()
            ->latest('tanggal_tukar')
            ->paginate(10);

        return view('admin.perusahaan.show', compact('perusahaan', 'tukarFakturs'));
    }

    public function edit($id)
    {
        $perusahaan = Perusahaan::findOrFail($id);

        return view('admin.perusahaan.edit', compact('perusahaan'));
    }

    public function update(PerusahaanRequest $request, $id)
    {
        $perusahaan = Perusahaan::findOrFail($id);

        $perusahaan->update($request->validated());

        return redirect()
            ->route('admin.perusahaan.index')
            ->with('success', 'Perusahaan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $perusahaan = Perusahaan::withCount('tukarFakturs')->findOrFail($id);

        if ($perusahaan->tukar_fakturs_count > 0) {
            return back()->with(
                'error',
                'Perusahaan tidak dapat dihapus karena sudah dipakai pada '
                . $perusahaan->tukar_fakturs_count . ' data tukar faktur. '
                . 'Nonaktifkan saja perusahaan ini.'
            );
        }

        $perusahaan->delete();

        return redirect()
            ->route('admin.perusahaan.index')
            ->with('success', 'Perusahaan berhasil dihapus.');
    }
}
