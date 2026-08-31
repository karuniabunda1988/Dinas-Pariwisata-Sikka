<?php
declare(strict_types=1);

/**
 * Multi-bahasa ID/EN (FR-I18N-01).
 * Bahasa disimpan di sesi; diganti lewat ?lang=en pada URL apa pun.
 */
final class Lang
{
    private static string $aktif = 'id';
    /** @var array<string,string> */
    private static array $kamus = [];

    public static function inisialisasi(): void
    {
        $tersedia = App::config('app')['bahasa_tersedia'];
        $default  = App::config('app')['bahasa_default'];

        $diminta = strtolower((string) ($_GET['lang'] ?? ''));
        if ($diminta !== '' && in_array($diminta, $tersedia, true)) {
            Session::set('bahasa', $diminta);
        }

        $aktif = (string) Session::get('bahasa', $default);
        self::$aktif = in_array($aktif, $tersedia, true) ? $aktif : $default;

        $berkas = dirname(__DIR__) . '/lang/' . self::$aktif . '.php';
        self::$kamus = is_file($berkas) ? (array) require $berkas : [];
    }

    public static function aktif(): string
    {
        return self::$aktif;
    }

    public static function inggris(): bool
    {
        return self::$aktif === 'en';
    }

    public static function teks(string $kunci, ?string $default = null): string
    {
        return self::$kamus[$kunci] ?? ($default ?? $kunci);
    }

    /**
     * Ambil kolom konten sesuai bahasa aktif, mundur ke bahasa Indonesia
     * bila terjemahan belum diisi admin - lebih baik menampilkan teks ID
     * daripada bidang kosong.
     */
    public static function kolom(array $baris, string $kolom): string
    {
        if (self::inggris()) {
            $en = trim((string) ($baris[$kolom . '_en'] ?? ''));
            if ($en !== '') {
                return $en;
            }
        }
        return (string) ($baris[$kolom] ?? '');
    }
}
