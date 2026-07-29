<?php

namespace App\Http\Controllers;

use App\Http\Requests\TukarFakturStoreRequest;
use App\Models\Perusahaan;
use App\Models\TukarFaktur;

class TukarFakturController extends Controller
{
    public function create()
    {
        $ptTujuan = [
            'PT Panca Abadi Nan Jaya (Sushi Tei & Tom Sushi)',
            'PT Maharasa Jaya Abadi (Pepper Lunch)',
            'PT Loka Abadi Nanjaya (Waruna)',
            'PT Sukha Abadi Nanjaya (Song Fa)',
        ];

        return view('tukarfaktur.create', compact('ptTujuan'));
    }

    public function store(TukarFakturStoreRequest $request)
    {
        $data = $request->validated();

        // Nama sudah dipastikan cocok persis dengan master oleh form request,
        // tinggal sambungkan relasinya.
        $data['perusahaan_id'] = Perusahaan::whereRaw('LOWER(nama) = ?', [mb_strtolower($data['perusahaan_pengaju'])])
            ->get()
            ->first(fn ($p) => $p->nama === $data['perusahaan_pengaju'])
            ?->id;

        TukarFaktur::create($data);

        return redirect('/kontrabon/success');
    }

    public function success()
    {
        return view('tukarfaktur.success');
    }
}
