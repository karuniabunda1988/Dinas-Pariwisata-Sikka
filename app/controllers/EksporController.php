<?php
declare(strict_types=1);

/**
 * Ekspor titik destinasi sebagai GPX / KML (FR-MAP-11, Fase 2).
 *
 * Penting untuk dua persona di §6 PRD yang memakai perangkat luar situs:
 * pendaki Gunung Egon yang membawa GPS genggam, dan penyelam yang memakai
 * aplikasi navigasi laut. Keduanya membutuhkan berkas titik, bukan halaman
 * web - dan justru merekalah yang paling sering berada di luar jangkauan
 * sinyal ketika membutuhkan koordinatnya.
 */
final class EksporController extends Controller
{
    /** Batas wajar agar berkas tetap ringan dan proses tidak membebani hosting. */
    private const MAKS_TITIK = 500;

    public function gpx(): void
    {
        [$daftar, $namaBerkas] = $this->kumpulkanTitik('gpx');

        header('Content-Type: application/gpx+xml; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $namaBerkas . '.gpx"');

        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<gpx version="1.1" creator="Pariwisata Kabupaten Sikka" '
           . 'xmlns="http://www.topografix.com/GPX/1/1" '
           . 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" '
           . 'xsi:schemaLocation="http://www.topografix.com/GPX/1/1 '
           . 'http://www.topografix.com/GPX/1/1/gpx.xsd">' . "\n";

        echo "  <metadata>\n";
        echo '    <name>' . e($this->judulEkspor()) . "</name>\n";
        echo '    <desc>Titik destinasi wisata Kabupaten Sikka, Flores, Nusa Tenggara Timur. '
           . 'Sumber: ' . e(Pengaturan::ambil('instansi', 'Dinas Pariwisata Kabupaten Sikka')) . '.</desc>' . "\n";
        echo '    <time>' . gmdate('Y-m-d\TH:i:s\Z') . "</time>\n";
        echo "  </metadata>\n";

        foreach ($daftar as $d) {
            echo '  <wpt lat="' . e($d['latitude']) . '" lon="' . e($d['longitude']) . "\">\n";
            echo '    <name>' . e(Lang::kolom($d, 'nama')) . "</name>\n";
            echo '    <desc>' . e($this->keterangan($d)) . "</desc>\n";
            echo '    <link href="' . e(url_absolut('/destinasi/' . $d['slug'])) . "\"/>\n";
            echo '    <type>' . e($d['kategori_nama']) . "</type>\n";
            echo "  </wpt>\n";
        }
        echo '</gpx>';

        LogAktivitas::catat('ekspor', 'destinasi', null, 'Ekspor GPX (' . count($daftar) . ' titik)');
        exit;
    }

    public function kml(): void
    {
        [$daftar, $namaBerkas] = $this->kumpulkanTitik('kml');

        header('Content-Type: application/vnd.google-earth.kml+xml; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $namaBerkas . '.kml"');

        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<kml xmlns="http://www.opengis.net/kml/2.2">' . "\n";
        echo "  <Document>\n";
        echo '    <name>' . e($this->judulEkspor()) . "</name>\n";
        echo '    <description>' . e('Titik destinasi wisata Kabupaten Sikka, Flores, NTT.') . "</description>\n";

        // Satu gaya per kategori supaya warna pin di Google Earth konsisten
        // dengan warna di peta situs (§10.5).
        foreach (Kategori::semua() as $k) {
            echo '    <Style id="kat-' . e($k['slug']) . "\">\n";
            echo "      <IconStyle>\n";
            echo '        <color>' . e($this->warnaKml((string) $k['warna'])) . "</color>\n";
            echo "        <scale>1.1</scale>\n";
            echo "      </IconStyle>\n";
            echo "    </Style>\n";
        }

        foreach ($daftar as $d) {
            echo "    <Placemark>\n";
            echo '      <name>' . e(Lang::kolom($d, 'nama')) . "</name>\n";
            echo '      <description>' . e($this->keterangan($d)) . "</description>\n";
            echo '      <styleUrl>#kat-' . e($d['kategori_slug']) . "</styleUrl>\n";
            echo "      <Point>\n";
            // KML memakai urutan bujur,lintang - kebalikan dari GPX.
            echo '        <coordinates>' . e($d['longitude']) . ',' . e($d['latitude']) . ",0</coordinates>\n";
            echo "      </Point>\n";
            echo "    </Placemark>\n";
        }

        echo "  </Document>\n";
        echo '</kml>';

        LogAktivitas::catat('ekspor', 'destinasi', null, 'Ekspor KML (' . count($daftar) . ' titik)');
        exit;
    }

    /**
     * Ambil titik sesuai filter yang sama dengan halaman peta, sehingga
     * "apa yang terlihat di peta" itulah yang terunduh.
     *
     * @return array{0:array<int,array<string,mixed>>,1:string}
     */
    private function kumpulkanTitik(string $format): array
    {
        $filter = [
            'kategori'      => get_param('kategori'),
            'kecamatan'     => get_param('kecamatan'),
            'cari'          => get_param('q'),
            'ada_koordinat' => true,
            'limit'         => self::MAKS_TITIK,
            'urut'          => 'nama',
        ];

        $daftar = Destinasi::daftar($filter);

        $nama = 'wisata-sikka';
        if (get_param('kategori') !== '') {
            $nama .= '-' . buat_slug(get_param('kategori'));
        }
        if (get_param('kecamatan') !== '') {
            $nama .= '-' . buat_slug(get_param('kecamatan'));
        }

        return [$daftar, $nama . '-' . date('Ymd')];
    }

    private function judulEkspor(): string
    {
        $bagian = ['Destinasi Wisata Kabupaten Sikka'];
        if (($k = get_param('kategori')) !== '') {
            $kat = Kategori::cariSlug($k);
            if ($kat !== null) {
                $bagian[] = $kat['nama'];
            }
        }
        if (($kc = get_param('kecamatan')) !== '') {
            $kec = Kecamatan::cariSlug($kc);
            if ($kec !== null) {
                $bagian[] = 'Kecamatan ' . $kec['nama'];
            }
        }
        return implode(' - ', $bagian);
    }

    /** Keterangan ringkas yang berguna dibaca di perangkat GPS. */
    private function keterangan(array $d): string
    {
        $baris = [];
        $ringkas = trim(Lang::kolom($d, 'deskripsi_singkat'));
        if ($ringkas !== '') {
            $baris[] = ringkas($ringkas, 220);
        }
        if (($d['kecamatan_nama'] ?? '') !== '') {
            $baris[] = 'Kecamatan: ' . $d['kecamatan_nama'];
        }
        if ((string) $d['jam_operasional'] !== '') {
            $baris[] = 'Jam: ' . $d['jam_operasional'];
        }
        if ((string) $d['kisaran_tarif'] !== '') {
            $baris[] = 'Tarif: ' . $d['kisaran_tarif'];
        }
        if ((int) $d['perlu_verifikasi_lapangan'] === 1) {
            $baris[] = 'PERHATIAN: koordinat belum diverifikasi lapangan oleh Dinas Pariwisata. '
                     . 'Jangan dijadikan satu-satunya acuan navigasi.';
        }
        return implode("\n", $baris);
    }

    /**
     * Ubah warna heksadesimal (#rrggbb) ke format KML (aabbggrr).
     * KML memakai urutan byte terbalik dan alfa di depan.
     */
    private function warnaKml(string $hex): string
    {
        $hex = ltrim(trim($hex), '#');
        if (strlen($hex) !== 6 || !ctype_xdigit($hex)) {
            return 'ff0d7d74';
        }
        return 'ff' . substr($hex, 4, 2) . substr($hex, 2, 2) . substr($hex, 0, 2);
    }
}
