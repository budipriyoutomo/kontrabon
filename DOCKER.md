# Docker — Kontrabon (Tukar Faktur)

Image: nginx + php-fpm 8.2 + supervisor dalam satu container. Asset Vite di-build saat image build.

Dua hal **tidak** dibuat di sini karena sudah ada di VPS:

| Sudah ada | Container | Cara dipakai |
|---|---|---|
| Database | `mysql8` (`mysql:8.0.34`) | app connect ke `mysql8:3306` |
| Reverse proxy / SSL | `nginx-proxy-manager` (80, 81, 443) | NPM proxy ke `kontrabon_app:80` |

nginx di dalam image ini **bukan duplikat** NPM — dia yang bicara FastCGI ke php-fpm (NPM tidak bisa serve PHP). Pola sama seperti `maharasa-backend` dan `rekrutment-backend` di VPS Anda.

## Port

Port host yang sudah terpakai: `80`, `81`, `443`, `3308`, `5432`, `5672`, `8000`, `8001`, `8002`, `8080`, `15672`.

App pakai **`8003`** (bebas). Ubah lewat `APP_PORT` kalau perlu.

---

## 1. Siapkan network bersama

Network yang dipakai: **`helpdesk_maharasa-net`** (default `DOCKER_SHARED_NETWORK` di compose).

`mysql8` dan `nginx-proxy-manager` harus ada di network itu. Cek dulu:

```bash
docker network inspect helpdesk_maharasa-net --format '{{range .Containers}}{{.Name}} {{end}}'
```

Sambungkan yang belum ada:

```bash
docker network connect helpdesk_maharasa-net mysql8
docker network connect helpdesk_maharasa-net nginx-proxy-manager
```

`docker network connect` aman — container tetap jalan, network lamanya tidak dicabut.

Pakai network lain:

```bash
DOCKER_SHARED_NETWORK=nama-network-anda docker compose up -d
```

## 2. Build & jalankan

```bash
docker compose build
docker compose up -d
docker compose exec app php artisan migrate --force
```

Cek langsung: `curl -I http://localhost:8003`

## 3. Daftarkan di Nginx Proxy Manager

Buka NPM di `http://IP-VPS:81` → **Hosts → Proxy Hosts → Add Proxy Host**.

Tab **Details**:

| Field | Isi |
|---|---|
| Domain Names | domain Anda, mis. `kontrabon.maharasa.id` |
| Scheme | `http` |
| Forward Hostname / IP | `kontrabon_app` |
| Forward Port | `80` |
| Cache Assets | off |
| Block Common Exploits | on |
| Websockets Support | on |

Scheme tetap `http` — itu koneksi NPM → container di dalam network docker. HTTPS berhenti di NPM, tidak perlu sertifikat di dalam container.

Forward pakai nama container (`kontrabon_app`), bukan `IP:8003`, supaya trafik tidak keluar-masuk host.

## 4. Aktifkan HTTPS

### a. Arahkan DNS

A record domain → IP VPS. Wajib sudah propagasi sebelum request sertifikat, kalau tidak Let's Encrypt gagal validasi.

### b. Request sertifikat di NPM

Edit proxy host tadi → tab **SSL**:

| Field | Isi |
|---|---|
| SSL Certificate | `Request a new SSL Certificate` |
| Force SSL | **on** — redirect http → https |
| HTTP/2 Support | on |
| HSTS Enabled | on (aktifkan setelah https terbukti jalan) |
| Email | email Anda |
| I Agree to the Let's Encrypt ToS | centang |

Port 80 dan 443 sudah dipegang NPM, jadi validasi HTTP-01 jalan tanpa setup tambahan.

### c. Sesuaikan `.env`

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://kontrabon.maharasa.id
SESSION_SECURE_COOKIE=true
```

`APP_URL` dipakai untuk link di email dan PDF — tempat yang tidak punya konteks request, jadi harus benar. `SESSION_SECURE_COOKIE=true` bikin cookie sesi cuma dikirim lewat https.

`APP_ENV=production` juga menyalakan `config:cache` + `route:cache` otomatis di entrypoint.

Lalu:

```bash
docker compose up -d --force-recreate
```

### d. Verifikasi

```bash
curl -I http://kontrabon.maharasa.id     # harus 301 -> https
curl -I https://kontrabon.maharasa.id    # harus 200
```

Buka di browser, cek Console tidak ada error mixed content, dan login berhasil (bukti cookie secure jalan).

### Yang sudah disiapkan di sisi app

Tiga hal ini sudah dibereskan, tidak perlu Anda ubah lagi:

| File | Isi | Kenapa |
|---|---|---|
| [app/Http/Middleware/TrustProxies.php](app/Http/Middleware/TrustProxies.php) | `protected $proxies = '*'` | tanpa ini `X-Forwarded-Proto` diabaikan → `asset()` keluar `http://` → CSS/JS Vite diblokir browser sebagai mixed content |
| [docker/nginx/nginx.conf](docker/nginx/nginx.conf) | `map $http_x_forwarded_proto $fastcgi_https` | param `HTTPS` ke php-fpm harus kosong saat http; nilai mentah `"http"` juga dibaca PHP sebagai ON karena string-nya tidak kosong |
| [docker/nginx/default.conf](docker/nginx/default.conf) | `set_real_ip_from` + `real_ip_header` | IP asli pengunjung, bukan IP container NPM, yang masuk log dan rate limit |

`$proxies = '*'` aman di sini karena container **tidak** publish port 80 ke publik — satu-satunya jalur masuk adalah NPM.

