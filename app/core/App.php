<?php
declare(strict_types=1);

/**
 * Kernel aplikasi: autoload, konfigurasi, routing, dan dispatch.
 * Pola MVC sederhana tanpa Composer (§13 PRD).
 */
final class App
{
    /** @var array<string,mixed> */
    private static array $config = [];
    private static string $basePath = '';
    private static string $uri = '';

    /**
     * Awalan menuju folder public/ dari sudut pandang peramban.
     *
     * Dua tata letak sama-sama didukung:
     *  - Document root = akar proyek (XAMPP htdocs/nama-folder). Permintaan
     *    diteruskan .htaccess ke public/index.php, sehingga aset berada di
     *    "<base>/public/assets/...". Nilai ini menjadi '/public'.
     *  - Document root = folder public/ (lazim pada cPanel bila kode ditaruh
     *    di luar public_html). Aset berada di "<base>/assets/...", sehingga
     *    nilai ini kosong.
     *
     * Menyamaratakan keduanya membuat seluruh CSS/JS gagal dimuat pada salah
     * satu tata letak - situs tetap terbuka tetapi tampil tanpa gaya.
     */
    private static string $awalanPublic = '';

    /**
     * Tabel rute: [metode, pola, controller, aksi].
     * Pola memakai penanda {slug} / {id} yang diterjemahkan ke regex.
     * @var array<int,array{0:string,1:string,2:string,3:string}>
     */
    private const ROUTES = [
        ['GET',  '/',                       'HomeController',      'index'],
        ['GET',  '/peta',                   'PetaController',      'index'],

        ['GET',  '/destinasi',              'DestinasiController', 'index'],
        ['GET',  '/destinasi/kategori/{slug}', 'DestinasiController', 'kategori'],
        ['GET',  '/destinasi/kecamatan/{slug}','DestinasiController', 'kecamatan'],
        ['GET',  '/destinasi/{slug}',       'DestinasiController', 'detail'],
        ['POST', '/destinasi/{slug}/ulasan','DestinasiController', 'kirimUlasan'],

        ['GET',  '/event',                  'EventController',     'index'],
        ['GET',  '/event/{slug}',           'EventController',     'detail'],

        ['GET',  '/umkm',                   'UmkmController',      'index'],
        ['GET',  '/umkm/{slug}',            'UmkmController',      'detail'],

        ['GET',  '/artikel',                'ArtikelController',   'index'],
        ['GET',  '/artikel/{slug}',         'ArtikelController',   'detail'],

        ['GET',  '/statistik',              'StatistikController', 'index'],

        ['GET',  '/layanan',                'LayananController',   'index'],
        ['GET',  '/layanan/pengaduan',      'LayananController',   'formPengaduan'],
        ['POST', '/layanan/pengaduan',      'LayananController',   'kirimPengaduan'],
        ['GET',  '/layanan/pengaduan/terkirim', 'LayananController','pengaduanTerkirim'],

        ['GET',  '/profil',                 'ProfilController',    'index'],

        // --- API internal (§10.4) -------------------------------------
        ['GET',  '/api/destinasi',          'ApiController',       'destinasi'],
        ['GET',  '/api/destinasi/{slug}',   'ApiController',       'destinasiDetail'],
        ['GET',  '/api/kategori',           'ApiController',       'kategori'],
        ['GET',  '/api/kecamatan',          'ApiController',       'kecamatan'],
        ['GET',  '/api/cari',               'ApiController',       'cari'],
        ['GET',  '/api/umkm',               'ApiController',       'umkm'],

        // --- SEO -------------------------------------------------------
        ['GET',  '/sitemap.xml',            'SeoController',       'sitemap'],
        ['GET',  '/robots.txt',             'SeoController',       'robots'],

        // --- Autentikasi admin ----------------------------------------
        ['GET',  '/admin/login',            'AuthController',      'formLogin'],
        ['POST', '/admin/login',            'AuthController',      'login'],
        ['POST', '/admin/logout',           'AuthController',      'logout'],

        // --- Panel admin ----------------------------------------------
        ['GET',  '/admin',                  'AdminController',     'dashboard'],

        ['GET',  '/admin/destinasi',        'AdminDestinasiController', 'index'],
        ['GET',  '/admin/destinasi/baru',   'AdminDestinasiController', 'formBaru'],
        ['POST', '/admin/destinasi/baru',   'AdminDestinasiController', 'simpanBaru'],
        ['GET',  '/admin/destinasi/{id}/ubah', 'AdminDestinasiController', 'formUbah'],
        ['POST', '/admin/destinasi/{id}/ubah', 'AdminDestinasiController', 'simpanUbah'],
        ['POST', '/admin/destinasi/{id}/hapus', 'AdminDestinasiController', 'hapus'],
        ['POST', '/admin/destinasi/{id}/verifikasi', 'AdminDestinasiController', 'tandaiTerverifikasi'],
        ['POST', '/admin/destinasi/{id}/galeri', 'AdminDestinasiController', 'tambahGaleri'],
        ['POST', '/admin/galeri/{id}/hapus', 'AdminDestinasiController', 'hapusGaleri'],

        ['GET',  '/admin/umkm',             'AdminUmkmController', 'index'],
        ['GET',  '/admin/umkm/baru',        'AdminUmkmController', 'formBaru'],
        ['POST', '/admin/umkm/baru',        'AdminUmkmController', 'simpanBaru'],
        ['GET',  '/admin/umkm/{id}/ubah',   'AdminUmkmController', 'formUbah'],
        ['POST', '/admin/umkm/{id}/ubah',   'AdminUmkmController', 'simpanUbah'],
        ['POST', '/admin/umkm/{id}/hapus',  'AdminUmkmController', 'hapus'],
        ['POST', '/admin/umkm/{id}/verifikasi', 'AdminUmkmController', 'ubahVerifikasi'],

        ['GET',  '/admin/event',            'AdminEventController','index'],
        ['GET',  '/admin/event/baru',       'AdminEventController','formBaru'],
        ['POST', '/admin/event/baru',       'AdminEventController','simpanBaru'],
        ['GET',  '/admin/event/{id}/ubah',  'AdminEventController','formUbah'],
        ['POST', '/admin/event/{id}/ubah',  'AdminEventController','simpanUbah'],
        ['POST', '/admin/event/{id}/hapus', 'AdminEventController','hapus'],

        ['GET',  '/admin/artikel',          'AdminArtikelController','index'],
        ['GET',  '/admin/artikel/baru',     'AdminArtikelController','formBaru'],
        ['POST', '/admin/artikel/baru',     'AdminArtikelController','simpanBaru'],
        ['GET',  '/admin/artikel/{id}/ubah','AdminArtikelController','formUbah'],
        ['POST', '/admin/artikel/{id}/ubah','AdminArtikelController','simpanUbah'],
        ['POST', '/admin/artikel/{id}/hapus','AdminArtikelController','hapus'],

        ['GET',  '/admin/ulasan',           'AdminUlasanController','index'],
        ['POST', '/admin/ulasan/{id}/moderasi', 'AdminUlasanController','moderasi'],

        ['GET',  '/admin/pengaduan',        'AdminPengaduanController','index'],
        ['GET',  '/admin/pengaduan/{id}',   'AdminPengaduanController','detail'],
        ['POST', '/admin/pengaduan/{id}',   'AdminPengaduanController','perbarui'],

        ['GET',  '/admin/statistik',        'AdminStatistikController','index'],
        ['POST', '/admin/statistik',        'AdminStatistikController','simpan'],
        ['POST', '/admin/statistik/{id}/hapus', 'AdminStatistikController','hapus'],
        ['GET',  '/admin/statistik/ekspor', 'AdminStatistikController','ekspor'],

        ['GET',  '/admin/pengguna',         'AdminPenggunaController','index'],
        ['GET',  '/admin/pengguna/baru',    'AdminPenggunaController','formBaru'],
        ['POST', '/admin/pengguna/baru',    'AdminPenggunaController','simpanBaru'],
        ['GET',  '/admin/pengguna/{id}/ubah','AdminPenggunaController','formUbah'],
        ['POST', '/admin/pengguna/{id}/ubah','AdminPenggunaController','simpanUbah'],
        ['POST', '/admin/pengguna/{id}/hapus','AdminPenggunaController','hapus'],

        ['GET',  '/admin/pengaturan',       'AdminPengaturanController','index'],
        ['POST', '/admin/pengaturan',       'AdminPengaturanController','simpan'],

        ['GET',  '/admin/log',              'AdminLogController',  'index'],
    ];

