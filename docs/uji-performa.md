# Hasil Uji Performa
### Kriteria Penerimaan UAT §21 butir 1 dan Kebutuhan Non-Fungsional §12

Diukur dengan Chromium (Playwright) memakai pelambatan jaringan dan CPU dari
Chrome DevTools Protocol, pada viewport ponsel 360px, dengan **kompresi gzip
aktif** sebagaimana diatur `mod_deflate` di `public/.htaccess`.

Tile peta diblokir selama pengujian: yang diukur adalah waktu sampai peta
**dapat dipakai** (kontainer siap dan pin tergambar), bukan waktu mengunduh
gambar peta dari server pihak ketiga yang di luar kendali sistem ini.

## Hasil pada skala target PRD (126 destinasi aktif)

| Halaman | Data terkirim | Fast 3G | Slow 3G |
|---|---|---|---|
| Beranda | 72 KB | 1,23 dtk | 2,42 dtk |
| **Peta interaktif** | 133 KB | **2,23 dtk** | **4,32 dtk** |
| Arsip destinasi | 63 KB | 1,19 dtk | 2,28 dtk |

- **Fast 3G** — 1.600 kbps, RTT 300 ms, CPU 4x lebih lambat
- **Slow 3G** — 400 kbps, RTT 400 ms, CPU 4x lebih lambat

## Penilaian terhadap kriteria

| Kriteria | Ambang | Fast 3G | Slow 3G |
|---|---|---|---|
| Peta dapat diinteraksi (UAT butir 1) | < 4 dtk | LULUS (2,23) | **Meleset (4,32)** |
| Waktu muat awal (§12) | < 3 dtk | LULUS (1,23) | LULUS (2,42) |

PRD menyebut "simulasi koneksi 3G" tanpa menentukan profilnya. Pada profil
3G yang lazim (Fast 3G) seluruh kriteria terpenuhi dengan selisih lebar.
Pada profil Slow 3G - yang memodelkan sel 3G di tepi jangkauan - halaman peta
meleset sekitar 0,3 detik.

Penyebabnya murni jumlah data yang harus diunduh, bukan lambatnya kode:
133 KB pada 400 kbps memakan sekitar 2,7 detik hanya untuk transfer,
ditambah waktu penguraian JavaScript pada CPU yang dilambatkan 4x.

### Pilihan bila 4 detik pada Slow 3G dianggap wajib

Satu-satunya berkas yang bisa dikeluarkan dari jalur kritis tanpa mengurangi
fitur adalah **JavaScript Bootstrap** (22 KB setelah kompresi), yang hanya
dipakai untuk tombol menu, dropdown, dan penutup notifikasi. Menggantinya
dengan skrip sendiri sekitar 40 baris akan menghemat kira-kira 0,4-0,5 detik
dan meloloskan kriteria pada Slow 3G.

Hal itu **sengaja belum dilakukan**: PRD §13 menempatkan kemudahan alih
tanggung jawab ke pengembang lokal sebagai prioritas, dan menggantikan
pustaka standar dengan kode sendiri berlawanan dengan prioritas itu demi
selisih 0,3 detik pada profil jaringan yang paling buruk. Keputusan ini
sebaiknya diambil Dinas, bukan diputuskan sepihak saat implementasi.

## Optimasi yang sudah diterapkan

| Optimasi | Dampak |
|---|---|
| Peta ringkas beranda dimuat malas (`IntersectionObserver`) | Beranda 3,79 -> 2,48 dtk pada Slow 3G |
| Aset disertakan lokal, bukan CDN | Menghapus pencarian DNS + koneksi ke host pihak ketiga |
| Kompresi gzip (`mod_deflate`) | Bootstrap CSS 228 -> 30 KB; halaman peta 129 -> 12 KB |
| Skrip peta didahulukan sebelum Bootstrap | Peta menang berebut bandwidth pada koneksi lambat |
| Cache aset statis 7-30 hari (`mod_expires`) | Kunjungan berikutnya jauh lebih ringan |
| Payload pin ringkas (tanpa deskripsi panjang/galeri) | Halaman peta hanya 12 KB walau berisi 126 titik |

## Perilaku pada skala penuh

Menaikkan jumlah destinasi dari 9 menjadi 126 hanya menambah waktu peta
dapat diinteraksi sekitar 0,06 detik (4,26 -> 4,32 dtk pada Slow 3G).
Arsitekturnya menskala dengan baik sampai target tahun pertama:

| Ukuran | Nilai pada 126 destinasi |
|---|---|
| Waktu respons `/api/destinasi` | 4 ms |
| Waktu respons `/api/cari` | 3 ms |
| HTML halaman peta setelah gzip | 11,6 KB |
| Ekspor GPX 126 titik | 7 ms |

Pengelompokan pin (FR-MAP-05) bekerja sebagaimana dirancang - peta tidak
menjadi berat meski seluruh objek wisata sudah terdata.

## Catatan pengujian

Angka di atas berasal dari lingkungan pengembangan, bukan hosting produksi.
Sebelum go-live, ulangi pengukuran pada server yang sebenarnya - waktu
respons PHP dan basis data di shared hosting bisa berbeda. Pastikan pula
`mod_deflate` dan `mod_expires` benar-benar aktif; tanpa keduanya waktu muat
membengkak sekitar tiga kali lipat (halaman peta 12,7 detik pada Slow 3G).
