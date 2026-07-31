import TomSelect from 'tom-select';
import 'tom-select/dist/css/tom-select.css';
import './perusahaan-select.css';

/**
 * Dropdown pencarian supplier.
 *
 * Dipakai form publik (Bootstrap) maupun halaman admin (Tailwind), jadi
 * tampilannya sengaja netral dan hanya dirapikan lewat perusahaan-select.css.
 *
 * Cara pakai: <select data-perusahaan-select data-endpoint="..."> — lihat
 * komponen Blade x-perusahaan-select.
 */

function inisialisasi(el) {
    if (el.tomselect) {
        return;
    }

    const endpoint = el.dataset.endpoint;
    const minimal = parseInt(el.dataset.minChars || '2', 10);

    // Target auto-isi (opsional). Diisi hanya bila server mengirim datanya —
    // endpoint publik sengaja tidak mengirim kontak PIC.
    const targetPic = el.dataset.targetPic ? document.querySelector(el.dataset.targetPic) : null;
    const targetEmail = el.dataset.targetEmail ? document.querySelector(el.dataset.targetEmail) : null;
    const targetTop = el.dataset.targetTop ? document.querySelector(el.dataset.targetTop) : null;

    new TomSelect(el, {
        valueField: 'id',
        labelField: 'nama',
        searchField: 'nama',
        maxOptions: 20,
        create: false,
        // Opsi terpilih sudah ditanam server-side; jangan sampai terhapus
        // saat hasil pencarian pertama datang.
        preload: false,
        shouldLoad: (query) => query.length >= minimal,

        load(query, callback) {
            if (!endpoint) {
                callback();
                return;
            }

            fetch(`${endpoint}?q=${encodeURIComponent(query)}`, {
                headers: { Accept: 'application/json' },
            })
                .then((response) => (response.ok ? response.json() : []))
                .then((json) => callback(json.data || []))
                .catch(() => callback());
        },

        render: {
            option(data, escape) {
                const top = data.top !== null && data.top !== undefined
                    ? `<span class="ps-keterangan">TOP ${escape(String(data.top))} hari</span>`
                    : '';

                return `<div class="ps-opsi"><span class="ps-nama">${escape(data.nama)}</span>${top}</div>`;
            },
            item(data, escape) {
                return `<div>${escape(data.nama)}</div>`;
            },
            no_results(data, escape) {
                return `<div class="no-results">Supplier "${escape(data.input)}" tidak terdaftar. Hubungi finance Maharasa.</div>`;
            },
            loading() {
                return '<div class="ps-memuat">Mencari…</div>';
            },
        },

        onChange(value) {
            const pilihan = this.options[value];

            if (!pilihan) {
                return;
            }

            if (targetPic && pilihan.nama_pic) {
                targetPic.value = pilihan.nama_pic;
            }

            if (targetEmail && pilihan.email) {
                targetEmail.value = pilihan.email;
            }

            if (targetTop) {
                targetTop.textContent = pilihan.top !== null && pilihan.top !== undefined
                    ? `Term of payment: ${pilihan.top} hari`
                    : '';
            }
        },
    });
}

function pasangSemua() {
    document.querySelectorAll('[data-perusahaan-select]').forEach(inisialisasi);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', pasangSemua);
} else {
    pasangSemua();
}

// Supaya elemen yang muncul belakangan (mis. isi modal) bisa dipasang manual.
window.pasangPerusahaanSelect = pasangSemua;
