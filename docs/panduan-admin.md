# Panduan Panel Admin
### Sistem Informasi Pariwisata Kabupaten Sikka

Panduan ini ditulis untuk **staf Dinas tanpa latar belakang teknis**.
Anda tidak perlu tahu apa itu koordinat GPS, database, atau HTML.

---

## 1. Masuk ke Panel Admin

1. Buka alamat situs, tambahkan `/admin` di belakangnya.
2. Masukkan nama pengguna dan kata sandi Anda.

**Langkah pertama setelah menerima akun:** buka menu **Pengguna** → pilih nama
Anda → isi kata sandi baru → **Simpan**. Kata sandi minimal 10 karakter.

---

## 2. Mengenal Peran Akun

| Peran | Yang bisa dilakukan |
|---|---|
| **Super Admin** | Semuanya, termasuk mengelola akun dan pengaturan situs |
| **Admin Konten** | Mengelola destinasi, event, artikel, UMKM, pengaduan, statistik |
| **Mitra** | Hanya menyunting entri UMKM miliknya sendiri |

Mitra tidak dapat memverifikasi entrinya sendiri — verifikasi selalu dilakukan
staf Dinas.

---

## 3. Menambah Destinasi Baru

Ini pekerjaan paling sering. Target: **selesai dalam 10 menit** tanpa bantuan.

1. Klik **Destinasi & Pin Peta** → **+ Tambah Destinasi**.

2. **Isi nama destinasi.** Alamat halaman (slug) terisi otomatis — biarkan saja.

3. **Pilih kategori.** Kategori menentukan warna pin di peta:
   - 🏖 Pantai & Bahari — biru kehijauan
   - ⛰ Alam & Trekking — hijau
   - 🛕 Budaya & Religi — emas
   - 🏛 Buatan — terakota

4. **Pilih kecamatan.**

5. **Tentukan lokasi di peta** — ini bagian terpenting:
   - Pada kotak *Loncat ke area*, pilih kecamatan agar peta bergeser ke sana.
   - **Klik titik lokasi destinasi pada peta.** Sebuah pin akan muncul.
   - Kurang tepat? Klik lagi di tempat lain, atau seret pinnya.
   - Teks di bawah peta akan berbunyi *"Titik terpilih: ..."* berwarna hijau.

   > **Anda tidak perlu mengetik angka koordinat sama sekali.**
   > Bila muncul peringatan merah "di luar wilayah Kabupaten Sikka",
   > berarti Anda salah klik — klik ulang di lokasi yang benar.

6. **Isi deskripsi singkat.** Satu-dua kalimat. Teks inilah yang muncul di
   popup peta dan di hasil pencarian Google, jadi tulislah yang menarik dan
   jujur.

7. **Isi informasi praktis** — jam operasional, kisaran tarif, fasilitas,
   kontak pengelola, jarak dan waktu tempuh dari Maumere.

   Bagian ini yang mencegah wisatawan datang lalu kecewa. Bila Anda belum
   tahu jam bukanya, **kosongkan saja** — jangan mengarang.

8. **Unggah foto utama.** JPG atau PNG, maksimal 3 MB, sebaiknya mendatar
   (landscape).

   > Gunakan foto hasil kunjungan lapangan atau kiriman desa wisata yang
   > sudah memberi izin. **Jangan mengambil foto dari internet** — itu
   > pelanggaran hak cipta yang bisa menyeret Dinas ke masalah hukum.

9. **Pilih status:**
   - **Draft** — belum tampil ke publik. Pakai ini bila data belum lengkap.
   - **Aktif** — tampil di peta dan situs.
   - **Nonaktif** — disembunyikan sementara (mis. sedang ditutup).

10. Klik **Simpan**.

Setelah tersimpan, Anda dapat menambahkan **foto galeri** (minimal 3 foto
per destinasi). Isi juga kolom *alt text* — deskripsi singkat isi foto, agar
dapat dibaca pengguna tunanetra.

### Bila sistem menolak menyimpan

Sistem sengaja menolak beberapa hal demi menjaga kualitas data:

| Pesan | Artinya |
|---|---|
| "wajib memiliki titik koordinat" | Status Aktif tapi belum klik peta. Klik peta, atau simpan sebagai Draft. |
| "di luar wilayah Kabupaten Sikka" | Salah klik lokasi. Klik ulang. |
| "Deskripsi singkat wajib diisi" | Status Aktif harus punya deskripsi. |

Isian Anda **tidak hilang** saat penolakan — perbaiki bagian yang ditandai
lalu simpan lagi.

---

