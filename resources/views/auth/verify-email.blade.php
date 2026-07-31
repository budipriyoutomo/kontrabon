<x-guest-layout>
    <x-slot name="title">Verifikasi Email</x-slot>
    <x-slot name="heading">Verifikasi email Anda</x-slot>
    <x-slot name="description">
        Kami sudah mengirim tautan verifikasi ke email Anda. Klik tautan itu untuk mulai memakai aplikasi.
    </x-slot>

    @if (session('status') == 'verification-link-sent')
        <x-alert variant="success" icon="circle-check" class="mb-4">
            <x-alert.description>
                Tautan verifikasi baru sudah dikirim ke email yang Anda daftarkan.
            </x-alert.description>
        </x-alert>
    @endif

    <div class="flex items-center justify-between gap-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-button type="submit">Kirim Ulang Email</x-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-button type="submit" variant="ghost">Logout</x-button>
        </form>
    </div>
</x-guest-layout>
