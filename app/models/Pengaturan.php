<?php
declare(strict_types=1);

/** Pengaturan situs, pola key-value (§11). Dimuat sekali per permintaan. */
final class Pengaturan
{
    /** @var array<string,string>|null */
    private static ?array $cache = null;

    /** @return array<string,string> */
    public static function semua(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }
        $data = [];
        try {
            foreach (Database::all('SELECT kunci, nilai FROM pengaturan') as $baris) {
                $data[$baris['kunci']] = (string) $baris['nilai'];
            }
        } catch (Throwable $e) {
            error_log('[Pengaturan] ' . $e->getMessage());
        }
        return self::$cache = $data;
    }

    public static function ambil(string $kunci, string $default = ''): string
    {
        $nilai = self::semua()[$kunci] ?? '';
        return $nilai !== '' ? $nilai : $default;
    }

    public static function simpan(string $kunci, string $nilai): void
    {
        Database::run(
            'INSERT INTO pengaturan (kunci, nilai) VALUES (:k, :v)
             ON DUPLICATE KEY UPDATE nilai = VALUES(nilai)',
            ['k' => $kunci, 'v' => $nilai]
        );
        self::$cache = null;
    }

    /** @return array<int,array<string,mixed>> */
    public static function daftar(): array
    {
        return Database::all('SELECT * FROM pengaturan ORDER BY kunci');
    }
}
