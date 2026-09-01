# Tinjauan Keamanan
### Sistem Informasi Pariwisata Kabupaten Sikka

Tinjauan dilakukan terhadap seluruh kode (96 berkas PHP), sebagian dengan
pengujian serangan nyata terhadap sistem yang berjalan, bukan hanya
pembacaan kode.

---

## Ringkasan

| Kelas kerentanan | Hasil | Cara diperiksa |
|---|---|---|
| SQL injection | Aman | Seluruh query prepared statement; bagian SQL dinamis hanya dari konstanta |
| XSS tersimpan/terpantul | Aman | Seluruh keluaran di-escape; diuji dengan payload nyata |
| CSRF | Aman | 28 dari 28 rute POST memanggil `Csrf::wajib()` |
| Kontrol akses | Aman | 48 dari 48 rute admin memeriksa autentikasi/peran |
| Unggah berkas berbahaya | Aman | Diuji 5 vektor serangan, seluruhnya tertahan |
| Open redirect | Aman | Tujuan dibatasi asal sendiri; diuji dengan referer eksternal |
| Path traversal | Aman | Nama view disaring, berkas dibatasi di dalam `app/views/` |
| Enumerasi pengguna | Aman | Verifikasi hash selalu dijalankan walau pengguna tidak ada |
| Brute force login | Dimitigasi | Batas 10 percobaan/jam per sesi, kegagalan tercatat di log |
| Kebocoran galat | Dimitigasi | Jejak kode hanya saat debug; kini ada peringatan otomatis di dasbor |

---

## Pengujian yang dijalankan

### Unggah berkas

Lima berkas dikirim melalui form admin yang sah:

| Berkas | Hasil |
|---|---|
| PNG sah | **Diterima** - tersimpan dengan nama buatan sistem |
| Kode PHP dengan magic byte JPEG palsu | Ditolak - `getimagesize()` gagal |
| Berkas PHP polos berlabel `image/png` | Ditolak - `finfo` membaca tipe sebenarnya |
| SVG berisi `<script>` | Ditolak - SVG tidak termasuk tipe yang diizinkan |
| PNG sah disisipi kode PHP di ekornya | Diterima sebagai gambar, disimpan berekstensi `.png` |

Kasus terakhir adalah serangan paling realistis karena lolos pemeriksaan
tipe. Pertahanan berlapis yang menanganinya:

1. Ekstensi berkas **selalu** diturunkan dari tipe hasil deteksi, tidak
   pernah dari nama kiriman - berkas bernama `jahat.php.png` tetap tersimpan
   sebagai `.png`.
2. Nama berkas seluruhnya dibuat sistem (waktu + 8 karakter acak), sehingga
   penyerang tidak bisa menebak atau menentukan lokasinya.
3. `public/uploads/.htaccess` mematikan mesin PHP dan menolak akses ke
   berkas berekstensi skrip.

### XSS

Nama destinasi diisi `<script>alert("xss")</script>` melalui panel admin,
lalu halaman publiknya diperiksa. Payload muncul ter-escape di HTML dan
sebagai `<script>` di dalam blok JSON-LD - tidak pernah tereksekusi.

Temuan yang sudah diperbaiki lebih awal: JSON-LD sempat memakai
`json_encode()` biasa sehingga nama destinasi berisi `</script>` dapat
menutup blok skrip dan menjalankan kode. Seluruh JSON yang ditanam di dalam
`<script>` kini melewati `json_skrip()` yang meng-escape `<`, `>`, dan `&`.

### Open redirect

Permintaan POST dengan `Referer: https://situs-jahat.example/` dan token CSRF
salah dikirim ke form pengaduan. Sistem mengabaikan referer eksternal dan
mengarahkan kembali ke halaman sendiri.

---

## Peringatan otomatis di dasbor

Nilai bawaan `config.php` ramah untuk pengembangan di XAMPP, tetapi berbahaya
bila terbawa ke server publik. Alih-alih hanya menuliskannya di dokumentasi
yang mudah terlewat, dasbor admin kini menampilkan peringatan kepada Super
Admin ketika situs diakses dari host non-lokal dan:

- mode debug masih aktif (jejak kode bisa terlihat pengunjung),
- situs berjalan tanpa HTTPS,
- akun masih memakai kata sandi bawaan.

Peringatan tidak muncul saat bekerja di `localhost` agar tidak mengganggu
pengembangan.

---

## Catatan data pribadi (UU PDP No. 27/2022)

Data pribadi yang disimpan sistem ini minimal:

| Tabel | Data pribadi | Sifat |
|---|---|---|
| `pengaduan` | nama pelapor, kontak, alamat IP | Nama dan kontak **opsional** - pengaduan anonim tetap diterima |
| `pengguna_admin` | nama, email, kata sandi (hash bcrypt) | Akun kerja, bukan data publik |
| `umkm` | nama usaha, alamat, telepon | Data usaha yang memang untuk dipublikasikan |
| `ulasan` | nama penulis | Hanya bila fitur ulasan diaktifkan |

Yang masih perlu ditetapkan Dinas bersama Bagian Hukum sebelum go-live:

- [ ] Berapa lama data pengaduan disimpan sebelum dihapus atau dianonimkan
- [ ] Apakah penyimpanan alamat IP pelapor diperlukan; saat ini disimpan
      untuk menelusuri penyalahgunaan, dan dapat dihilangkan bila dinilai
      berlebihan
- [ ] Penyusunan kebijakan privasi yang ditautkan dari form pengaduan
- [ ] Prosedur menanggapi permintaan penghapusan data dari pelapor

---

## Yang tidak tercakup tinjauan ini

- **Uji penetrasi oleh pihak ketiga.** Tinjauan ini dilakukan terhadap kode
  sendiri; pemeriksaan independen tetap disarankan sebelum go-live.
- **Keamanan tingkat server** - konfigurasi hosting, firewall, akses SSH,
  dan pencadangan adalah ranah Diskominfo/penyedia hosting.
- **Perilaku Apache sebenarnya.** Aturan `.htaccess` tidak dapat diuji pada
  server pengembangan PHP. Setelah dipasang, pastikan secara manual bahwa
  `/app/`, `/database/`, dan berkas `.sql` benar-benar tidak dapat diakses
  lewat peramban.
- **Ketahanan terhadap serangan volumetrik (DDoS)** - di luar jangkauan
  aplikasi, bergantung pada penyedia hosting.

## Yang wajib dilakukan sebelum go-live

1. Ganti kata sandi `superadmin` - kata sandi bawaan tercantum di repositori
   dan harus dianggap sudah bocor.
2. Setel `'debug' => false` di `app/config/config.local.php`.
3. Aktifkan HTTPS dan hidupkan baris `Strict-Transport-Security` di
   `public/.htaccess`.
4. Pastikan `app/config/config.local.php` tidak ikut ter-commit ke git
   (sudah diatur di `.gitignore`).
5. Aktifkan pencadangan basis data harian dari cPanel.
6. Uji manual bahwa berkas `.sql` dan folder `app/` tidak dapat diunduh.
