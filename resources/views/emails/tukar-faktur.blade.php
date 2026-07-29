<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: Arial, Helvetica, sans-serif; font-size: 14px; color: #333;">

<p>
    Yth. Bapak/Ibu <strong>{{ $data->perusahaan_pengaju }}</strong>,
</p>

<p>
    Bersama email ini kami sampaikan bahwa pengajuan
    <strong>Tukar Faktur Online</strong> telah diproses oleh tim finance.
</p>

<hr style="border:none;border-top:1px solid #ddd;margin:16px 0;">

<table cellpadding="6" cellspacing="0" width="100%" style="border-collapse: collapse;">
    <tr>
        <td width="35%" style="color:#666;">Tanggal Pengajuan Faktur</td>
        <td width="5%">:</td>
        <td>
            {{ \Carbon\Carbon::parse($data->created_at)->format('n/j/Y H:i:s') }}
        </td>
    </tr>

    <tr>
        <td style="color:#666;">Pengajuan Faktur Online ke</td>
        <td>:</td>
        <td>
            {{ $data->pt_tujuan }}
        </td>
    </tr>

    <tr>
        <td style="color:#666;">Faktur Online dari</td>
        <td>:</td>
        <td>
            {{ $data->perusahaan_pengaju }}
        </td>
    </tr>

    <tr>
        <td style="color:#666;">Total Tagihan</td>
        <td>:</td>
        <td>
            <strong>
                Rp {{ number_format($data->jumlah_rupiah, 2, ',', '.') }}
            </strong>
        </td>
    </tr>

    <tr>
        <td style="color:#666;">PIC Pengajuan</td>
        <td>:</td>
        <td>
            {{ $data->email_penerima }}
        </td>
    </tr>

    <tr>
        <td style="color:#666;">Tanggal Pembayaran</td>
        <td>:</td>
        <td>
            <strong>
                {{ \Carbon\Carbon::parse($data->tanggal_pembayaran)->format('d M Y') }}
            </strong>
        </td>
    </tr>
</table>

<hr style="border:none;border-top:1px solid #ddd;margin:16px 0;">

<p>
    Dokumen PDF Tukar Faktur Online terlampir pada email ini
    sebagai bukti pengajuan yang telah diproses.
</p>

<p>
    Apabila terdapat pertanyaan lebih lanjut, silakan menghubungi
    tim finance kami.
</p>

<p>
    Terima kasih atas kerja samanya.
</p>

<p style="margin-top:24px;">
    Hormat kami,<br>
    <strong>Maharasa Group</strong>
</p>

</body>
</html>
