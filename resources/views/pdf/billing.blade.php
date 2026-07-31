<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Rekap Billing</title>
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { font-size: 10px; color: #1e293b; }
        h1 { font-size: 15px; margin: 0 0 4px; }
        .meta { font-size: 9px; color: #64748b; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #cbd5e1; padding: 4px 6px; }
        th { background: #f1f5f9; text-align: left; font-size: 9px; }
        td.angka, th.angka { text-align: right; }
        td.tengah { text-align: center; }
        .ringkas { margin-bottom: 12px; }
        .ringkas td, .ringkas th { border: none; padding: 2px 0; }
        .total { font-size: 12px; font-weight: bold; }
        tfoot td { background: #f8fafc; font-weight: bold; }
    </style>
</head>
<body>

<h1>Rekap Billing — Maharasa Group</h1>

<div class="meta">
    Dicetak {{ now()->format('d/m/Y H:i') }} WIB.
    @if($filter['start_bayar'] || $filter['end_bayar'])
        Tanggal bayar
        {{ $filter['start_bayar'] ? \Carbon\Carbon::parse($filter['start_bayar'])->format('d/m/Y') : '…' }}
        s.d.
        {{ $filter['end_bayar'] ? \Carbon\Carbon::parse($filter['end_bayar'])->format('d/m/Y') : '…' }}.
    @endif
    @if($filter['pt_tujuan']) PT tujuan: {{ $filter['pt_tujuan'] }}. @endif
    @if($filter['perusahaan']) Supplier: {{ $filter['perusahaan'] }}. @endif
    <br>
    Hanya memuat pengajuan yang sudah terverifikasi.
</div>

<table class="ringkas">
    <tr>
        <td>Jumlah dokumen</td>
        <td class="angka">{{ $ringkasan['jumlahDokumen'] }}</td>
    </tr>
    <tr class="total">
        <td>Total tagihan</td>
        <td class="angka">Rp {{ number_format($ringkasan['totalRupiah'], 0, ',', '.') }}</td>
    </tr>
</table>

<table>
    <thead>
        <tr>
            <th>Tgl Bayar</th>
            <th>PT Tujuan</th>
            <th>Supplier</th>
            <th class="angka">TOP</th>
            <th>No Kwitansi</th>
            <th>Tgl Tukar</th>
            <th>Status</th>
            <th class="angka">Jumlah</th>
        </tr>
    </thead>

    <tbody>
        @forelse($data as $row)
            <tr>
                <td>{{ $row->tanggal_pembayaran ? \Carbon\Carbon::parse($row->tanggal_pembayaran)->format('d/m/Y') : '-' }}</td>
                <td>{{ $row->pt_tujuan }}</td>
                <td>{{ $row->perusahaan_pengaju }}</td>
                <td class="angka">{{ optional($row->perusahaan)->top ?? '-' }}</td>
                <td>{{ $row->no_kwitansi }}</td>
                <td>{{ $row->tanggal_tukar ? \Carbon\Carbon::parse($row->tanggal_tukar)->format('d/m/Y') : '-' }}</td>
                <td>{{ $row->status?->label() }}</td>
                <td class="angka">{{ number_format($row->jumlah_rupiah, 0, ',', '.') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="tengah">Tidak ada data</td>
            </tr>
        @endforelse
    </tbody>

    <tfoot>
        <tr>
            <td colspan="7">Total</td>
            <td class="angka">Rp {{ number_format($ringkasan['totalRupiah'], 0, ',', '.') }}</td>
        </tr>
    </tfoot>
</table>

</body>
</html>
