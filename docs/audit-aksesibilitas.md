# Hasil Audit Aksesibilitas & Responsif
### Kriteria Penerimaan UAT §21 butir 6

Diuji otomatis dengan Chromium (Playwright) pada **11 halaman publik dan
admin**, dua lebar viewport: **360px** (ponsel kelas bawah) dan **1280px**
(desktop).

## Yang diperiksa

| Aspek | Metode |
|---|---|
| Kontras warna | Rumus rasio luminansi WCAG 2.1; ambang 4.5:1 teks normal, 3:1 teks besar |
| Scroll horizontal | `scrollWidth` vs `clientWidth` pada 360px |
| Alt text | Setiap `<img>` wajib punya atribut `alt` |
| Nama aksesibel | Setiap `<a>`/`<button>` wajib punya teks, `aria-label`, atau `title` |
| Hirarki heading | Tepat satu `<h1>` per halaman |
| Bahasa dokumen | Atribut `lang` pada `<html>` |
| Galat JavaScript | `pageerror` pada setiap halaman |

## Hasil akhir

**0 pelanggaran** pada seluruh kategori di kedua lebar viewport.

## Perbaikan yang dilakukan agar lolos

Audit awal menemukan 23 kegagalan kontras. Seluruhnya berasal dari tiga
sumber, dan diperbaiki dengan nilai yang dihitung ulang - bukan ditebak:

| Sumber | Sebelum | Sesudah |
|---|---|---|
| Chip kategori Budaya & Religi (emas `#ca8a04` di latar chip-nya) | 2.61:1 | 4.55:1 |
| Chip kategori Pantai & Bahari (`#0d9488`) | 3.25:1 | 5.45:1 |
| Warna tautan Bootstrap `#0d6efd` di latar `#f1f7f6` | 4.15:1 | 5.94:1 |
| Teks sekunder Bootstrap `#6c757d` di latar `#f8f9fa` | 4.45:1 | 5.78:1 |

Warna teks chip digelapkan lewat `color-mix(in srgb, var(--warna) 73%, black)`.
Angka 73% dipilih karena merupakan nilai tertinggi - artinya paling dekat
dengan warna kategori aslinya - yang masih meloloskan **seluruh enam
kategori** pada ambang 4.5:1. Identitas warna kategori (§10.5 PRD) tetap
terjaga sambil memenuhi WCAG AA.

Ukuran teks chip juga dinaikkan dari 0.72rem menjadi 0.78rem.

## Yang masih harus diuji manual

Audit otomatis tidak menggantikan pengujian manusia. Sebelum go-live:

- [ ] Navigasi keyboard penuh (Tab/Shift+Tab/Enter) pada form admin
- [ ] Uji dengan pembaca layar (NVDA atau TalkBack)
- [ ] Uji pada perangkat Android kelas menengah-bawah yang sebenarnya
- [ ] Uji oleh staf Dinas non-teknis (UAT §21 butir 4)

## Cara menjalankan ulang

Skrip audit tidak disertakan di repositori karena membutuhkan Node.js dan
Playwright yang tidak ada di lingkungan hosting cPanel. Untuk mengulang,
jalankan pemeriksa aksesibilitas mana pun (mis. axe DevTools atau Lighthouse
di Chrome) pada halaman-halaman di atas dengan lebar layar 360px.
