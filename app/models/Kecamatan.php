<?php
declare(strict_types=1);

final class Kecamatan
{
    /** @var array<int,array<string,mixed>>|null */
    private static ?array $cache = null;

    /** @return array<int,array<string,mixed>> */
    public static function semua(): array
    {
        return self::$cache ??= Database::all('SELECT * FROM kecamatan ORDER BY nama');
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
    public static function cariId(?int $id): ?array
    {
        if ($id === null) {
            return null;
        }
        foreach (self::semua() as $k) {
            if ((int) $k['id'] === $id) {
                return $k;
            }
        }
        return null;
    }

    public static function nama(?int $id): string
    {
        return (string) (self::cariId($id)['nama'] ?? '-');
    }

    /** @return array<int,array<string,mixed>> */
    public static function denganJumlah(): array
    {
        return Database::all(
            "SELECT kc.*, COUNT(d.id) AS jumlah
             FROM kecamatan kc
             LEFT JOIN destinasi d ON d.kecamatan_id = kc.id AND d.status = 'aktif'
             GROUP BY kc.id
             ORDER BY kc.nama"
        );
    }

    /** Berapa kecamatan yang sudah punya minimal satu destinasi aktif (§16). */
    public static function jumlahTercakup(): int
    {
        return (int) Database::scalar(
            "SELECT COUNT(DISTINCT kecamatan_id) FROM destinasi
             WHERE status = 'aktif' AND kecamatan_id IS NOT NULL"
        );
    }
}
