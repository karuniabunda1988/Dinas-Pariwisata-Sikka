# Sistem Informasi & Peta Interaktif Pariwisata Kabupaten Sikka

Platform digital resmi Dinas Pariwisata Kabupaten Sikka dengan **peta
interaktif berbasis pin lokasi** sebagai fitur inti. Setiap destinasi punya
titik koordinat yang dapat diklik dan menampilkan foto, deskripsi, kategori,
jam operasional, kisaran tarif, kontak, serta cara mencapainya.

Dikembangkan oleh **Karunia Bunda IT Training Center Maumere**.

---

## Ringkasan Teknis

| Komponen | Pilihan |
|---|---|
| Bahasa | PHP 8 native, pola MVC, tanpa Composer |
| Basis data | MySQL / MariaDB (PDO, prepared statement) |
| Antarmuka | Bootstrap 5 + CSS sendiri, vanilla JavaScript |
| Peta | Leaflet.js + OpenStreetMap + Leaflet.markercluster |
| Lingkungan | XAMPP (pengembangan) / cPanel shared hosting (produksi) |

Peta memakai **OpenStreetMap, bukan Google Maps API**: tanpa API key, tanpa
kuota, tanpa tagihan berjalan yang bergantung kartu kredit institusi — sesuai
keputusan arsitektur pada PRD §10.1.

---

## Instalasi di XAMPP (Pengembangan)

1. **Salin berkas proyek** ke folder `htdocs`:

   ```
   C:\xampp\htdocs\pariwisata-sikka\
   ```

2. **Jalankan Apache dan MySQL** dari XAMPP Control Panel.

3. **Buat basis data** — buka <http://localhost/phpmyadmin>, lalu:
   - Tab **Import** → pilih `database/schema.sql` → **Go**
   - Tab **Import** → pilih `database/seed.sql` → **Go**

   Berkas `schema.sql` sudah membuat basis data `sikka_pariwisata` sendiri,
   jadi tidak perlu membuatnya manual lebih dulu.

4. **Buka situs** di <http://localhost/pariwisata-sikka/>

   Sistem mendeteksi sendiri lokasi foldernya, jadi tidak ada konfigurasi
   URL yang perlu diubah.

5. **Masuk ke panel admin** di <http://localhost/pariwisata-sikka/admin/login>

   | | |
   |---|---|
   | Nama pengguna | `superadmin` |
   | Kata sandi | `SikkaAdmin2026!` |

   > **Ganti kata sandi ini segera** melalui menu *Pengguna* setelah masuk
   > pertama kali. Kata sandi bawaan tercantum di repositori publik dan
   > karena itu harus dianggap sudah bocor.

### Bila mod_rewrite belum aktif

Buka `C:\xampp\apache\conf\httpd.conf`, hilangkan tanda `#` pada baris
`LoadModule rewrite_module modules/mod_rewrite.so`, pastikan
`AllowOverride All` untuk direktori `htdocs`, lalu mulai ulang Apache.

---

## Pemasangan di Hosting cPanel (Produksi)

1. Unggah seluruh berkas ke `public_html` (atau subfolder) lewat File Manager
   atau FTP.
2. Buat basis data + pengguna MySQL di cPanel, lalu impor `database/schema.sql`
   dan `database/seed.sql` melalui phpMyAdmin.
3. Salin `app/config/config.local.example.php` menjadi
   `app/config/config.local.php` dan isi kredensial basis data hosting.
   Berkas ini diabaikan git sehingga kredensial tidak ikut ter-commit.
