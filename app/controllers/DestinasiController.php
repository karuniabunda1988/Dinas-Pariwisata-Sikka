<?php
declare(strict_types=1);

/** Direktori & detail destinasi (§9.3). */
final class DestinasiController extends Controller
{
    private const PER_HALAMAN = 12;

    public function index(): void
    {
        $this->arsip([
            'judul'     => Lang::inggris() ? 'All Destinations' : 'Semua Destinasi',
            'deskripsi' => Lang::inggris()
                ? 'Directory of tourist destinations in Sikka Regency, Flores, East Nusa Tenggara.'
                : 'Direktori destinasi wisata Kabupaten Sikka, Flores, Nusa Tenggara Timur.',
        ]);
    }

    /** Halaman arsip per kategori - SEO landing page (FR-DEST-01). */
    public function kategori(string $slug): void
    {
        $kat = Kategori::cariSlug($slug);
        if ($kat === null) {
            App::halaman404();
            return;
        }
        $nama = Lang::inggris() && $kat['nama_en'] !== '' ? $kat['nama_en'] : $kat['nama'];

        $this->arsip([
            'judul'     => Lang::inggris()
                ? $nama . ' in Sikka Regency'
                : 'Wisata ' . $nama . ' di Kabupaten Sikka',
            'deskripsi' => Lang::inggris()
                ? "Complete list of {$nama} destinations across the 21 districts of Sikka Regency, Flores - with map pins, opening hours and how to get there."
                : "Daftar lengkap destinasi {$nama} di 21 kecamatan Kabupaten Sikka, Flores - lengkap dengan titik peta, jam operasional, dan cara mencapainya.",
            'kategoriAktif' => $kat,
            'filterDasar'   => ['kategori' => $kat['id']],
        ]);
    }

    public function kecamatan(string $slug): void
    {
        $kec = Kecamatan::cariSlug($slug);
        if ($kec === null) {
            App::halaman404();
            return;
        }
        $this->arsip([
            'judul'     => Lang::inggris()
                ? 'Destinations in ' . $kec['nama'] . ' District'
                : 'Destinasi Wisata Kecamatan ' . $kec['nama'],
            'deskripsi' => Lang::inggris()
                ? "Tourist destinations in {$kec['nama']} District, Sikka Regency, Flores."
                : "Destinasi wisata di Kecamatan {$kec['nama']}, Kabupaten Sikka, Flores.",
            'kecamatanAktif' => $kec,
            'filterDasar'    => ['kecamatan' => $kec['id']],
        ]);
    }

    /** @param array<string,mixed> $konteks */
    private function arsip(array $konteks): void
    {
        $this->cachePublik(300);

        $halaman = max(1, (int) get_param('hal', '1'));
        $filter  = ($konteks['filterDasar'] ?? []) + [
            'cari'   => get_param('q'),
            'limit'  => self::PER_HALAMAN,
            'offset' => ($halaman - 1) * self::PER_HALAMAN,
            'urut'   => get_param('urut', 'unggulan'),
        ];

        // Filter tambahan dari querystring (bisa dikombinasi dengan filter dasar)
        if (($k = get_param('kategori')) !== '' && !isset($konteks['filterDasar']['kategori'])) {
            $filter['kategori'] = $k;
        }
        if (($kc = get_param('kecamatan')) !== '' && !isset($konteks['filterDasar']['kecamatan'])) {
            $filter['kecamatan'] = $kc;
        }

        $total = Destinasi::hitung($filter);

        $this->tampilkan('destinasi/arsip', array_merge($konteks, [
            'daftar'       => Destinasi::daftar($filter),
            'total'        => $total,
            'halaman'      => $halaman,
            'totalHalaman' => max(1, (int) ceil($total / self::PER_HALAMAN)),
            'kategoriList' => Kategori::denganJumlah(),
            'kecamatanList'=> Kecamatan::denganJumlah(),
        ]));
    }

