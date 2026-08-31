<?php
declare(strict_types=1);

final class Kategori
{
    /** @var array<int,array<string,mixed>>|null */
    private static ?array $cache = null;

    /** @return array<int,array<string,mixed>> */
    public static function semua(): array
    {
        return self::$cache ??= Database::all('SELECT * FROM kategori ORDER BY urutan, nama');
    }

    /** @return array<string,mixed>|null */
    public static function cariSlug(string $slug): ?array
    {
        foreach (self::semua() as $k) {
            if ($k['slug'] === $slug) {
                return $k;
            }
        }
        return null;
    }

    /** @return array<string,mixed>|null */
    public static function cariId(int $id): ?array
    {
        foreach (self::semua() as $k) {
            if ((int) $k['id'] === $id) {
                return $k;
            }
        }
        return null;
    }

    public static function warna(int $id): string
    {
        return (string) (self::cariId($id)['warna'] ?? '#64748b');
    }

    /** Jumlah destinasi aktif per kategori - untuk halaman arsip & legenda. */
    /** @return array<int,array<string,mixed>> */
    public static function denganJumlah(): array
    {
        return Database::all(
            "SELECT k.*, COUNT(d.id) AS jumlah
             FROM kategori k
             LEFT JOIN destinasi d ON d.kategori_id = k.id AND d.status = 'aktif'
             GROUP BY k.id
             ORDER BY k.urutan, k.nama"
        );
    }
}
