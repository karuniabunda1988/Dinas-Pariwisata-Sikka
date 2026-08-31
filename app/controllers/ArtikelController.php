<?php
declare(strict_types=1);

final class ArtikelController extends Controller
{
    private const PER_HALAMAN = 9;

    public function index(): void
    {
        $this->cachePublik(300);

        $halaman = max(1, (int) get_param('hal', '1'));
        $filter  = [
            'kategori' => get_param('kategori'),
            'cari'     => get_param('q'),
            'limit'    => self::PER_HALAMAN,
            'offset'   => ($halaman - 1) * self::PER_HALAMAN,
        ];
        $total = Artikel::hitung(['status' => 'publish']);

        $this->tampilkan('artikel/index', [
            'judul'     => Lang::inggris() ? 'Articles & Travel Guides' : 'Artikel & Panduan Perjalanan',
            'deskripsi' => Lang::inggris()
                ? 'Travel guides, tips and official news from the Sikka Regency Tourism Office.'
                : 'Panduan perjalanan, tips wisata, dan berita resmi Dinas Pariwisata Kabupaten Sikka.',
            'daftar'       => Artikel::daftar($filter),
            'total'        => $total,
            'halaman'      => $halaman,
            'totalHalaman' => max(1, (int) ceil($total / self::PER_HALAMAN)),
        ]);
    }

    public function detail(string $slug): void
    {
        $a = Artikel::cariSlug($slug);
        if ($a === null) {
            App::halaman404();
            return;
        }
        $this->cachePublik(600);

        $jsonld = array_filter([
            '@context'      => 'https://schema.org',
            '@type'         => 'Article',
            'headline'      => $a['judul'],
            'description'   => ringkas((string) $a['ringkasan'], 300),
            'datePublished' => $a['published_at'] ?: $a['created_at'],
            'dateModified'  => $a['updated_at'],
            'url'           => url_absolut('/artikel/' . $a['slug']),
            'image'         => $a['gambar_sampul'] !== ''
                ? base_origin() . unggahan((string) $a['gambar_sampul'], 'artikel') : null,
            'author'        => [
                '@type' => 'Organization',
                'name'  => Pengaturan::ambil('instansi', 'Dinas Pariwisata Kabupaten Sikka'),
            ],
        ]);

        $this->tampilkan('artikel/detail', [
            'judul'     => (string) $a['judul'],
            'deskripsi' => ringkas((string) $a['ringkasan'], 160),
            'gambar'    => $a['gambar_sampul'] !== ''
                ? base_origin() . unggahan((string) $a['gambar_sampul'], 'artikel') : '',
            'kanonik'   => url_absolut('/artikel/' . $a['slug']),
            'jsonld'    => $jsonld,
            'a'         => $a,
            'lainnya'   => Artikel::daftar(['limit' => 3]),
        ]);
    }
}