    public static function jalankan(): void
    {
        self::$config = require dirname(__DIR__) . '/config/config.php';

        date_default_timezone_set(self::$config['app']['timezone']);
        self::daftarAutoload();
        self::tentukanBasePath();

        $debug = (bool) self::$config['app']['debug'];
        ini_set('display_errors', $debug ? '1' : '0');
        error_reporting($debug ? E_ALL : E_ALL & ~E_DEPRECATED & ~E_NOTICE);

        Session::mulai();
        Lang::inisialisasi();

        try {
            self::dispatch();
        } catch (Throwable $e) {
            self::tanganiError($e);
        }
    }

    private static function daftarAutoload(): void
    {
        spl_autoload_register(static function (string $kelas): void {
            foreach (['core', 'controllers', 'models'] as $folder) {
                $berkas = dirname(__DIR__) . "/{$folder}/{$kelas}.php";
                if (is_file($berkas)) {
                    require_once $berkas;
                    return;
                }
            }
        });
        require_once __DIR__ . '/helpers.php';
    }

    /**
     * Menentukan base path aplikasi secara otomatis sehingga sistem jalan
     * baik di XAMPP (htdocs/nama-folder) maupun di document root cPanel.
     */
    private static function tentukanBasePath(): void
    {
        $manual = trim((string) self::$config['app']['base_url']);
        if ($manual !== '') {
            self::$basePath = rtrim($manual, '/');
            $skrip = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
            if (str_contains(dirname($skrip), '/public')) {
                self::$awalanPublic = '/public';
            }
        } else {
            $script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
            $dir    = rtrim(dirname($script), '/');

            // Front controller dijalankan dari dalam /public: berarti document
            // root berada satu tingkat di atasnya, dan aset harus dirujuk
            // dengan awalan /public.
            if (str_ends_with($dir, '/public')) {
                $dir = substr($dir, 0, -strlen('/public'));
                self::$awalanPublic = '/public';
            } elseif ($dir === '/public') {
                $dir = '';
                self::$awalanPublic = '/public';
            }
            self::$basePath = $dir === '/' ? '' : $dir;
        }

        $uri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }
        $uri = rawurldecode($uri);
        if (self::$basePath !== '' && str_starts_with($uri, self::$basePath)) {
            $uri = substr($uri, strlen(self::$basePath));
        }
        $uri = '/' . trim($uri, '/');
        self::$uri = $uri;
    }

    private static function dispatch(): void
    {
        $metode = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
        if ($metode === 'HEAD') {
            $metode = 'GET';
        }
        $adaPolaCocokBedaMetode = false;

        foreach (self::ROUTES as [$rmetode, $pola, $controller, $aksi]) {
            $regex = self::polaKeRegex($pola);
            if (!preg_match($regex, self::$uri, $cocok)) {
                continue;
            }
            if ($rmetode !== $metode) {
                $adaPolaCocokBedaMetode = true;
                continue;
            }

            // Argumen aksi = capture group setelah full match
            $params = array_slice($cocok, 1);

            if (!class_exists($controller)) {
                throw new RuntimeException("Controller {$controller} tidak ditemukan.");
            }
            $obj = new $controller();
            if (!method_exists($obj, $aksi)) {
                throw new RuntimeException("Aksi {$controller}::{$aksi} tidak ditemukan.");
            }
            $obj->$aksi(...$params);
            return;
        }

        // Rute ada tetapi metodenya salah: 405, bukan 404. Membedakan
        // keduanya membantu saat menelusuri masalah integrasi.
        if ($adaPolaCocokBedaMetode) {
            self::halamanMetodeSalah();
            return;
        }
        self::halaman404();
    }

    private static function halamanMetodeSalah(): void
    {
        http_response_code(405);
        header('Allow: GET, POST');

        if (str_starts_with(self::$uri, '/api/')) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Metode HTTP tidak diizinkan untuk endpoint ini'], JSON_UNESCAPED_UNICODE);
            return;
        }
        (new Controller())->tampilkan('errors/404', [
            'judul'   => 'Halaman tidak dapat diakses dengan cara ini',
            'noindex' => true,
        ]);
    }

    private static function polaKeRegex(string $pola): string
    {
        $regex = preg_replace_callback(
            '/\{(\w+)\}/',
            static function (array $m): string {
                return $m[1] === 'id' ? '(\d+)' : '([A-Za-z0-9\-\_\.]+)';
            },
            $pola
        );
        return '#^' . $regex . '$#';
    }

    public static function halaman404(): void
    {
        if (http_response_code() === 200) {
            http_response_code(404);
        }
        if (str_starts_with(self::$uri, '/api/')) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Endpoint tidak ditemukan'], JSON_UNESCAPED_UNICODE);
            return;
        }
        $c = new Controller();
        $c->tampilkan('errors/404', ['judul' => 'Halaman tidak ditemukan']);
    }

    private static function tanganiError(Throwable $e): void
    {
        error_log('[Pariwisata Sikka] ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
        http_response_code(500);

        $debug = (bool) (self::$config['app']['debug'] ?? false);
        if (str_starts_with(self::$uri, '/api/')) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(
                ['error' => 'Terjadi kesalahan server'] + ($debug ? ['detail' => $e->getMessage()] : []),
                JSON_UNESCAPED_UNICODE
            );
            return;
        }

        try {
            $c = new Controller();
            $c->tampilkan('errors/500', [
                'judul' => 'Terjadi kesalahan',
                'pesan' => $debug ? $e->getMessage() . "\n" . $e->getTraceAsString() : null,
            ]);
        } catch (Throwable) {
            echo '<h1>Terjadi kesalahan server</h1>';
            if ($debug) {
                echo '<pre>' . htmlspecialchars((string) $e, ENT_QUOTES, 'UTF-8') . '</pre>';
            }
        }
    }

    /** @return array<string,mixed> */
    public static function config(?string $bagian = null): array
    {
        if ($bagian === null) {
            return self::$config;
        }
        return self::$config[$bagian] ?? [];
    }

    public static function basePath(): string
    {
        return self::$basePath;
    }

    /** Awalan URL menuju folder public/ ('' atau '/public'). Lihat $awalanPublic. */
    public static function awalanPublic(): string
    {
        return self::$awalanPublic;
    }

    public static function uri(): string
    {
        return self::$uri;
    }
}
