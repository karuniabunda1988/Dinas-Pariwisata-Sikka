<?php
/**
 * Konfigurasi aplikasi.
 *
 * Untuk produksi (cPanel), JANGAN mengubah berkas ini. Salin
 * config.local.example.php menjadi config.local.php dan isi kredensial di
 * sana - berkas itu diabaikan git sehingga kredensial tidak ikut ter-commit.
 */
declare(strict_types=1);

$config = [
    // --- Basis data (default: XAMPP) -----------------------------------
    'db' => [
        'host'    => 'localhost',
        'name'    => 'sikka_pariwisata',
        'user'    => 'root',
        'pass'    => '',
        'charset' => 'utf8mb4',
    ],

    // --- Aplikasi ------------------------------------------------------
    'app' => [
        // Kosongkan agar dideteksi otomatis (cocok untuk XAMPP di subfolder
        // htdocs maupun cPanel di document root). Isi manual bila situs
        // berada di belakang reverse proxy.
        'base_url'      => '',
        'nama'          => 'Pariwisata Kabupaten Sikka',
        'timezone'      => 'Asia/Makassar',   // WITA
        'bahasa_default'=> 'id',
        'bahasa_tersedia' => ['id', 'en'],
        'debug'         => true,              // Set false di produksi
        'hak_cipta'     => 'Karunia Bunda IT Training Center Maumere',
    ],

    // --- Peta (§10.1: Leaflet + OpenStreetMap, tanpa API key) ----------
    'peta' => [
        'tile_url'     => 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
        'tile_atribusi'=> '&copy; Kontributor <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        'lat_awal'     => -8.6199,
        'lng_awal'     => 122.2111,
        'zoom_awal'    => 10,
        'zoom_maks'    => 18,

        /*
         * Lapisan peta tambahan (FR-MAP-10, Fase 2).
         *
         * Seluruhnya tanpa API key dan tanpa akun berbayar, sejalan dengan
         * keputusan §10.1. Set 'aktif' => false untuk mematikan salah satu
         * lapisan tanpa mengubah kode.
         *
         * CATATAN LISENSI - PERIKSA SEBELUM GO-LIVE:
         * Lapisan citra satelit di bawah memakai layanan Esri yang lazim
         * dipakai bebas dengan atribusi, tetapi ketentuannya ditetapkan
         * penyedia dan dapat berubah. Bagian Hukum/Diskominfo sebaiknya
         * memastikan kesesuaiannya untuk situs instansi pemerintah, atau
         * menggantinya dengan langganan citra resmi/BIG bila diperlukan.
         * Mematikannya ('aktif' => false) tidak memengaruhi fitur lain.
         */
        'lapisan' => [
            [
                'nama'     => 'Peta Jalan',
                'nama_en'  => 'Street Map',
                'url'      => 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
                'atribusi' => '&copy; Kontributor <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                'zoom_maks'=> 19,
                'aktif'    => true,
                'bawaan'   => true,
            ],
            [
                // Berguna untuk pendaki Gunung Egon: menampilkan garis kontur.
                'nama'     => 'Topografi',
                'nama_en'  => 'Topographic',
                'url'      => 'https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png',
                'atribusi' => 'Peta: &copy; <a href="https://opentopomap.org">OpenTopoMap</a> (CC-BY-SA), data &copy; Kontributor OpenStreetMap',
                'zoom_maks'=> 17,
                'aktif'    => true,
                'bawaan'   => false,
            ],
            [
                'nama'     => 'Citra Satelit',
                'nama_en'  => 'Satellite',
                'url'      => 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
                'atribusi' => 'Citra &copy; Esri, Maxar, Earthstar Geographics',
                'zoom_maks'=> 18,
                'aktif'    => true,
                'bawaan'   => false,
            ],
        ],

        /*
         * Lapisan batas kecamatan (FR-MAP-10).
         *
         * Berkas GeoJSON TIDAK disertakan karena data batas wilayah resmi
         * harus berasal dari sumber sah - Badan Informasi Geospasial
         * (Ina-Geoportal) atau Bagian Pemerintahan Setda. Menggambar batas
         * kecamatan berdasarkan perkiraan akan menyesatkan dan berpotensi
         * menimbulkan persoalan administratif.
         *
         * Cara mengaktifkan: taruh berkas GeoJSON di public/data/ dengan nama
         * di bawah. Kendali lapisan akan memunculkan opsinya secara otomatis
         * hanya bila berkas itu ada.
         */
        'batas_kecamatan' => 'data/batas-kecamatan.geojson',
    ],

    // --- Unggah berkas -------------------------------------------------
    'upload' => [
        'maks_byte'   => 3 * 1024 * 1024,     // 3 MB - realistis untuk koneksi daerah
        'ekstensi'    => ['jpg', 'jpeg', 'png', 'webp'],
        'mime'        => ['image/jpeg', 'image/png', 'image/webp'],
    ],

    // --- Batas laju form publik (§12: cegah spam/abuse) ----------------
    'rate_limit' => [
        'pengaduan_per_jam' => 5,
        'ulasan_per_jam'    => 5,
    ],
];

$lokal = __DIR__ . '/config.local.php';
if (is_file($lokal)) {
    $override = require $lokal;
    if (is_array($override)) {
        foreach ($override as $bagian => $nilai) {
            $config[$bagian] = is_array($nilai) && isset($config[$bagian]) && is_array($config[$bagian])
                ? array_merge($config[$bagian], $nilai)
                : $nilai;
        }
    }
}

return $config;
