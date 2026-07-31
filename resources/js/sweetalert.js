import Swal from 'sweetalert2/dist/sweetalert2.esm.js';

// Urutan impor ini penting: CSS bawaan SweetAlert dulu, baru override milik
// aplikasi, supaya penggayaan berbasis design token yang menang.
import 'sweetalert2/dist/sweetalert2.css';
import '../css/sweetalert.css';

/*
 * SweetAlert2 dipakai lewat bundle Vite, bukan CDN, supaya tampilannya bisa
 * diikat ke design token aplikasi dan ikut berganti saat dark mode aktif.
 *
 * Warna tombol sengaja tidak diatur lewat opsi JS: buttonsStyling dimatikan
 * dan seluruh pewarnaan diserahkan ke CSS lewat customClass, agar hanya ada
 * satu sumber kebenaran untuk gaya tombol.
 */
const customClass = {
    popup: 'app-swal',
    title: 'app-swal__title',
    htmlContainer: 'app-swal__body',
    actions: 'app-swal__actions',
    confirmButton: 'app-swal__confirm',
    cancelButton: 'app-swal__cancel',
    icon: 'app-swal__icon',
};

const base = Swal.mixin({
    buttonsStyling: false,
    reverseButtons: true,
    customClass,
});

/**
 * Konfirmasi hapus yang seragam untuk seluruh halaman.
 *
 * Form dibuat saat itu juga karena tombol hapus di dalam tabel berupa
 * <button>, bukan form tersendiri per baris.
 *
 * @returns {Promise<boolean>} true bila user menekan tombol hapus.
 */
function confirmDelete(url, { title = 'Hapus data ini?', text = 'Data yang dihapus tidak bisa dikembalikan.' } = {}) {
    return base
        .fire({
            title,
            text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal',
            customClass: {
                ...customClass,
                confirmButton: 'app-swal__confirm app-swal__confirm--destructive',
            },
        })
        .then((result) => {
            if (! result.isConfirmed) {
                return false;
            }

            const token = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = url;
            form.innerHTML = `
                <input type="hidden" name="_token" value="${token}">
                <input type="hidden" name="_method" value="DELETE">
            `;

            document.body.appendChild(form);
            form.submit();

            return true;
        });
}

/**
 * Tampilkan flash message dari session sebagai dialog.
 *
 * Datanya dibaca dari elemen yang dirender <x-flash>, bukan dari script inline,
 * karena bundle Vite dimuat sebagai module (defer) sehingga script inline yang
 * memanggil Swal akan jalan sebelum Swal sempat terdefinisi.
 */
function renderFlashMessages() {
    document.querySelectorAll('[data-flash]').forEach((element) => {
        const type = element.dataset.flash;

        base.fire({
            icon: type === 'error' ? 'error' : 'success',
            title: element.dataset.flashTitle || (type === 'error' ? 'Gagal' : 'Berhasil'),
            text: element.dataset.flashMessage || '',
            confirmButtonText: 'Tutup',
        });
    });
}

window.Swal = base;
window.ui = { ...(window.ui ?? {}), confirmDelete };

renderFlashMessages();

export { base as Swal, confirmDelete };