Port `8003` masih terbuka untuk debug. Setelah HTTPS jalan, sebaiknya tutup: hapus blok `ports:` di [docker-compose.yml](docker-compose.yml), atau ikat ke localhost saja dengan `"127.0.0.1:8003:80"`.

---

## Isi container

Satu container, satu service (`app` / `kontrabon_app`). Supervisor menjaga 4 program:

| Program | Perintah | Default | Toggle |
|---|---|---|---|
| `php-fpm` | `php-fpm` | on | — |
| `nginx` | `nginx` | on | — |
| `queue` | `php artisan queue:work --tries=3 --max-time=3600` | **on** | `RUN_QUEUE`, `QUEUE_WORKERS` |
| `scheduler` | `php artisan schedule:work` | off | `RUN_SCHEDULER` |

`scheduler` sengaja off — `app/Console/Kernel.php` masih kosong. Nyalakan saat sudah ada task terjadwal:

```bash
RUN_SCHEDULER=true docker compose up -d
```

Worker keluar tiap 1 jam (`--max-time=3600`) lalu di-start ulang supervisor — cegah memory leak dan pastikan worker pakai kode terbaru.

`stop_grace_period: 90s` + `stopwaitsecs=70` memberi job yang sedang jalan waktu untuk selesai sebelum container dimatikan.

### Kelola program di dalam container

```bash
docker compose exec app supervisorctl status
docker compose exec app supervisorctl restart queue:*
docker compose exec app supervisorctl stop scheduler
```

Restart worker saja setelah deploy — tidak perlu turunkan web server.

### Kapan perlu dipisah lagi

Gabung cocok selama traffic dan volume job masih kecil. Pisahkan jadi container sendiri kalau:

- worker perlu di-scale terpisah dari web (`QUEUE_WORKERS` sudah tidak cukup),
- job berat bikin CPU/RAM web ikut tersendat,
- perlu resource limit atau restart policy berbeda per proses.

Caranya: `RUN_QUEUE=false` di service `app`, lalu tambah service kedua pakai image sama dengan `command: php artisan queue:work`.

## Koneksi database

`.env` dipakai apa adanya (`env_file`), dua variabel ditimpa compose supaya menunjuk ke container:

| Var | Nilai di container |
|---|---|
| `DB_HOST` | `mysql8` |
| `DB_PORT` | `3306` |

Port `3308` tetap berlaku dari host VPS (TablePlus, `mysql -h 127.0.0.1 -P 3308`) — `.env` tidak perlu diubah.

Kalau `mysql8` tidak mau disambungkan ke network bersama, ada jalur host:

```bash
DOCKER_DB_HOST=host.docker.internal DOCKER_DB_PORT=3308 docker compose up -d
```

## Variabel compose

Opsional, taruh di shell atau file `.env` (diawali `DOCKER_` agar tidak bentrok dengan var Laravel):

| Var | Default | Fungsi |
|---|---|---|
| `APP_PORT` | `8003` | port HTTP di host |
| `DOCKER_SHARED_NETWORK` | `maharasa-net` | network berisi `mysql8` + `nginx-proxy-manager` |
| `DOCKER_DB_HOST` | `mysql8` | host DB dari dalam container |
| `DOCKER_DB_PORT` | `3306` | port DB dari dalam container |
| `RUN_QUEUE` | `true` | jalankan queue worker di dalam container |
| `QUEUE_WORKERS` | `1` | jumlah proses worker |
| `RUN_SCHEDULER` | `false` | jalankan `schedule:work` |
| `RUN_MIGRATIONS` | `false` | jalankan `migrate --force` saat start |
| `SESSION_SECURE_COOKIE` | `false` | set `true` setelah HTTPS aktif di NPM |
| `DOCKER_UID` / `DOCKER_GID` | `1000` | uid/gid user `app` di container |

## Perintah harian

```bash
docker compose logs -f app            # log nginx + php-fpm + laravel
docker compose exec app sh            # shell ke container
docker compose exec app php artisan tinker
docker compose exec app supervisorctl restart queue:*   # reload worker saja
docker compose down                   # stop (volume storage tetap)
```

## Cache config

`APP_ENV=local` → entrypoint **tidak** cache config/route/view, jadi ubah `.env` cukup `docker compose restart app`.

`APP_ENV=production` → entrypoint jalankan `config:cache`, `route:cache`, `view:cache` otomatis tiap start.

## Volume

| Volume | Path | Isi |
|---|---|---|
| `storage-app` | `/var/www/html/storage/app` | upload / file hasil app |
| `storage-logs` | `/var/www/html/storage/logs` | `laravel.log` |

Named volume, bukan bind mount — bind mount sering bikin masalah permission di `storage/`.

## Deploy ulang setelah ganti kode

```bash
git pull
docker compose build app
docker compose up -d
```

Kode PHP dan asset frontend keduanya butuh rebuild image (di-`COPY`, tidak di-mount).

## File

- [Dockerfile](Dockerfile) — 3 stage: asset Vite → composer → runtime
- [docker-compose.yml](docker-compose.yml)
- [docker/entrypoint.sh](docker/entrypoint.sh) — permission storage, tunggu DB, `storage:link`, cache
- [docker/nginx/](docker/nginx/) — `nginx.conf`, `default.conf`
- [docker/php/](docker/php/) — `php.ini`, `php-fpm-pool.conf`
- [docker/supervisor/supervisord.conf](docker/supervisor/supervisord.conf)
- [.dockerignore](.dockerignore)
