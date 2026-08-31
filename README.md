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

### Tata letak document root

Sistem mendukung dua tata letak dan mendeteksinya sendiri:

| Tata letak | Kapan dipakai | Catatan |
|---|---|---|
| Document root = **akar proyek** | XAMPP `htdocs/nama-folder`, cPanel sederhana | `.htaccess` akar meneruskan ke `public/` dan memblokir `app/`, `database/`, `docs/` |
| Document root = **folder `public/`** | cPanel bila kode ditaruh di luar `public_html` | **Lebih aman** - kode dan basis data sama sekali tidak berada di bawah web root |

Tata letak kedua lebih disarankan bila hosting mengizinkan, karena
perlindungan berkas tidak lagi bergantung pada `.htaccess`.

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

Bootstrap 5.3.3, Leaflet 1.9.4, dan Leaflet.markercluster 1.5.3 **disertakan
langsung** di `public/assets/vendor/` (total sekitar 556 KB), bukan dimuat
dari CDN. Konsekuensinya:

- Situs tetap tampil utuh ketika CDN tidak terjangkau - relevan untuk
  konektivitas kecamatan pinggiran dan untuk demo XAMPP tanpa internet.
- Tidak ada ketergantungan pihak ketiga pada aset milik pemerintah daerah.
- Tidak perlu proses build; berkas cukup diunggah apa adanya.

Yang masih memerlukan internet hanyalah **tile peta** (OpenStreetMap dkk.).
Bila tile gagal dimuat, halaman peta otomatis menampilkan **daftar teks
destinasi** sehingga wisatawan tetap memperoleh informasi inti (§10.7 PRD).

### Lapisan peta (FR-MAP-10)

Tersedia tiga lapisan dasar yang seluruhnya tanpa API key: **Peta Jalan**
(OpenStreetMap), **Topografi** (OpenTopoMap - berguna untuk pendakian Gunung
Egon), dan **Citra Satelit** (Esri World Imagery). Masing-masing dapat
dimatikan lewat `app/config/config.php` tanpa mengubah kode.

> Ketentuan pemakaian layanan citra satelit ditetapkan penyedianya dan dapat
> berubah. Sebaiknya dipastikan Bagian Hukum/Diskominfo sebelum go-live, atau
> diganti dengan langganan citra resmi/BIG. Mematikannya tidak memengaruhi
> fitur lain.

Lapisan **Batas Kecamatan** hanya muncul bila berkas GeoJSON resmi diletakkan
di `public/data/` - lihat `public/data/README.md`. Data batas wilayah tidak
disertakan karena harus berasal dari sumber sah (BIG/Ina-Geoportal atau Bagian
Pemerintahan Setda), bukan digambar perkiraan.

### Unduh titik GPS (FR-MAP-11)

Halaman peta menyediakan tombol unduh **GPX** dan **KML** yang mengikuti
filter yang sedang aktif. Berguna bagi pendaki Gunung Egon dan penyelam yang
memerlukan koordinat di lokasi tanpa sinyal.

| Endpoint | Keluaran |
|---|---|
| `GET /ekspor/gpx?kategori=&kecamatan=&q=` | GPX 1.1 untuk GPS genggam & aplikasi trekking |
| `GET /ekspor/kml?kategori=&kecamatan=&q=` | KML untuk Google Earth / Maps |

---

## Lisensi & Hak Cipta

&copy; Karunia Bunda IT Training Center Maumere.
Kode sumber diserahkan penuh kepada Pemerintah Kabupaten Sikka.
Data peta &copy; kontributor OpenStreetMap.
