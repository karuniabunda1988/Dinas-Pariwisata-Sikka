# Folder data geospasial

Folder ini menampung berkas GeoJSON opsional untuk lapisan tambahan peta
(FR-MAP-10). Berkas tidak disertakan dalam repositori.

## batas-kecamatan.geojson

Batas administratif 21 kecamatan Kabupaten Sikka.

**Data batas wilayah harus diambil dari sumber resmi**, bukan digambar
perkiraan:

- Badan Informasi Geospasial (Ina-Geoportal) — <https://tanahair.indonesia.go.id>
- Bagian Pemerintahan Setda Kabupaten Sikka
- Diskominfo Kabupaten Sikka

Setelah berkas diletakkan di sini dengan nama `batas-kecamatan.geojson`,
opsi "Batas Kecamatan" akan muncul otomatis pada kendali lapisan peta.
Tanpa berkas ini, opsi tersebut tidak ditampilkan sama sekali.

Format yang diharapkan: `FeatureCollection` berisi Polygon/MultiPolygon,
dengan properti `nama` (atau `NAMOBJ`/`WADMKC`) pada tiap fitur untuk label.

Disarankan menyederhanakan geometri (mis. dengan mapshaper.org) hingga di
bawah ~500 KB agar tetap ringan pada koneksi 3G.