4. Di `config.local.php`, set `'debug' => false`.
5. Pastikan folder `public/uploads/` dapat ditulis (izin `755`).
6. Aktifkan HTTPS (Let's Encrypt tersedia gratis di cPanel), lalu aktifkan
   baris `Strict-Transport-Security` di `public/.htaccess`.
7. Masuk ke `/admin/login`, ganti kata sandi bawaan, lalu isi menu
   **Pengaturan Situs** (nomor WhatsApp notifikasi, kontak dinas, tautan PPID
   dan OSS).

### Setelah go-live

- Daftarkan `sitemap.xml` di Google Search Console.
- Aktifkan backup basis data harian dari cPanel.
- Ajukan domain `.go.id` melalui Diskominfo Kabupaten Sikka.

---

## Struktur Proyek

```
.htaccess              Meneruskan seluruh permintaan ke public/
index.php              Cadangan bila mod_rewrite tidak aktif
app/
  config/              Konfigurasi (config.local.php menimpa yang bawaan)
  core/                Kernel: router, PDO, sesi, autentikasi, CSRF,
                       i18n, unggah berkas, notifikasi bertingkat
  controllers/         Controller publik, API, dan panel admin
  models/              Akses data (seluruhnya prepared statement)
  views/               Template: layouts, partials, halaman, admin
  lang/                Kamus Bahasa Indonesia & Inggris
public/
  index.php            Front controller
  assets/css|js|img    Aset statis; peta.js adalah inti fitur peta
  uploads/             Foto destinasi/UMKM/event/artikel
database/
  schema.sql           Skema tabel
  seed.sql             21 kecamatan, 6 kategori, destinasi awal
docs/
  panduan-admin.md     Panduan untuk staf Dinas (non-teknis)
  sop-pembaruan-data.md SOP verifikasi berkala
```

---

## API Internal

Endpoint JSON yang dipakai peta, dirancang agar bisa dipakai ulang aplikasi
mobile pada fase berikutnya tanpa perubahan backend.

| Endpoint | Fungsi |
|---|---|
| `GET /api/destinasi?kategori=&kecamatan=&bbox=&q=` | Pin dalam viewport/filter, payload ringan |
| `GET /api/destinasi/{slug}` | Detail penuh satu destinasi |
| `GET /api/kategori` | Kategori + jumlah destinasi |
| `GET /api/kecamatan` | 21 kecamatan + jumlah destinasi |
| `GET /api/cari?q=` | Autocomplete pencarian peta |
| `GET /api/umkm?destinasi_id=&jenis=` | UMKM terverifikasi |

`bbox` memakai urutan `minLng,minLat,maxLng,maxLat`.

---

## Catatan Penting tentang Data

**Koordinat pada `seed.sql` adalah perkiraan dari sumber sekunder, bukan hasil
survei GPS lapangan.** Baris yang ditandai `perlu_verifikasi_lapangan = 1`
berstatus draft dan tidak tampil ke publik sampai diverifikasi Dinas.
Destinasi berstatus *aktif* wajib memiliki titik koordinat — sistem menolak
menyimpannya bila kosong, dan menolak koordinat di luar wilayah Kabupaten
Sikka.

**Statistik kunjungan diinput manual.** Sistem ini tidak melacak wisatawan
secara otomatis. Setiap baris statistik wajib mencantumkan sumber datanya agar
dapat diaudit ketika dipakai sebagai bahan rapat anggaran.

---

## Keamanan

- Seluruh query memakai prepared statement (PDO, emulasi dimatikan).
- Semua form POST diproteksi token CSRF.
- Kata sandi di-hash dengan bcrypt; percobaan masuk dibatasi per jam.
- Seluruh keluaran di-escape; JSON yang ditanam di dalam `<script>` melewati
  `json_skrip()` yang meng-escape `<`, `>`, dan `&`.
- Folder `public/uploads/` tidak mengeksekusi PHP; tipe berkas divalidasi
  dari isinya (finfo), bukan dari ekstensi.
- Form publik dilindungi honeypot dan batas laju.
- Panel admin diberi `noindex` dan diblokir lewat `robots.txt`.

---

## Aset Pihak Ketiga

Bootstrap dan Leaflet dimuat dari CDN. Bila instansi menghendaki situs tetap
berjalan tanpa akses CDN (mis. jaringan internal), unduh berkas Bootstrap dan
Leaflet ke `public/assets/vendor/` lalu ganti URL-nya di
`app/views/layouts/utama.php`, `app/views/layouts/admin.php`, dan view yang
memuat peta. Halaman peta sudah menyediakan cadangan **daftar teks destinasi**
yang tetap tampil bila peta gagal dimuat.

---

## Lisensi & Hak Cipta

&copy; Karunia Bunda IT Training Center Maumere.
Kode sumber diserahkan penuh kepada Pemerintah Kabupaten Sikka.
Data peta &copy; kontributor OpenStreetMap.
