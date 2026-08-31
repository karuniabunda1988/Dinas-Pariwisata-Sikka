<?php
declare(strict_types=1);

final class EventController extends Controller
{
    public function index(): void
    {
        $this->cachePublik(300);

        $tahun = (int) (get_param('tahun') ?: date('Y'));
        $bulan = (int) (get_param('bulan') ?: date('n'));
        if ($bulan < 1 || $bulan > 12) {
            $bulan = (int) date('n');
        }
        if ($tahun < 2000 || $tahun > 2100) {
            $tahun = (int) date('Y');
        }

        $this->tampilkan('event/index', [
            'judul'     => Lang::inggris() ? 'Events & Culture' : 'Kalender Event & Budaya',
            'deskripsi' => Lang::inggris()
                ? 'Calendar of traditional ceremonies, festivals and cultural events in Sikka Regency, Flores.'
                : 'Kalender perayaan adat, festival, dan event budaya di Kabupaten Sikka, Flores.',
            'tahun'     => $tahun,
            'bulan'     => $bulan,
            'kalender'  => EventWisata::petaKalender($tahun, $bulan),
            'mendatang' => EventWisata::daftar(['mendatang' => true, 'limit' => 10]),
            'lampau'    => EventWisata::daftar(['lampau' => true, 'limit' => 5]),
        ]);
    }

    public function detail(string $slug): void
    {
        $e = EventWisata::cariSlug($slug);
        if ($e === null) {
            App::halaman404();
            return;
        }
        $this->cachePublik(300);

        $jsonld = [
            '@context'  => 'https://schema.org',
            '@type'     => 'Event',
            'name'      => $e['nama'],
            'startDate' => $e['tanggal_mulai'],
            'endDate'   => $e['tanggal_selesai'] ?: $e['tanggal_mulai'],
            'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
            'description' => ringkas((string) $e['deskripsi'], 300),
            'url'       => url_absolut('/event/' . $e['slug']),
            'location'  => [
                '@type'   => 'Place',
                'name'    => $e['lokasi_teks'] ?: ($e['destinasi_nama'] ?? 'Kabupaten Sikka'),
                'address' => [
                    '@type'          => 'PostalAddress',
                    'addressRegion'  => 'Nusa Tenggara Timur',
                    'addressCountry' => 'ID',
                ],
            ],
        ];

        $this->tampilkan('event/detail', [
            'judul'     => (string) $e['nama'],
            'deskripsi' => ringkas((string) $e['deskripsi'], 160),
            'gambar'    => $e['foto'] !== '' ? base_origin() . unggahan((string) $e['foto'], 'event') : '',
            'kanonik'   => url_absolut('/event/' . $e['slug']),
            'jsonld'    => $jsonld,
            'e'         => $e,
            'lainnya'   => EventWisata::daftar(['mendatang' => true, 'limit' => 4]),
        ]);
    }
}
