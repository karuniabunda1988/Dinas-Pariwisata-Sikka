<?php
declare(strict_types=1);

/**
 * API internal JSON (§10.4).
 * Dirancang agar bisa dipakai ulang aplikasi mobile Fase 2 tanpa perubahan
 * backend - karena itu bentuk payload dijaga stabil dan tidak bergantung
 * pada tampilan.
 */
final class ApiController extends Controller
{
    /** GET /api/destinasi?kategori=&kecamatan=&bbox=&q= */
    public function destinasi(): void
    {
        $filter = [
            'kategori'  => get_param('kategori'),
            'kecamatan' => get_param('kecamatan'),
            'cari'      => get_param('q'),
        ];

        $bbox = get_param('bbox');
        if ($bbox !== '') {
            $bagian = array_map('trim', explode(',', $bbox));
            if (count($bagian) === 4 && count(array_filter($bagian, 'is_numeric')) === 4) {
                $filter['bbox'] = array_map('floatval', $bagian);
            }
        }

        $pin = Destinasi::untukPeta($filter);

        json_respons([
            'meta' => [
                'jumlah'  => count($pin),
                'bahasa'  => Lang::aktif(),
                'filter'  => array_filter($filter, static fn($v) => $v !== '' && $v !== []),
            ],
            'data' => $pin,
        ], 200, 120);
    }

    /** GET /api/destinasi/{slug} - detail penuh untuk popup/halaman detail. */
    public function destinasiDetail(string $slug): void
    {
        $d = Destinasi::cariSlug($slug);
        if ($d === null) {
            json_respons(['error' => 'Destinasi tidak ditemukan'], 404);
        }

        $galeri = array_map(
            static fn(array $g) => [
                'url' => base_origin() . unggahan((string) $g['file']),
                'alt' => (string) $g['alt_text'],
            ],
            Destinasi::galeri((int) $d['id'])
        );

        $umkm = array_map(
            static fn(array $u) => [
                'nama'  => $u['nama'],
                'jenis' => Umkm::labelJenis((string) $u['jenis']),
                'url'   => url('/umkm/' . $u['slug']),
                'wa'    => $u['kontak_wa'] !== '' ? nomor_wa((string) $u['kontak_wa']) : '',
            ],
            Umkm::untukDestinasi((int) $d['id'])
        );

        json_respons([
            'data' => [
                'id'        => (int) $d['id'],
                'nama'      => Lang::kolom($d, 'nama'),
                'slug'      => $d['slug'],
                'kategori'  => [
                    'nama'  => $d['kategori_nama'],
                    'slug'  => $d['kategori_slug'],
                    'warna' => $d['kategori_warna'],
                ],
                'kecamatan' => $d['kecamatan_nama'],
                'lat'       => $d['latitude']  !== null ? (float) $d['latitude']  : null,
                'lng'       => $d['longitude'] !== null ? (float) $d['longitude'] : null,
                'deskripsi_singkat' => Lang::kolom($d, 'deskripsi_singkat'),
                'deskripsi_lengkap' => Lang::kolom($d, 'deskripsi_lengkap'),
                'jam_operasional'   => $d['jam_operasional'],
                'kisaran_tarif'     => $d['kisaran_tarif'],
                'fasilitas'   => array_values(array_filter(array_map('trim', explode(',', (string) $d['fasilitas'])))),
                'cara_mencapai' => $d['cara_mencapai'],
                'kontak'      => [
                    'nama'    => $d['kontak_nama'],
                    'telepon' => $d['kontak_telepon'],
                ],
                'jarak_dari_maumere_km' => $d['jarak_dari_maumere_km'] !== null ? (float) $d['jarak_dari_maumere_km'] : null,
                'waktu_tempuh_menit'    => $d['waktu_tempuh_menit'] !== null ? (int) $d['waktu_tempuh_menit'] : null,
                'foto_utama'  => (string) $d['foto_utama'] !== '' ? base_origin() . unggahan((string) $d['foto_utama']) : '',
                'galeri'      => $galeri,
                'umkm_terdekat' => $umkm,
                'perlu_verifikasi_lapangan' => (bool) $d['perlu_verifikasi_lapangan'],
                'terakhir_diperbarui'       => $d['updated_at'],
                'url'         => url_absolut('/destinasi/' . $d['slug']),
            ],
        ], 200, 120);
    }

    /** GET /api/kategori - data referensi untuk filter. */
    public function kategori(): void
    {
        $data = array_map(static fn(array $k) => [
            'id'     => (int) $k['id'],
            'nama'   => Lang::inggris() && $k['nama_en'] !== '' ? $k['nama_en'] : $k['nama'],
            'slug'   => $k['slug'],
            'warna'  => $k['warna'],
            'ikon'   => $k['ikon'],
            'jumlah' => (int) $k['jumlah'],
        ], Kategori::denganJumlah());

        json_respons(['data' => $data], 200, 600);
    }

    /** GET /api/kecamatan - 21 kecamatan + jumlah destinasi aktif. */
    public function kecamatan(): void
    {
        $data = array_map(static fn(array $k) => [
            'id'     => (int) $k['id'],
            'nama'   => $k['nama'],
            'slug'   => $k['slug'],
            'lat'    => $k['latitude']  !== null ? (float) $k['latitude']  : null,
            'lng'    => $k['longitude'] !== null ? (float) $k['longitude'] : null,
            'jumlah' => (int) $k['jumlah'],
        ], Kecamatan::denganJumlah());

        json_respons(['data' => $data], 200, 600);
    }

    /** GET /api/cari?q= - autocomplete peta (FR-MAP-04). */
    public function cari(): void
    {
        $q = get_param('q');
        $hasil = array_map(static fn(array $d) => [
            'nama'      => Lang::kolom($d, 'nama'),
            'slug'      => $d['slug'],
            'kecamatan' => $d['kecamatan_nama'] ?? '',
            'kategori'  => $d['kategori_nama'],
            'warna'     => $d['kategori_warna'],
            'lat'       => $d['latitude']  !== null ? (float) $d['latitude']  : null,
            'lng'       => $d['longitude'] !== null ? (float) $d['longitude'] : null,
        ], Destinasi::saran($q));

        json_respons(['data' => $hasil]);
    }

    /** GET /api/umkm?destinasi_id=&jenis= */
    public function umkm(): void
    {
        $filter = ['jenis' => get_param('jenis'), 'limit' => 60];
        if (($did = (int) get_param('destinasi_id')) > 0) {
            $filter['destinasi_id'] = $did;
        }

        $data = array_map(static fn(array $u) => [
            'nama'      => $u['nama'],
            'slug'      => $u['slug'],
            'jenis'     => $u['jenis'],
            'jenis_label' => Umkm::labelJenis((string) $u['jenis']),
            'alamat'    => $u['alamat'],
            'wa'        => $u['kontak_wa'] !== '' ? nomor_wa((string) $u['kontak_wa']) : '',
            'lat'       => $u['latitude']  !== null ? (float) $u['latitude']  : null,
            'lng'       => $u['longitude'] !== null ? (float) $u['longitude'] : null,
            'destinasi' => $u['destinasi_nama'] ?? null,
            'url'       => url('/umkm/' . $u['slug']),
        ], Umkm::daftar($filter));

        json_respons(['data' => $data], 200, 300);
    }
}
