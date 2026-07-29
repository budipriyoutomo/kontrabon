<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Tukar Faktur Maharasa</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Optional icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(180deg, #f8f9fa, #eef1f4);
        }
        .card {
            border: none;
            border-radius: 1rem;
        }
        .form-control, .form-select {
            border-radius: .75rem;
            padding: .75rem 1rem;
        }
        .btn-primary {
            border-radius: .75rem;
            padding: .75rem;
            font-weight: 600;
        }
        label {
            font-weight: 500;
        }
        .is-invalid {
            border: 1px solid #dc3545 !important;
        }

    </style>
</head>

<body>

<div class="container py-4 py-md-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">

            <div class="card shadow-sm">
                <div class="card-body p-4 p-md-5">

                    <!-- Header -->
                    <div class="text-center mb-4">
                        <h4 class="fw-bold mb-1">Tukar Faktur Online</h4>
                        <p class="text-muted small mb-0">
                            Maharasa Group
                        </p>
                    </div>

                    <!-- Info -->
                    <div class="alert alert-light border small mb-4">
                        <i class="bi bi-info-circle me-1"></i>
                        Pastikan nama supplier di tulis dengan huruf kapital sesuai dengan nama yang terdaftar dan email sudah diisi dengan benar.
                        Submit total kontrabon tiap PT hanya 1x, contoh : penagihan ke PT Panca Abadi Nan Jaya : 1x submit, penagihan ke PT Maharasa Jaya Abadi 1x submit
                    </div>

                    @if($errors->any())
                        <div class="alert alert-danger small mb-4">
                            <div class="fw-semibold mb-1">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                Pengajuan belum tersimpan
                            </div>
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="/kontrabon" novalidate id="formKontrabon">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Penukaran Faktur ke PT</label>
                            <select name="pt_tujuan"
                                    class="form-select @error('pt_tujuan') is-invalid @enderror"
                                    required>
                                <option value="">Pilih PT Tujuan</option>
                                @foreach($ptTujuan as $pt)
                                    <option value="{{ $pt }}" @selected(old('pt_tujuan') === $pt)>{{ $pt }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nama Perusahaan Pengaju</label>

                            <input type="text"
                                   name="perusahaan_pengaju"
                                   class="form-control @error('perusahaan_pengaju') is-invalid @enderror"
                                   placeholder="Contoh: PT Vendor Jaya"
                                   value="{{ old('perusahaan_pengaju') }}"
                                   autocomplete="off"
                                   required>

                            <div class="form-text small">
                                Tulis sama persis seperti nama perusahaan yang terdaftar di Maharasa.
                            </div>

                            @error('perusahaan_pengaju')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label">Tanggal Tukar Faktur</label>
                                <input type="date"
                                        name="tanggal_tukar"
                                        class="form-control @error('tanggal_tukar') is-invalid @enderror"
                                        value="{{ old('tanggal_tukar', date('Y-m-d')) }}"
                                        min="{{ date('Y-m-d') }}"
                                        required>

                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">No Kwitansi</label>
                                <input type="text"
                                       name="no_kwitansi"
                                       class="form-control text-uppercase @error('no_kwitansi') is-invalid @enderror"
                                       placeholder="KW-00123"
                                       value="{{ old('no_kwitansi') }}"
                                       required>
                            </div>
                        </div>

                        <div class="mb-3 mt-3">
                            <label class="form-label">Jumlah Rupiah</label>
                            <input type="number"
                                   name="jumlah_rupiah"
                                   class="form-control @error('jumlah_rupiah') is-invalid @enderror"
                                   placeholder="Contoh: 12500000"
                                   value="{{ old('jumlah_rupiah') }}"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nama PIC</label>
                            <input type="text"
                                   name="nama_pic"
                                   class="form-control @error('nama_pic') is-invalid @enderror"
                                   placeholder="Nama lengkap"
                                   value="{{ old('nama_pic') }}"
                                   required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Email Penerima Faktur</label>
                            <input type="email"
                                   name="email_penerima"
                                   class="form-control @error('email_penerima') is-invalid @enderror"
                                   placeholder="finance@perusahaan.com"
                                   value="{{ old('email_penerima') }}"
                                   required>
                        </div>

                        <button class="btn btn-primary w-100">
                            <i class="bi bi-send me-1"></i>
                            Kirim Tukar Faktur
                        </button>
                    </form>

                </div>
            </div>

            <!-- Footer -->
            <p class="text-center text-muted small mt-4">
                © {{ date('Y') }} Maharasa Group
            </p>

        </div>
    </div>
</div>

</body>
<script>
document.addEventListener('DOMContentLoaded', function () {

    const form = document.getElementById('formKontrabon');
    const emailInput = document.querySelector('input[name="email_penerima"]');
    const tanggalInput = document.querySelector('input[name="tanggal_tukar"]');

    // ==============================
    // NO KWITANSI: selalu huruf kapital
    // ==============================
    const kwitansiInput = document.querySelector('input[name="no_kwitansi"]');

    kwitansiInput.addEventListener('input', function () {
        const pos = this.selectionStart;
        this.value = this.value.toUpperCase();
        this.setSelectionRange(pos, pos);
    });

    // ==============================
    // EMAIL MASKING (lowercase + trim)
    // ==============================
    emailInput.addEventListener('input', function () {
        this.value = this.value.toLowerCase().replace(/\s/g, '');
    });

    // ==============================
    // VALIDASI FORM
    // ==============================
    form.addEventListener('submit', function (event) {

        let isValid = true;

        // Validasi kosong
        form.querySelectorAll('input, select').forEach(function (field) {
            if (field.disabled || field.type === 'hidden') {
                return;
            }
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;
            } else {
                field.classList.remove('is-invalid');
            }
        });

        // Validasi tanggal tidak boleh mundur
        const today = new Date().toISOString().split('T')[0];
        if (tanggalInput.value < today) {
            tanggalInput.classList.add('is-invalid');
            alert('Tanggal tukar faktur tidak boleh sebelum hari ini.');
            isValid = false;
        }

        // Validasi format email regex
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(emailInput.value)) {
            emailInput.classList.add('is-invalid');
            alert('Format email tidak valid.');
            isValid = false;
        }

        if (!isValid) {
            event.preventDefault();
            event.stopPropagation();
        }

    });

});
</script>

</html>