    public function detail(string $slug): void
    {
        $d = Destinasi::cariSlug($slug);
        if ($d === null) {
            App::halaman404();
            return;
        }
        $this->cachePublik(300);

        $adaKoordinat = $d['latitude'] !== null && $d['longitude'] !== null;
        $terdekat     = $adaKoordinat
            ? Destinasi::terdekat((float) $d['latitude'], (float) $d['longitude'], 4, (int) $d['id'])
            : [];

        $this->tampilkan('destinasi/detail', [
            'judul'     => Lang::kolom($d, 'nama'),
            'deskripsi' => ringkas(Lang::kolom($d, 'deskripsi_singkat'), 160),
            'gambar'    => $d['foto_utama'] !== '' ? base_origin() . unggahan((string) $d['foto_utama']) : '',
            'kanonik'   => url_absolut('/destinasi/' . $d['slug']),
            'jsonld'    => $this->structuredData($d),
            'd'         => $d,
            'galeri'    => Destinasi::galeri((int) $d['id']),
            'umkm'      => Umkm::untukDestinasi((int) $d['id']),
            'terdekat'  => $terdekat,
            'event'     => EventWisata::daftar(['mendatang' => true, 'limit' => 3]),
            'ulasan'    => Ulasan::aktif() ? Ulasan::disetujui((int) $d['id']) : [],
            'rating'    => Ulasan::aktif() ? Ulasan::rataRata((int) $d['id']) : ['rata' => 0.0, 'jumlah' => 0],
            'ulasanAktif' => Ulasan::aktif(),
        ]);
    }

    /**
     * schema.org/TouristAttraction otomatis dari data destinasi (FR-ART-02)
     * - tanpa input manual tambahan dari admin.
     * @return array<string,mixed>
     */
    private function structuredData(array $d): array
    {
        $data = [
            '@context'    => 'https://schema.org',
            '@type'       => 'TouristAttraction',
            'name'        => Lang::kolom($d, 'nama'),
            'description' => ringkas(Lang::kolom($d, 'deskripsi_singkat'), 300),
            'url'         => url_absolut('/destinasi/' . $d['slug']),
            'address'     => array_filter([
                '@type'           => 'PostalAddress',
                'addressLocality' => $d['kecamatan_nama'] ?? null,
                'addressRegion'   => 'Nusa Tenggara Timur',
                'addressCountry'  => 'ID',
            ]),
        ];

        if ($d['latitude'] !== null && $d['longitude'] !== null) {
            $data['geo'] = [
                '@type'     => 'GeoCoordinates',
                'latitude'  => (float) $d['latitude'],
                'longitude' => (float) $d['longitude'],
            ];
        }
        if ((string) $d['foto_utama'] !== '') {
            $data['image'] = base_origin() . unggahan((string) $d['foto_utama']);
        }
        if ((string) $d['jam_operasional'] !== '') {
            $data['openingHours'] = $d['jam_operasional'];
        }
        if ((string) $d['kontak_telepon'] !== '') {
            $data['telephone'] = $d['kontak_telepon'];
        }
        if (Ulasan::aktif()) {
            $r = Ulasan::rataRata((int) $d['id']);
            if ($r['jumlah'] > 0) {
                $data['aggregateRating'] = [
                    '@type'       => 'AggregateRating',
                    'ratingValue' => $r['rata'],
                    'reviewCount' => $r['jumlah'],
                ];
            }
        }
        return $data;
    }

    /** Kirim ulasan publik (Fase 2 - hanya aktif bila dinyalakan admin). */
    public function kirimUlasan(string $slug): void
    {
        Csrf::wajib();

        $d = Destinasi::cariSlug($slug);
        if ($d === null) {
            App::halaman404();
            return;
        }
        if (!Ulasan::aktif()) {
            Session::flash('error', 'Fitur ulasan belum diaktifkan.');
            redirect('/destinasi/' . $slug);
        }
        if (!$this->lewatBatasLaju('ulasan', (int) App::config('rate_limit')['ulasan_per_jam'])) {
            Session::flash('error', 'Terlalu banyak kiriman dari perangkat ini. Coba lagi nanti.');
            redirect('/destinasi/' . $slug);
        }

        $nama     = post('nama');
        $komentar = post('komentar');
        $rating   = (int) post('rating', '5');

        // Honeypot sederhana - bot mengisi field tersembunyi ini.
        if (post('website') !== '') {
            redirect('/destinasi/' . $slug);
        }
        if ($nama === '' || mb_strlen($komentar) < 10) {
            Session::flash('error', 'Nama wajib diisi dan ulasan minimal 10 karakter.');
            Session::simpanInputLama($_POST);
            redirect('/destinasi/' . $slug);
        }

        Ulasan::kirim((int) $d['id'], $nama, $rating, $komentar);
        Session::flash('sukses', 'Terima kasih. Ulasan Anda akan tayang setelah diperiksa admin.');
        redirect('/destinasi/' . $slug);
    }
}
