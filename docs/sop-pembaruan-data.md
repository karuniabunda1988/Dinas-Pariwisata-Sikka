# SOP Pembaruan & Verifikasi Data
### Dinas Pariwisata Kabupaten Sikka

Dokumen ini menjawab satu risiko yang paling mungkin mematikan platform:
**sistem selesai dibangun lalu ditinggal**, seperti yang terjadi pada situs
sebelumnya.

---

## 1. Penanggung Jawab

| Peran | Tugas | Frekuensi |
|---|---|---|
| Admin Konten Dinas | Input & pembaruan data di panel admin | Harian/mingguan |
| Kontak kecamatan (1 orang per kecamatan) | Memverifikasi kondisi lapangan | Per permintaan & tiap 6 bulan |
| Super Admin | Memantau dasbor, mengelola akun, backup | Bulanan |
| Kepala Bidang | Meninjau laporan pemenuhan target | Triwulan |

**Beban tidak boleh ditumpuk pada satu-dua orang di Dinas.** Tunjuk satu
kontak per kecamatan — staf kecamatan, staf desa, atau pengelola desa wisata —
sebagai sumber verifikasi.

---

## 2. Siklus Verifikasi Rutin

### Mingguan — Admin Konten
- Buka Dasbor, periksa **Pengaduan belum ditindak**; tanggapi dalam 3 hari kerja.
- Periksa **UMKM menunggu verifikasi**.
- Terbitkan minimal 1 pembaruan konten (destinasi baru, artikel, atau event).

### Bulanan — Admin Konten & Super Admin
- Input statistik kunjungan bulan sebelumnya beserta sumbernya.
- Periksa daftar **"Data perlu diverifikasi ulang"** di Dasbor.
- Pastikan target **minimal 4 pembaruan konten per bulan** tercapai
  (angka ini tampil di Dasbor).
- Pastikan backup basis data berjalan.

### Per 6 Bulan — Kontak Kecamatan
Untuk setiap destinasi di wilayahnya, konfirmasi:

- [ ] Jam operasional masih sama?
- [ ] Kisaran tarif berubah?
- [ ] Nomor kontak pengelola masih aktif?
- [ ] Fasilitas masih tersedia (toilet, parkir, warung)?
- [ ] Kondisi akses jalan berubah?
- [ ] Foto masih menggambarkan kondisi sekarang?
- [ ] Destinasi masih dibuka untuk umum?

Hasilnya dilaporkan ke Admin Konten, yang memperbarui data lalu menekan
**"Tandai sudah diverifikasi hari ini"**.

### Tahunan
- Tinjau ulang klasifikasi kategori dan destinasi unggulan.
- Cocokkan data dengan Sisparnas Kemenparekraf.
- Perbarui foto destinasi yang sudah lebih dari 2 tahun.

---

## 3. Standar Kualitas Data

Sebelum sebuah destinasi diubah dari Draft menjadi **Aktif**:

- [ ] Nama resmi, bukan sebutan populer semata
- [ ] Titik koordinat dipilih lewat klik peta dan sudah dicek posisinya
- [ ] Kategori dan kecamatan terisi
- [ ] Deskripsi singkat terisi (muncul di Google)
- [ ] Deskripsi lengkap minimal 2 paragraf
- [ ] Minimal 3 foto, tanpa watermark pihak ketiga, ada izin penggunaan
- [ ] Alt text terisi pada setiap foto
- [ ] Jam operasional dan kisaran tarif terisi, atau sengaja dikosongkan
      karena memang belum diketahui — **bukan diisi karangan**
- [ ] Cara mencapai menjelaskan kondisi jalan apa adanya
- [ ] Kolom sumber data terisi

---

## 4. Aturan Foto

- Sumber: kunjungan lapangan Dinas, kiriman desa wisata dengan izin tertulis,
  atau foto milik Pemkab.
- **Dilarang** mengambil foto dari Google Images, blog, atau media sosial
  orang lain tanpa izin.
- Format JPG/PNG/WebP, maksimal 3 MB, orientasi mendatar.
- Foto harus menggambarkan kondisi terkini, bukan kondisi 5 tahun lalu.

---

## 5. Penanganan Pengaduan

| Tahap | Batas waktu |
|---|---|
| Dibaca & status diubah ke Diproses | 2 hari kerja |
| Diteruskan ke pihak berwenang (bila di luar kewenangan Dinas) | 5 hari kerja |
| Status Selesai + catatan tindakan | 14 hari kerja |

Pengaduan yang menyangkut **keselamatan wisatawan** ditangani hari itu juga.

---

## 6. Indikator Keberhasilan

Dipantau lewat Dasbor admin dan halaman Statistik publik:

| Indikator | Target 6 bulan | Target 12 bulan |
|---|---|---|
| Destinasi terpublikasi | 50 | 80+ |
| Kecamatan tercakup | 15 dari 21 | 21 dari 21 |
| UMKM terverifikasi | 20 | 50 |
| Pembaruan konten per bulan | 4 | 6 |
| Pengaduan ditanggapi < 14 hari | 90% | 95% |

---

## 7. Bila Terjadi Pergantian Staf

Serah terima wajib mencakup:

1. Pembuatan akun baru untuk staf pengganti (**jangan** mewariskan akun lama —
   log aktivitas menjadi tidak dapat dipercaya).
2. Penonaktifan akun staf lama melalui menu Pengguna.
3. Penyerahan `panduan-admin.md` dan dokumen ini.
4. Pendampingan minimal 2 minggu.
5. Pembaruan daftar kontak kecamatan.
