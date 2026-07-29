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

        $perusahaanList = Perusahaan::active()->orderBy('nama')->get();

        return view('tukarfaktur.create', compact('ptTujuan', 'perusahaanList'));
    }

    public function store(TukarFakturStoreRequest $request)
    {
        // Normalisasi (uppercase no kwitansi, nama perusahaan dari master)
        // sudah dilakukan di TukarFakturStoreRequest::prepareForValidation().
        TukarFaktur::create($request->validated());

        return redirect('/kontrabon/success');
    }

    public function success()
    {
        return view('tukarfaktur.success');
    }
}
