---
name: caveman
description: Mode kompresi gaya bicara opsional untuk menghemat token respons AI. Aktifkan manual via /caveman [level] atau permintaan eksplisit "ngomong caveman"/"mode caveman". Jangan aktifkan otomatis, dan jangan pernah pakai ini sebagai alasan melonggarkan aturan wajib project (lihat skill resto-bot-dev) — caveman cuma mengubah gaya kalimat, bukan substansi teknis atau proses kerja.
---

# caveman — Mode Kompresi Respons

## Kapan aktif

- Aktif hanya setelah user memanggil `/caveman [level]` (atau varian "ngomong caveman", "mode caveman") dalam sesi ini.
- Tetap aktif untuk **sisa sesi** sampai user ganti level lain atau ketik `stop caveman` — tidak reset otomatis tiap giliran.
- Tidak aktif secara default. Tidak menggantikan atau melonggarkan aturan wajib project (`resto-bot-dev`, `AGENTS.md`, TDD, Clean Architecture, isolasi multi-tenant, dst) — caveman hanya mengubah **gaya bahasa prosa**, bukan substansi teknis atau urutan kerja.

## Level & perilaku

| Level | Perilaku |
|---|---|
| `lite` | Buang filler ("secara garis besar", "perlu dicatat bahwa") dan hedging berlebih. Kalimat tetap lengkap subjek-predikat. Nada tetap profesional. |
| `full` (default) | Buang kata sandang & filler. Fragmen kalimat boleh. Sinonim pendek (mis. "pakai" bukan "menggunakan"). |
| `ultra` | Fragmen telanjang. Singkatan umum (DB, auth, fn, ep=endpoint, migr=migration). Panah (`→`) untuk sebab-akibat/alur. |
| `wenyan-lite` | Register Bahasa Tionghoa Klasik ringan — hanya kalau diminta eksplisit; default tetap Indonesia/Inggris teknis. |
| `wenyan-full` | Maksimum 文言文, reduksi 80–90% karakter. |
| `wenyan-ultra` | Kompresi klasik ekstrem. |

## Yang TIDAK BOLEH dikompres — harus exact
- Code block, snippet, path file, nama fungsi/kelas/variabel/tabel.
- Error string, stack trace, log output, hasil test (`phpunit`, `pest`, `larastan`, `pint`).
- Angka: coverage %, versi PHP/Laravel, nomor migrasi, port, status code HTTP.
- Command shell yang harus di-copy-paste user (`php artisan migrate`, `composer install`, `composer update`, `docker compose`, dst).
- Isi laporan `/pre-push` dan `/update-docs` — hasil ✅/❌ dan checklist tetap format lengkap, jangan difragmenkan (user butuh baca ini utuh sebelum push).

## Auto-clarity — turun ke prosa normal

Matikan kompresi sementara (kembali ke prosa normal) untuk:

- Peringatan keamanan — secret ke-commit, `.env` ter-staged, API key/model AI hardcode.
- Konfirmasi aksi ireversibel — `git push --force`, edit/hapus migration Laravel yang sudah dijalankan (`php artisan migrate`), `migrate:fresh`, `db:wipe`, drop table, atau perubahan yang menyentuh isolasi data tenant.
- Urutan multi-langkah yang berisiko salah baca kalau difragmenkan (mis. instruksi migrasi + rollback, langkah deploy).
- User mengulang pertanyaan yang sama — tanda respons caveman sebelumnya tidak jelas.

Kembali ke mode ringkas setelah bagian yang butuh kejelasan itu selesai — jangan matikan seluruh sesi caveman hanya karena satu momen butuh kejelasan.

## Cara pakai

```
/caveman              # mode full (default)
/caveman lite          # kompresi lebih ringan
/caveman ultra          # kompresi ekstrem
/caveman wenyan          # Bahasa Tionghoa Klasik (full)
stop caveman             # kembali ke prosa normal, matikan mode
```

## Contoh
Pertanyaan: "Kenapa route saya selalu 404?"

Normal:
> Route belum terdaftar atau route cache masih menggunakan konfigurasi lama. Jalankan `php artisan route:list` dan bila perlu `php artisan optimize:clear`.

Caveman (full):
> Route belum register atau route cache lama. Cek `php artisan route:list`. Jika perlu `php artisan optimize:clear`.

Caveman (ultra):
> Route 404 → belum register / cache. `route:list` → `optimize:clear`.

  ## Relasi dengan skill lain

  - `resto-bot-dev` tetap wajib jalan bersamaan — caveman tidak boleh jadi alasan skip TDD, skip test isolasi multi-tenant, atau skip checklist `/pre-push`/`/update-docs`. Caveman cuma memendekkan kalimat penjelasan, bukan langkah kerja.
  - Kalau caveman aktif dan `AGENTS.md`/dokumen lain wajib dibaca penuh sebelum kerja (lihat AGENTS.md §2), tetap baca dan patuhi seluruhnya — hanya cara melaporkan hasilnya yang dipadatkan.