<x-app-layout>
    <x-slot name="title">Detail Tukar Faktur</x-slot>

    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
            ['label' => 'Tukar Faktur', 'url' => route('admin.tukar-faktur.index')],
            ['label' => $data->no_kwitansi],
        ]" />
    </x-slot>

    <div class="mx-auto max-w-4xl space-y-6">

        <x-card>
            <x-card.content class="flex flex-wrap items-center justify-between gap-6 pt-6">
                <div class="space-y-1">
                    <p class="text-sm text-muted-foreground">Jumlah</p>
                    <p class="text-2xl font-semibold tabular-nums tracking-tight">
                        Rp {{ number_format($data->jumlah_rupiah, 0, ',', '.') }}
                    </p>
                </div>

                <div class="space-y-1">
                    <p class="text-sm text-muted-foreground">Status</p>
                    <x-badge :status="$data->status" class="px-3 py-1 text-sm" />
                </div>
            </x-card.content>
        </x-card>

        <x-card>
            <x-card.header>
                <x-card.title>Verifikasi</x-card.title>
            </x-card.header>

            <x-card.content>
                @if ($data->verified_at)
                    <dl class="divide-y">
                        <x-detail-row label="Diverifikasi oleh">
                            {{ optional($data->verifier)->name ?? '-' }}
                        </x-detail-row>

                        <x-detail-row label="Tanggal verifikasi">
                            {{ $data->verified_at->format('d F Y H:i') }}
                        </x-detail-row>

                        @if ($data->verified_note)
                            <x-detail-row label="Catatan">{{ $data->verified_note }}</x-detail-row>
                        @endif
                    </dl>
                @elseif ($data->status === \App\Enums\TukarFakturStatus::EmailSent)
                    @can('verify', $data)
                        <form
                            method="POST"
                            action="{{ route('admin.verifikasi.verify', $data->id) }}"
                            class="flex flex-col gap-4 sm:flex-row sm:items-end"
                        >
                            @csrf

                            <x-form-field
                                label="Catatan verifikasi"
                                name="verified_note"
                                hint="Opsional."
                                class="flex-1"
                            >
                                <x-input type="text" name="verified_note" maxlength="255" />
                            </x-form-field>

                            <x-button type="submit" variant="success">
                                <x-icon name="badge-check" />
                                Verifikasi Data Ini
                            </x-button>
                        </form>
                    @else
                        <p class="text-sm text-muted-foreground">Menunggu diverifikasi oleh verifikator.</p>
                    @endcan
                @else
                    <p class="text-sm text-muted-foreground">
                        Data belum sampai tahap verifikasi. Verifikasi baru bisa dilakukan setelah
                        email bukti terkirim ke supplier.
                    </p>
                @endif
            </x-card.content>
        </x-card>

        <x-card>
            <x-card.header class="flex-row items-center justify-between space-y-0">
                <x-card.title>Informasi Faktur</x-card.title>

                @if ($data->status->isEditable() && auth()->user()->can('update', $data))
                    <x-button
                        type="button"
                        variant="outline"
                        size="sm"
                        x-on:click="$dispatch('open-modal', 'edit-tukar-faktur')"
                    >
                        <x-icon name="pencil" />
                        Edit Data
                    </x-button>
                @endif
            </x-card.header>

            <x-card.content>
                <dl class="divide-y">
                    <x-detail-row label="PT Tujuan">{{ $data->pt_tujuan }}</x-detail-row>
                    <x-detail-row label="Perusahaan Pengaju">{{ $data->perusahaan_pengaju }}</x-detail-row>
                    <x-detail-row label="Tanggal Tukar">
                        {{ \Carbon\Carbon::parse($data->tanggal_tukar)->format('d F Y') }}
                    </x-detail-row>
                    <x-detail-row label="No Kwitansi">{{ $data->no_kwitansi }}</x-detail-row>
                    <x-detail-row label="Jumlah Rupiah">
                        Rp {{ number_format($data->jumlah_rupiah, 0, ',', '.') }}
                    </x-detail-row>
                    <x-detail-row label="Nama PIC">{{ $data->nama_pic }}</x-detail-row>
                    <x-detail-row label="Email PIC">{{ $data->email_penerima }}</x-detail-row>
                </dl>
            </x-card.content>
        </x-card>

        <x-card>
            <x-card.header>
                <x-card.title>Informasi Pembayaran</x-card.title>
            </x-card.header>

            <x-card.content class="space-y-3">
                {{-- Mengisi tanggal bayar memicu email ke supplier, jadi hanya
                     boleh dilakukan kontrabon dan hanya selagi masih pending. --}}
                @if ($data->status === \App\Enums\TukarFakturStatus::Pending && auth()->user()->can('setPaymentDate', $data))
                    <form
                        method="POST"
                        action="{{ route('admin.tukar-faktur.payment-date', $data->id) }}"
                        class="flex flex-col gap-4 sm:flex-row sm:items-end"
                    >
                        @csrf

                        <x-form-field label="Tanggal Pembayaran" name="tanggal_pembayaran" required class="flex-1">
                            <x-input
                                type="date"
                                name="tanggal_pembayaran"
                                :value="$data->tanggal_pembayaran"
                                required
                            />
                        </x-form-field>

                        <x-button type="submit">Simpan Pembayaran</x-button>
                    </form>
                @else
                    <dl>
                        <x-detail-row label="Tanggal Pembayaran">
                            {{ $data->tanggal_pembayaran
                                ? \Carbon\Carbon::parse($data->tanggal_pembayaran)->format('d F Y')
                                : 'Belum diisi' }}
                        </x-detail-row>
                    </dl>
                @endif

                {{-- Kirim ulang untuk supplier yang kehilangan emailnya. Hanya
                     muncul setelah email pertama benar-benar terkirim, dan
                     tidak mengubah status apa pun. --}}
                @if ($data->status !== \App\Enums\TukarFakturStatus::Pending && auth()->user()->can('resendEmail', $data))
                    <div class="flex flex-col gap-2 border-t pt-3 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-sm text-muted-foreground">
                            Bukti sudah dikirim ke <span class="font-medium text-foreground">{{ $data->email_penerima }}</span>.
                        </p>

                        <form
                            method="POST"
                            action="{{ route('admin.tukar-faktur.resend-email', $data->id) }}"
                            x-data
                            x-on:submit="$event.target.querySelector('button').disabled = true"
                        >
                            @csrf

                            <x-button type="submit" variant="outline" size="sm">
                                <x-icon name="send" />
                                Kirim Ulang Email
                            </x-button>
                        </form>
                    </div>
                @endif
            </x-card.content>
        </x-card>

        <div>
            <x-button variant="outline" :href="route('admin.tukar-faktur.index')">
                <x-icon name="arrow-left" />
                Kembali
            </x-button>
        </div>

    </div>

    @if ($data->status->isEditable() && auth()->user()->can('update', $data))
        <x-dialog name="edit-tukar-faktur" max-width="2xl">
            <x-dialog.header>
                <x-dialog.title>Edit Tukar Faktur</x-dialog.title>
                <x-dialog.description>Perbarui informasi faktur.</x-dialog.description>
            </x-dialog.header>

            <form method="POST" action="{{ route('admin.tukar-faktur.update', $data->id) }}">
                @csrf
                @method('PUT')

                <x-dialog.content class="grid gap-4 sm:grid-cols-2">
                    <x-form-field label="PT Tujuan" name="pt_tujuan">
                        <x-input type="text" name="pt_tujuan" :value="$data->pt_tujuan" />
                    </x-form-field>

                    <x-form-field
                        label="Perusahaan Pengaju"
                        name="perusahaan_id"
                        :hint="'Tercatat sebagai: ' . $data->perusahaan_pengaju"
                    >
                        {{-- Dibiarkan opsional: data lama bisa saja punya nama yang
                             belum ada di master. Kosongkan = nama lama dipertahankan. --}}
                        <x-perusahaan-select
                            name="perusahaan_id"
                            :value="$data->perusahaan_id"
                            :selected-label="$data->perusahaan?->nama ?? $data->perusahaan_pengaju"
                            target-pic="#edit-nama-pic"
                            target-email="#edit-email-penerima"
                            target-top="#edit-info-top"
                            placeholder="Ketik nama supplier…"
                            ringkas
                        />

                        <p id="edit-info-top" class="text-xs text-info empty:hidden"></p>
                    </x-form-field>

                    <x-form-field label="Tanggal Tukar" name="tanggal_tukar">
                        <x-input type="date" name="tanggal_tukar" :value="$data->tanggal_tukar" />
                    </x-form-field>

                    <x-form-field label="No Kwitansi" name="no_kwitansi">
                        <x-input type="text" name="no_kwitansi" :value="$data->no_kwitansi" />
                    </x-form-field>

                    <x-form-field label="Jumlah Rupiah" name="jumlah_rupiah">
                        <x-input type="number" name="jumlah_rupiah" :value="$data->jumlah_rupiah" />
                    </x-form-field>

                    <x-form-field label="Nama PIC" name="nama_pic">
                        <x-input type="text" name="nama_pic" id="edit-nama-pic" :value="$data->nama_pic" />
                    </x-form-field>

                    <x-form-field label="Email Penerima" name="email_penerima" class="sm:col-span-2">
                        <x-input
                            type="email"
                            name="email_penerima"
                            id="edit-email-penerima"
                            :value="$data->email_penerima"
                        />
                    </x-form-field>
                </x-dialog.content>

                <x-dialog.footer>
                    <x-button type="button" variant="outline" x-on:click="$dispatch('close-modal', 'edit-tukar-faktur')">
                        Batal
                    </x-button>

                    <x-button type="submit">Simpan Perubahan</x-button>
                </x-dialog.footer>
            </form>
        </x-dialog>
    @endif
</x-app-layout>
