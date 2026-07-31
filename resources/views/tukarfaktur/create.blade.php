<x-public-layout title="Tukar Faktur">
    <x-slot name="heading">Tukar Faktur Online</x-slot>

    <x-card class="shadow-sm">
        <x-card.content class="space-y-5 pt-6">

            <x-alert icon="info">
                <x-alert.description>
                    <ul class="list-disc space-y-1 pl-4 text-sm text-muted-foreground">
                        <li>
                            Pastikan nama supplier ditulis dengan huruf kapital sesuai nama yang terdaftar
                            dan email sudah diisi dengan benar.
                        </li>
                        <li>
                            Submit total kontrabon tiap PT hanya <strong class="text-foreground">1 kali</strong>.
                            Contoh: penagihan ke <strong class="text-foreground">PT PANCA ABADI NAN JAYA</strong>
                            cukup 1x submit, dan penagihan ke
                            <strong class="text-foreground">PT MAHARASA JAYA ABADI</strong> juga cukup 1x submit.
                        </li>
                    </ul>
                </x-alert.description>
            </x-alert>

            @if ($errors->any())
                <x-alert variant="destructive" icon="triangle-alert" id="ringkasan-error" tabindex="-1">
                    <x-alert.title>
                        Pengajuan belum tersimpan — {{ $errors->count() }} hal perlu diperbaiki
                    </x-alert.title>
                    <x-alert.description>
                        <ul class="mt-1 list-disc space-y-1 pl-4">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </x-alert.description>
                </x-alert>
            @endif

            <form method="POST" action="/kontrabon" novalidate id="formKontrabon" class="space-y-4">
                @csrf

                <x-form-field label="Penukaran Faktur ke PT" name="pt_tujuan" required>
                    <x-select name="pt_tujuan" required>
                        <option value="">Pilih PT Tujuan</option>
                        @foreach ($ptTujuan as $pt)
                            <option value="{{ $pt }}" @selected(old('pt_tujuan') === $pt)>{{ $pt }}</option>
                        @endforeach
                    </x-select>
                </x-form-field>

                {{-- Nama diketik manual dan dicek harus sama persis dengan master.
                     Sengaja tanpa daftar saran: kecocokan sebagian huruf tidak
                     boleh diterima. Lihat TukarFakturStoreRequest. --}}
                <x-form-field
                    label="Nama Supplier"
                    name="perusahaan_pengaju"
                    required
                    hint="Tulis sama persis seperti nama perusahaan yang terdaftar di Maharasa. Kalau supplier belum terdaftar, hubungi finance Maharasa."
                >
                    <x-input
                        type="text"
                        name="perusahaan_pengaju"
                        placeholder="Contoh: PT Vendor Jaya"
                        :value="old('perusahaan_pengaju')"
                        autocomplete="off"
                        :aria-invalid="$errors->has('perusahaan_pengaju') ? 'true' : null"
                        aria-describedby="saran-perusahaan"
                        required
                    />

                    {{-- Hanya muncul kalau bedanya semata huruf besar/kecil.
                         Nama yang tidak terdaftar tidak pernah disarankan.
                         Lihat TukarFakturStoreRequest::validasiNamaPerusahaan. --}}
                    @if (session('saran_perusahaan'))
                        <div
                            id="saran-perusahaan"
                            class="rounded-md border border-info/40 bg-info/10 p-3 text-sm"
                        >
                            <div class="flex items-start gap-2">
                                <x-icon name="lightbulb" class="mt-0.5 text-info" />

                                <div class="min-w-0 flex-1 space-y-2">
                                    <p class="text-muted-foreground">
                                        Penulisan yang terdaftar:
                                        <strong class="break-words font-semibold text-foreground">
                                            {{ session('saran_perusahaan') }}
                                        </strong>
                                    </p>

                                    <x-button
                                        type="button"
                                        variant="outline"
                                        size="sm"
                                        id="pakai-saran"
                                        data-saran="{{ session('saran_perusahaan') }}"
                                    >
                                        <x-icon name="wand-sparkles" />
                                        Pakai penulisan ini
                                    </x-button>
                                </div>
                            </div>
                        </div>
                    @endif
                </x-form-field>

                <div class="grid gap-4 sm:grid-cols-2">
                    <x-form-field label="Tanggal Tukar Faktur" name="tanggal_tukar" required>
                        <x-input
                            type="date"
                            name="tanggal_tukar"
                            :value="old('tanggal_tukar', date('Y-m-d'))"
                            min="{{ date('Y-m-d') }}"
                            required
                        />
                    </x-form-field>

                    <x-form-field label="No Kwitansi" name="no_kwitansi" required>
                        <x-input
                            type="text"
                            name="no_kwitansi"
                            class="uppercase"
                            placeholder="KW-00123"
                            :value="old('no_kwitansi')"
                            required
                        />
                    </x-form-field>
                </div>

                <x-form-field label="Jumlah Rupiah" name="jumlah_rupiah" required>
                    <x-input
                        type="number"
                        name="jumlah_rupiah"
                        placeholder="Contoh: 12500000"
                        :value="old('jumlah_rupiah')"
                        required
                    />
                </x-form-field>

                <x-form-field label="Nama PIC" name="nama_pic" required>
                    <x-input type="text" name="nama_pic" placeholder="Nama lengkap" :value="old('nama_pic')" required />
                </x-form-field>

                <x-form-field label="Email Penerima Faktur" name="email_penerima" required>
                    <x-input
                        type="email"
                        name="email_penerima"
                        placeholder="finance@perusahaan.com"
                        :value="old('email_penerima')"
                        required
                    />
                </x-form-field>

                <x-button type="submit" size="lg" class="w-full">
                    <x-icon name="send" />
                    Kirim Tukar Faktur
                </x-button>
            </form>

        </x-card.content>
    </x-card>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {

                const form = document.getElementById('formKontrabon');
                const emailInput = document.querySelector('input[name="email_penerima"]');
                const tanggalInput = document.querySelector('input[name="tanggal_tukar"]');
                const kwitansiInput = document.querySelector('input[name="no_kwitansi"]');
                const supplierInput = document.querySelector('input[name="perusahaan_pengaju"]');

                // Isi ulang nama supplier dengan penulisan resmi dari master.
                const tombolSaran = document.getElementById('pakai-saran');

                if (tombolSaran) {
                    tombolSaran.addEventListener('click', function () {
                        supplierInput.value = this.dataset.saran;
                        supplierInput.classList.remove('border-destructive', 'focus-visible:ring-destructive');
                        supplierInput.focus();
                    });
                }

                // Setelah gagal validasi, bawa perhatian ke ringkasan errornya
                // dulu — di ponsel panel itu bisa berada jauh di atas layar.
                const ringkasanError = document.getElementById('ringkasan-error');

                if (ringkasanError) {
                    ringkasanError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    ringkasanError.focus({ preventScroll: true });
                }

                // No kwitansi selalu huruf kapital.
                kwitansiInput.addEventListener('input', function () {
                    const pos = this.selectionStart;
                    this.value = this.value.toUpperCase();
                    this.setSelectionRange(pos, pos);
                });

                // Email selalu huruf kecil dan tanpa spasi.
                emailInput.addEventListener('input', function () {
                    this.value = this.value.toLowerCase().replace(/\s/g, '');
                });

                // Tandai field bermasalah dengan warna destructive dari design token.
                const invalidClasses = ['border-destructive', 'focus-visible:ring-destructive'];

                function tandai(field, bermasalah) {
                    field.classList.toggle(invalidClasses[0], bermasalah);
                    field.classList.toggle(invalidClasses[1], bermasalah);
                }

                form.addEventListener('submit', function (event) {
                    let isValid = true;

                    form.querySelectorAll('input, select').forEach(function (field) {
                        if (field.disabled || field.type === 'hidden') {
                            return;
                        }

                        const kosong = ! field.value.trim();
                        tandai(field, kosong);

                        if (kosong) {
                            isValid = false;
                        }
                    });

                    const today = new Date().toISOString().split('T')[0];

                    if (tanggalInput.value < today) {
                        tandai(tanggalInput, true);
                        alert('Tanggal tukar faktur tidak boleh sebelum hari ini.');
                        isValid = false;
                    }

                    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                    if (! emailPattern.test(emailInput.value)) {
                        tandai(emailInput, true);
                        alert('Format email tidak valid.');
                        isValid = false;
                    }

                    if (! isValid) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                });

            });
        </script>
    @endpush
</x-public-layout>
