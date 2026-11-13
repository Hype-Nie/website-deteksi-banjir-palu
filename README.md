# Palu Waspada

Aplikasi Laravel 12 untuk eksplorasi kerawanan banjir Kota Palu. Antarmuka menggunakan Blade, CSS modular, JavaScript, Leaflet 1.9.4, dan ikon Lucide. Inferensi menggunakan dua bundle `.pkl` yang disertakan, tanpa melatih ulang model.

## Fitur

- Peta interaktif dengan pilihan K-Means/FCM, filter kategori, penggunaan lahan, dan zona transisi, klik titik untuk rincian.
- Perbandingan DBI, Silhouette, distribusi kategori, FPC, dan kesepakatan antar model.
- Data sampel dengan pencarian, pagination, filter, tautan ke titik pada peta, dan ekspor CSV sesuai filter.
- Analisis titik baru dengan validasi server, CSRF, pembatasan permintaan, serta keanggotaan FCM.
- Halaman metodologi, bobot parameter, sumber, dan batas interpretasi.

## Menjalankan

### Menjalankan proyek yang sudah disiapkan

Buka terminal di folder proyek dan jalankan:

```powershell
php artisan serve
```

Buka http://127.0.0.1:8000. Biarkan terminal tetap terbuka selama aplikasi digunakan, tekan `Ctrl+C` untuk menghentikan server. Peta dasar dan Google Fonts membutuhkan koneksi internet.

