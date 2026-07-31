{{--
    Jembatan flash message session ke SweetAlert.

    Elemen ini sengaja tidak menampilkan apa pun. Pesannya dititipkan lewat
    atribut data lalu dibaca resources/js/sweetalert.js setelah bundle dimuat,
    karena script inline akan berjalan lebih dulu daripada module Vite.
--}}
@foreach (['success', 'error'] as $type)
    @if (session()->has($type))
        <div
            hidden
            data-flash="{{ $type }}"
            data-flash-title="{{ $type === 'error' ? 'Gagal' : 'Berhasil' }}"
            data-flash-message="{{ session($type) }}"
        ></div>
    @endif
@endforeach
