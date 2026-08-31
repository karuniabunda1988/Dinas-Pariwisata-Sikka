<?php
declare(strict_types=1);

/** Escape HTML - dipakai di SELURUH view tanpa kecuali. */
function e(mixed $nilai): string
{
    return htmlspecialchars((string) $nilai, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Bangun URL absolut relatif terhadap base path aplikasi. */
function url(string $path = '/', array $query = []): string
{
    $path = '/' . ltrim($path, '/');
    $url  = App::basePath() . ($path === '/' ? '/' : rtrim($path, '/'));
    if ($query !== []) {
        $url .= '?' . http_build_query($query);
    }
    return $url === '' ? '/' : $url;
}

/**
 * URL berkas aset statis di folder public/.
 *
 * Awalan '/public' hanya ditambahkan bila document root memang berada di
 * atas folder public/ - lihat App::awalanPublic(). Menyamaratakannya membuat
 * seluruh CSS/JS 404 pada salah satu tata letak hosting.
 */
function aset(string $path): string
{
    return App::basePath() . App::awalanPublic() . '/' . ltrim($path, '/');
}

/** URL berkas unggahan; mengembalikan placeholder bila kosong. */
function unggahan(string $berkas, string $folder = 'destinasi'): string
{
    $berkas = trim($berkas);
    if ($berkas === '') {
        return aset('assets/img/placeholder.svg');
    }
    return aset('uploads/' . $folder . '/' . rawurlencode($berkas));
}

function base_origin(): string
{
    $skema = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host  = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
    return $skema . '://' . $host;
}

/** URL absolut penuh (untuk sitemap, Open Graph, structured data). */
function url_absolut(string $path = '/'): string
{
    return base_origin() . url($path);
}

function redirect(string $path): never
{
    header('Location: ' . (str_starts_with($path, 'http') ? $path : url($path)));
    exit;
}

/** Slug URL-aman dari teks bebas. */
function buat_slug(string $teks): string
{
    $teks = trim($teks);
    $teks = str_replace(['&'], ' dan ', $teks);
    $teks = preg_replace('/[^\p{L}\p{N}]+/u', '-', $teks) ?? '';
    $teks = strtolower(trim($teks, '-'));
    $teks = preg_replace('/-+/', '-', $teks) ?? '';
    return $teks === '' ? 'item-' . substr(bin2hex(random_bytes(4)), 0, 6) : $teks;
}

/** Potong teks pada batas kata. */
function ringkas(string $teks, int $panjang = 160): string
{
    $teks = trim(preg_replace('/\s+/', ' ', strip_tags($teks)) ?? '');
    if (mb_strlen($teks) <= $panjang) {
        return $teks;
    }
    $potong = mb_substr($teks, 0, $panjang);
    $spasi  = mb_strrpos($potong, ' ');
    return rtrim($spasi !== false ? mb_substr($potong, 0, $spasi) : $potong, ',.;:') . '...';
}

/** Ubah teks polos multi-paragraf menjadi HTML paragraf yang aman. */
function paragraf(string $teks): string
{
    $blok = preg_split('/\R{2,}/', trim($teks)) ?: [];
    $html = '';
    foreach ($blok as $p) {
        $p = trim($p);
        if ($p !== '') {
            $html .= '<p>' . nl2br(e($p)) . '</p>';
        }
    }
    return $html;
}

/** Format tanggal Indonesia/Inggris tanpa dependensi intl. */
function tanggal_lokal(?string $tanggal, bool $denganTahun = true): string
{
    if ($tanggal === null || $tanggal === '' || str_starts_with($tanggal, '0000')) {
        return '-';
    }
    $ts = strtotime($tanggal);
    if ($ts === false) {
        return '-';
    }
    $bulanId = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    if (Lang::inggris()) {
        return date($denganTahun ? 'j F Y' : 'j F', $ts);
    }
    $out = (int) date('j', $ts) . ' ' . $bulanId[(int) date('n', $ts)];
    return $denganTahun ? $out . ' ' . date('Y', $ts) : $out;
}

function rupiah(int|float|null $angka): string
{
    return 'Rp' . number_format((float) ($angka ?? 0), 0, ',', '.');
}

function angka(int|float|null $n): string
{
    return number_format((float) ($n ?? 0), 0, ',', '.');
}

/** Normalkan nomor telepon Indonesia ke format wa.me (62xxxx). */
function nomor_wa(string $nomor): string
{
    $bersih = preg_replace('/\D+/', '', $nomor) ?? '';
    if ($bersih === '') {
        return '';
    }
    if (str_starts_with($bersih, '0')) {
        $bersih = '62' . substr($bersih, 1);
    } elseif (!str_starts_with($bersih, '62')) {
        $bersih = '62' . ltrim($bersih, '+');
    }
    return $bersih;
}

/** Jarak haversine dalam kilometer - untuk "destinasi/UMKM terdekat". */
function jarak_km(float $lat1, float $lng1, float $lat2, float $lng2): float
{
    $R = 6371.0;
    $dLat = deg2rad($lat2 - $lat1);
    $dLng = deg2rad($lng2 - $lng1);
    $a = sin($dLat / 2) ** 2
       + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
    return $R * 2 * atan2(sqrt($a), sqrt(1 - $a));
}

/** Validasi koordinat berada dalam kotak wajar wilayah Kabupaten Sikka. */
function koordinat_masuk_akal(?float $lat, ?float $lng): bool
{
    if ($lat === null || $lng === null) {
        return false;
    }
    // Kotak longgar mencakup Palue di barat laut hingga Talibura di timur.
    return $lat >= -9.2 && $lat <= -8.0 && $lng >= 121.4 && $lng <= 122.9;
}

/**
 * Encode JSON yang AMAN untuk ditanam di dalam blok <script> pada HTML.
 *
 * Tanpa JSON_HEX_TAG, teks yang mengandung "</script>" - misalnya nama
 * destinasi yang diisi admin - akan menutup tag skrip lebih awal dan
 * membuka celah XSS tersimpan. Seluruh JSON yang dicetak di dalam
 * <script> WAJIB melewati fungsi ini, bukan json_encode() langsung.
 */
function json_skrip(mixed $data, bool $rapi = false): string
{
    $bendera = JSON_UNESCAPED_UNICODE
             | JSON_UNESCAPED_SLASHES
             | JSON_HEX_TAG      // < dan >  -> \u003C \u003E
             | JSON_HEX_AMP      // &        -> \u0026
             | JSON_HEX_APOS     // '        -> \u0027
             | JSON_HEX_QUOT;    // "        -> \u0022
    if ($rapi) {
        $bendera |= JSON_PRETTY_PRINT;
    }
    return (string) json_encode($data, $bendera);
}

/** Kirim respons JSON dan hentikan eksekusi. */
function json_respons(mixed $data, int $status = 200, int $cacheDetik = 0): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    if ($cacheDetik > 0) {
        header('Cache-Control: public, max-age=' . $cacheDetik);
    }
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function ip_klien(): string
{
    return substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45);
}

/** Nilai POST yang sudah dipangkas. */
function post(string $kunci, string $default = ''): string
{
    $v = $_POST[$kunci] ?? $default;
    return is_string($v) ? trim($v) : $default;
}

function get_param(string $kunci, string $default = ''): string
{
    $v = $_GET[$kunci] ?? $default;
    return is_string($v) ? trim($v) : $default;
}

/** Tandai menu navigasi yang sedang aktif. */
function menu_aktif(string $prefix): string
{
    $uri = App::uri();
    $cocok = $prefix === '/' ? $uri === '/' : str_starts_with($uri, $prefix);
    return $cocok ? ' active' : '';
}

/** URL untuk mengganti bahasa tanpa kehilangan halaman saat ini. */
function url_bahasa(string $kode): string
{
    $q = $_GET;
    $q['lang'] = $kode;
    return url(App::uri(), $q);
}