Jika aplikasi menampilkan pesan bahwa data analisis belum tersedia, jalankan `php artisan flood:prepare` terlebih dahulu. Penjelasan perintah tersebut tersedia pada bagian [Persiapan data analisis](#persiapan-data-analisis).

### Pemasangan pertama kali

Prasyarat: PHP >=8.2, Composer, Node >=20.19 dengan npm, dan Python 3.10 (versi yang diuji). Gunakan CPython Windows, bukan Python MSYS, untuk paket numerik. Pastikan folder `model/` berisi dataset CSV dan kedua file model `.pkl`.

1. Buka terminal di folder proyek, lalu pasang dependensi Laravel dan frontend:

```sh
composer install
npm install
```

Jika PowerShell menolak menjalankan `npm.ps1`, gunakan `npm.cmd install` dan `npm.cmd run build` sebagai pengganti perintah npm di panduan ini.

2. Jika file `.env` belum ada, salin `.env.example` menjadi `.env`, lalu buat kunci aplikasi. Jangan menimpa `.env` yang sudah berisi konfigurasi Anda.

```powershell
Copy-Item .env.example .env
php artisan key:generate
```

Pada Linux/macOS gunakan `cp .env.example .env` untuk menyalin file. Konfigurasi berikut dapat digunakan untuk menjalankan aplikasi lokal tanpa database:

```dotenv
APP_NAME="Palu Waspada"
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

3. Buat virtual environment Python di folder proyek:

```sh
python -m venv .venv
```

Jika `python` di Windows mengarah ke MSYS, gunakan path lengkap CPython ketika membuat virtual environment. Sebagai contoh, pada mesin pengembangan ini perintahnya adalah `C:/laragon/bin/python/python-3.10/python.exe -m venv .venv`.

4. Pasang dependensi Python ke virtual environment. Pada Windows PowerShell:

```powershell
.\.venv\Scripts\python.exe -m pip install -r requirements.txt
```

Linux/macOS:

```sh
.venv/bin/python -m pip install -r requirements.txt
```

Backend secara default memakai `.venv/Scripts/python.exe` pada Windows dan `.venv/bin/python` pada Linux/macOS. Untuk interpreter lain, atur `FLOOD_PYTHON` di `.env` menggunakan path absolut.

5. Siapkan hasil analisis, build aset, dan jalankan server:

```sh
php artisan flood:prepare
npm run build
php artisan serve
```

Buka http://127.0.0.1:8000. Untuk pengembangan aset dengan hot reload, jalankan `npm run dev` di terminal terpisah.

### Persiapan data analisis

```sh
php artisan flood:prepare
```

Jalankan perintah ini setelah pemasangan pertama, setelah dataset atau file model berubah, atau ketika `storage/app/flood/results.json` belum ada. Perintah ini tidak perlu dijalankan setiap kali aplikasi dibuka. File hasil tidak disimpan di Git, sehingga perlu dibuat setelah proyek disalin ke lingkungan baru.

Perintah tersebut membaca dataset CSV dan kedua bundle model di `model/`, membersihkan baris yang tidak lengkap, lalu menjalankan inferensi menggunakan model K-Means dan FCM yang sudah dilatih. Selanjutnya, perintah menghitung metrik perbandingan dan zona transisi, mengubah koordinat UTM 50S ke WGS 84, dan menyimpan hasil ke `storage/app/flood/results.json`. Peta, tabel, dan halaman perbandingan menggunakan hasil tersebut.

Perintah ini **tidak melatih ulang model**. Model dan dataset sumber tetap berada di folder `model/`. Untuk menyiapkan ulang hasil setelah mengubah sumber, jalankan kembali perintah ini sebelum membuka aplikasi.

Jika persiapan gagal, pastikan virtual environment dan dependensi Python sudah terpasang, `FLOOD_PYTHON` menunjuk ke interpreter yang benar jika dikonfigurasi, dan dataset serta kedua file `.pkl` tersedia di `model/`. Jika hasil belum tersedia, aplikasi menampilkan petunjuk persiapan dengan status 503.

## Data dan model

Sumber ada di `model/`: dataset CSV, notebook penelitian, dan dua model.

Hasil dataset bawaan: 1.426 baris sumber, 1.423 valid, 3 dikecualikan. Kategori tinggi 227, sedang 714, rendah 482 pada kedua model, 82 titik transisi. Angka dihitung dari inferensi model.

Bundle mencatat scikit-learn 1.9.1, runtime kompatibilitas yang diuji adalah 1.6.1. Aplikasi memakai transformer dan pusat klaster tersimpan. Perintah persiapan menolak hasil apabila prediksi K-Means berbeda dari label pelatihan tersimpan. Seluruh 1.423 label cocok, keanggotaan FCM diverifikasi terhadap `scikit-fuzzy.cmeans_predict` dengan toleransi absolut 1e-10. Jangan mengganti versi dependensi tanpa menjalankan ulang pemeriksaan ini. Muat hanya bundle model tepercaya.

Koordinat sumber ditafsirkan EPSG:32750 sesuai notebook. Tidak ada berkas batas administrasi di proyek sehingga peta menampilkan titik, bukan poligon kerawanan. Peta dasar OpenStreetMap dan Google Fonts memerlukan internet, titik analisis dan font fallback tetap tersedia ketika sumber eksternal tidak dapat diakses.

Tidak ada data cuaca langsung. Derajat keanggotaan FCM, FPC, dan kesepakatan metode bukan probabilitas kejadian atau akurasi prediksi banjir. Label kategori mengikuti pemetaan elevasi dari notebook.

## Struktur

```text
app/Http/Controllers/FloodController.php     HTTP dan ekspor
app/Http/Requests/                          Validasi filter dan input
app/Services/Flood/                         Repository hasil dan proses Python
config/flood.php                           Path interpreter, dataset, hasil
scripts/flood/                             Transformasi, inferensi, persiapan data
scripts/flood_cli.py                       Antarmuka JSON stdin/stdout
resources/views/{layouts,components,flood} Tampilan Blade
resources/css/                            Gaya dasar, layout, peta, halaman
resources/js/flood/                       Peta, form prediksi, format data
```

Proses Python menerima argumen tetap dan input JSON melalui stdin, bukan interpolasi perintah shell. Setiap prediksi baru memakai bundle tersimpan. Dataset menggunakan hasil persiapan agar permintaan peta tidak memuat model berulang kali.

## Pengujian

Jalankan persiapan data dan build sebelum pengujian.

```sh
php artisan test
```

Pemeriksaan numerik terhadap notebook:

```powershell
.\.venv\Scripts\python.exe -m pip install -r requirements-dev.txt
.\.venv\Scripts\python.exe scripts/test_models.py
```

Pengujian browser (server lokal dijalankan otomatis jika belum ada):

```sh
npx playwright install chromium
npm run test:e2e
```

Konfigurasi produksi: set `APP_DEBUG=false`, arahkan document root ke `public`, sediakan izin tulis `storage` dan `bootstrap/cache`, siapkan venv dan hasil analisis di server, lalu jalankan build aset. Folder `docs` berisi draf penelitian dan tidak disajikan melalui public web root.

Referensi peta: [dokumentasi Leaflet](https://leafletjs.com/reference-1.9.4.html).
