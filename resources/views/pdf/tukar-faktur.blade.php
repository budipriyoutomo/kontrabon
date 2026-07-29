<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
    @page {
        size: A5 landscape;
        margin: 15mm 12mm;
    }

    body {
        font-family: DejaVu Sans;
        font-size: 12px;
        position: relative;
    }

    .watermark {
        position: fixed;
        top: 50%;
        left: 50%;
        width: 60%;
        max-width: 320px;
        opacity: 0.06;
        transform: translate(-50%, -50%);
        z-index: -1;
        pointer-events: none;
    }

    .header {
        width: 100%;
        margin-bottom: 14px;
    }

    .company-info {
        font-size: 10px;
        line-height: 1.35;
    }

    .title {
        text-align: center;
        font-size: 15px;
        font-weight: bold;
        margin: 6px 0 12px 0;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 8px;
    }

    th, td {
        border: 1px solid #000;
        padding: 6px;
        font-size: 11.5px;
    }

    .no-border td {
        border: none;
        padding: 3px 0;
    }
</style>


</head>
<body>

    <!-- WATERMARK LOGO -->
    <img
        src="{{ public_path('images/logo-maharasa.png') }}"
        class="watermark"
        alt="Watermark Logo">

    <!-- HEADER -->
    <div class="header">
        <div class="company-info">
            <strong>{{ $data->pt_tujuan }}</strong><br>
            Bizpark Blok B2 No 16–18<br>
            Jl. Kopo 455 D Bandung<br>
            P : 022 5441 0675<br>
            F : 022 5441 0675
        </div>
    </div>

    <!-- TITLE -->
    <div class="title">
        TUKAR FAKTUR ONLINE
    </div>

    <!-- INFO PENERIMA -->
    <table class="no-border">
        <tr>
            <td width="30%">
                <strong>Telah Terima dari :</strong>
                {{ $data->perusahaan_pengaju }}
            </td>
        </tr>
    </table>

    <!-- TABEL FAKTUR -->
    <table>
        <thead>
            <tr>
                <th>Tanggal Kwitansi</th>
                <th>No Kwitansi</th>
                <th>Jumlah (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="text-align:center">
                    {{ \Carbon\Carbon::parse($data->tanggal_tukar)->format('d/m/Y') }}
                </td>
                <td style="text-align:center">
                    {{ $data->no_kwitansi }}
                </td>
                <td style="text-align:right">
                    {{ number_format($data->jumlah_rupiah, 2, ',', '.') }}
                </td>
            </tr>
        </tbody>
    </table>

    <!-- TANGGAL BAYAR -->
    <p style="margin-top:15px;">
        <strong>Tanggal Pembayaran :</strong>
        {{ \Carbon\Carbon::parse($data->tanggal_pembayaran)->format('d/m/Y') }}
    </p>

    <!-- NOTES -->
    <p><strong>Notes :</strong></p>
    <ul>
        <li>Tukar Faktur Online ini otomatis terkirim via email.</li>
        <li>Tanggal pembayaran dapat berubah sesuai kondisi perusahaan.</li>
    </ul>

    <p style="margin-top:20px;">
        <em>*Dokumen ini valid sebagai bukti tukar faktur.</em>
    </p>

</body>
</html>