## 4. Merawat Data (jangan dilewati)

Situs lama Dinas gagal bukan karena jelek, tapi karena **tidak pernah
diperbarui sejak 2014**. Bagian ini yang mencegah hal itu terulang.

Pada **Dasbor**, kotak *"Data perlu diverifikasi ulang"* menampilkan destinasi
yang tidak disentuh lebih dari 6 bulan. Untuk masing-masing:

1. Hubungi pengelola atau kontak kecamatan.
2. Tanyakan: jam buka masih sama? tarif berubah? nomor kontak masih aktif?
3. Perbarui bila ada perubahan.
4. Klik **"Tandai sudah diverifikasi hari ini"**.

Lakukan minimal **sekali tiap 6 bulan per destinasi**. Lihat
`sop-pembaruan-data.md`.

---

## 5. Menangani Pengaduan

Menu **Pengaduan** menampilkan laporan wisatawan. Angka merah di samping menu
berarti ada laporan yang belum ditindak.

1. Klik **Buka** pada laporan.
2. Bila pelapor mencantumkan nomor, tersedia tombol **Balas via WhatsApp**.
3. Ubah status: **Baru** → **Diproses** → **Selesai**.
4. Tulis catatan internal (tidak dilihat publik) berisi tindakan yang diambil.

> Pengaduan **selalu tersimpan** meskipun notifikasi WhatsApp gagal terkirim.
> Karena itu menu ini wajib diperiksa rutin, jangan hanya mengandalkan
> notifikasi masuk ke HP.

---

## 6. UMKM & Mitra

Menu **UMKM & Akomodasi** berisi warung, homestay, kelompok tenun, dan
operator dive.

- Entri baru berstatus **menunggu** dan belum tampil ke publik.
- Setelah diperiksa kebenarannya, ubah menjadi **terverifikasi**.
- Isi **Destinasi terdekat** — inilah yang membuat UMKM ikut tampil di
  halaman destinasi dan di popup peta. Bagian inilah yang paling langsung
  membantu pelaku usaha kecil.

---

## 7. Event & Budaya

Menu **Event & Budaya** untuk perayaan adat, festival, dan hari besar
keagamaan.

- Tanggal yang mengikuti kalender adat atau liturgi (misalnya Pekan Suci)
  **berubah tiap tahun** — biarkan berstatus Draft sampai tanggalnya
  dikonfirmasi ke pihak penyelenggara, baru ubah ke Aktif.
- Bila diisi **Destinasi terkait**, event otomatis tertaut ke pin peta.

---

## 8. Statistik Kunjungan

Menu **Statistik Kunjungan** untuk memasukkan angka kunjungan bulanan.

- Sistem **tidak menghitung wisatawan secara otomatis**. Angka diambil dari
  laporan yang sudah dikumpulkan OPD.
- **Sumber data wajib diisi**, misalnya *"Laporan bulanan UPT Pantai Koka,
  Maret 2026"*. Angka tanpa sumber tidak bisa dipertanggungjawabkan saat
  dipakai untuk rapat anggaran bersama DPRD.
- Memasukkan periode yang sama dua kali akan **memperbarui** angkanya,
  bukan menggandakan.
- Tombol **Ekspor CSV** menghasilkan berkas yang langsung bisa dibuka di
  Excel sebagai bahan laporan.

---

## 9. Pengaturan Situs (Super Admin)

Menu **Pengaturan Situs** berisi nama situs, kontak dinas, dan:

- **Nomor WhatsApp notifikasi** — tujuan pemberitahuan pengaduan masuk.
  Bila dikosongkan, pengaduan tetap tersimpan, hanya tidak ada notifikasi.
- **Ulasan publik** — biarkan nonaktif sampai ada staf yang benar-benar
  rutin memoderasi. Ulasan spam yang dibiarkan tayang lebih merusak
  kredibilitas daripada tidak ada ulasan sama sekali.

---

## 10. Bila Terjadi Masalah

| Masalah | Yang harus dilakukan |
|---|---|
| Peta tidak muncul di form | Periksa koneksi internet. Peta memerlukan internet aktif. |
| Foto gagal diunggah | Ukuran melebihi 3 MB, atau bukan JPG/PNG/WebP. Perkecil dahulu. |
| "Sesi Anda kedaluwarsa" | Anda terlalu lama membiarkan form terbuka. Isian tetap tersimpan — klik Simpan sekali lagi. |
| Lupa kata sandi | Hubungi Super Admin untuk menyetelkan yang baru. |
| Situs tidak bisa dibuka sama sekali | Hubungi pengelola hosting/Diskominfo. |
