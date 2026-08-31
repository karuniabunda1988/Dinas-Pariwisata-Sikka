<?php
declare(strict_types=1);

/** Direktori UMKM, akomodasi & ekonomi kreatif (§9.5). */
final class UmkmController extends Controller
{
    private const PER_HALAMAN = 12;

    public function index(): void
    {
        $this->cachePublik(300);

        $halaman = max(1, (int) get_param('hal', '1'));
        $jenis   = get_param('jenis');
        if ($jenis !== '' && !array_key_exists($jenis, Umkm::JENIS)) {
            $jenis = '';
        }

        $filter = [
            'jenis'     => $jenis,
            'kecamatan' => get_param('kecamatan'),
            'cari'      => get_param('q'),
            'limit'     => self::PER_HALAMAN,
            'offset'    => ($halaman - 1) * self::PER_HALAMAN,
        ];
        $total = Umkm::hitung($filter);

        $this->tampilkan('umkm/index', [
            'judul'     => Lang::inggris()
                ? 'Local Businesses, Stays & Creative Economy'
                : 'UMKM, Akomodasi & Ekonomi Kreatif',
            'deskripsi' => Lang::inggris()
                ? 'Directory of verified local businesses, homestays, weavers and dive operators in Sikka Regency.'
                : 'Direktori UMKM, homestay, penenun, dan operator dive terverifikasi di Kabupaten Sikka.',
            'daftar'       => Umkm::daftar($filter),
            'total'        => $total,
            'halaman'      => $halaman,
            'totalHalaman' => max(1, (int) ceil($total / self::PER_HALAMAN)),
            'jenisAktif'   => $jenis,
            'kecamatanList'=> Kecamatan::semua(),
        ]);
    }

    public function detail(string $slug): void
    {
        $u = Umkm::cariSlug($slug);
        if ($u === null) {
            App::halaman404();
            return;
        }
        $this->cachePublik(300);

        // schema.org/LocalBusiness otomatis (FR-ART-02)
        $jsonld = array_filter([
            '@context'    => 'https://schema.org',
            '@type'       => 'LocalBusiness',
            'name'        => $u['nama'],
            'description' => ringkas((string) $u['deskripsi'], 300),
            'url'         => url_absolut('/umkm/' . $u['slug']),
            'telephone'   => $u['kontak_telepon'] ?: null,
            'image'       => $u['foto'] !== '' ? base_origin() . unggahan((string) $u['foto'], 'umkm') : null,
            'address'     => array_filter([
                '@type'           => 'PostalAddress',
                'streetAddress'   => $u['alamat'] ?: null,
                'addressLocality' => $u['kecamatan_nama'] ?: null,
                'addressRegion'   => 'Nusa Tenggara Timur',
                'addressCountry'  => 'ID',
            ]),
        ]);

        if ($u['latitude'] !== null && $u['longitude'] !== null) {
            $jsonld['geo'] = [
                '@type'     => 'GeoCoordinates',
                'latitude'  => (float) $u['latitude'],
                'longitude' => (float) $u['longitude'],
            ];
        }

        $this->tampilkan('umkm/detail', [
            'judul'     => (string) $u['nama'],
            'deskripsi' => ringkas((string) $u['deskripsi'], 160),
            'kanonik'   => url_absolut('/umkm/' . $u['slug']),
            'jsonld'    => $jsonld,
            'u'         => $u,
            'serupa'    => Umkm::daftar(['jenis' => $u['jenis'], 'limit' => 4]),
        ]);
    }
}
