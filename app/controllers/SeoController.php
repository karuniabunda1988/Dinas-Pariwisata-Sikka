<?php
declare(strict_types=1);

/** Sitemap & robots otomatis (§12 - kebutuhan SEO). */
final class SeoController extends Controller
{
    public function sitemap(): void
    {
        header('Content-Type: application/xml; charset=utf-8');
        header('Cache-Control: public, max-age=3600');

        $url = [];
        $tambah = static function (string $lokasi, ?string $ubah, string $prioritas, string $frekuensi) use (&$url): void {
            $url[] = [
                'loc'        => $lokasi,
                'lastmod'    => $ubah !== null ? date('Y-m-d', strtotime($ubah)) : null,
                'priority'   => $prioritas,
                'changefreq' => $frekuensi,
            ];
        };

        // Halaman statis
        $tambah(url_absolut('/'),          null, '1.0', 'daily');
        $tambah(url_absolut('/peta'),      null, '0.9', 'weekly');
        $tambah(url_absolut('/destinasi'), null, '0.9', 'weekly');
        $tambah(url_absolut('/event'),     null, '0.7', 'weekly');
        $tambah(url_absolut('/umkm'),      null, '0.7', 'weekly');
        $tambah(url_absolut('/artikel'),   null, '0.7', 'weekly');
        $tambah(url_absolut('/statistik'), null, '0.5', 'monthly');
        $tambah(url_absolut('/layanan'),   null, '0.5', 'monthly');
        $tambah(url_absolut('/profil'),    null, '0.4', 'monthly');

        // Halaman arsip kategori - landing page SEO (FR-DEST-01)
        foreach (Kategori::semua() as $k) {
            $tambah(url_absolut('/destinasi/kategori/' . $k['slug']), null, '0.8', 'weekly');
        }
        foreach (Kecamatan::semua() as $kc) {
            $tambah(url_absolut('/destinasi/kecamatan/' . $kc['slug']), null, '0.6', 'weekly');
        }

        foreach (Destinasi::daftar([]) as $d) {
            $tambah(url_absolut('/destinasi/' . $d['slug']), (string) $d['updated_at'], '0.8', 'monthly');
        }
        foreach (Umkm::daftar(['limit' => 500]) as $u) {
            $tambah(url_absolut('/umkm/' . $u['slug']), (string) $u['updated_at'], '0.5', 'monthly');
        }
        foreach (EventWisata::daftar(['limit' => 200]) as $e) {
            $tambah(url_absolut('/event/' . $e['slug']), (string) $e['updated_at'], '0.5', 'weekly');
        }
        foreach (Artikel::daftar(['limit' => 500]) as $a) {
            $tambah(url_absolut('/artikel/' . $a['slug']), (string) $a['updated_at'], '0.6', 'monthly');
        }

        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($url as $u) {
            echo "  <url>\n";
            echo '    <loc>' . e($u['loc']) . "</loc>\n";
            if ($u['lastmod'] !== null) {
                echo '    <lastmod>' . e($u['lastmod']) . "</lastmod>\n";
            }
            echo '    <changefreq>' . e($u['changefreq']) . "</changefreq>\n";
            echo '    <priority>' . e($u['priority']) . "</priority>\n";
            echo "  </url>\n";
        }
        echo '</urlset>';
    }

    public function robots(): void
    {
        header('Content-Type: text/plain; charset=utf-8');
        header('Cache-Control: public, max-age=86400');

        echo "User-agent: *\n";
        echo "Allow: /\n";
        echo "Disallow: /admin\n";          // Panel admin tidak diindeks (§8)
        echo "Disallow: /api/\n";
        echo "Disallow: /layanan/pengaduan/terkirim\n";
        echo "\n";
        echo 'Sitemap: ' . url_absolut('/sitemap.xml') . "\n";
    }
}
