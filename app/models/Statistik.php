<?php
declare(strict_types=1);

/**
 * Statistik kunjungan (FR-STAT-01).
 *
 * CATATAN PENTING: sistem TIDAK melacak wisatawan secara otomatis. Seluruh
 * angka di sini diinput manual oleh admin dari data yang sudah dikumpulkan
 * OPD, lengkap dengan kolom sumber_data agar dapat diaudit.
 */
final class Statistik
{
    /** @return array<int,array<string,mixed>> */
    public static function daftar(?int $tahun = null): array
    {
        $sql = 'SELECT s.*, k.nama AS kategori_nama, k.warna AS kategori_warna
                FROM statistik_kunjungan s
                LEFT JOIN kategori k ON k.id = s.kategori_id';
        $params = [];
        if ($tahun !== null) {
            $sql .= ' WHERE s.tahun = :t';
            $params['t'] = $tahun;
        }
        return Database::all($sql . ' ORDER BY s.tahun DESC, s.bulan DESC, k.urutan', $params);
    }

    /** Tren bulanan total (kategori_id NULL) untuk grafik publik. */
    /** @return array<int,array<string,mixed>> */
    public static function trenBulanan(int $bulanTerakhir = 24): array
    {
        return Database::all(
            'SELECT tahun, bulan, SUM(jumlah) AS jumlah
             FROM statistik_kunjungan
             GROUP BY tahun, bulan
             ORDER BY tahun DESC, bulan DESC
             LIMIT :limit',
            ['limit' => $bulanTerakhir]
        );
    }

    /** Sebaran per kategori destinasi untuk tahun tertentu. */
    /** @return array<int,array<string,mixed>> */
    public static function perKategori(int $tahun): array
    {
        return Database::all(
            'SELECT k.nama, k.warna, SUM(s.jumlah) AS jumlah
             FROM statistik_kunjungan s
             JOIN kategori k ON k.id = s.kategori_id
             WHERE s.tahun = :t
             GROUP BY k.id
             ORDER BY jumlah DESC',
            ['t' => $tahun]
        );
    }

    /** @return array<int,int> */
    public static function tahunTersedia(): array
    {
        $baris = Database::all('SELECT DISTINCT tahun FROM statistik_kunjungan ORDER BY tahun DESC');
        return array_map(static fn($b) => (int) $b['tahun'], $baris);
    }

    public static function totalTahun(int $tahun): int
    {
        return (int) Database::scalar(
            'SELECT COALESCE(SUM(jumlah), 0) FROM statistik_kunjungan WHERE tahun = :t',
            ['t' => $tahun]
        );
    }

    public static function simpan(int $tahun, int $bulan, ?int $kategoriId, int $jumlah, string $sumber): void
    {
        // UNIQUE(tahun, bulan, kategori_id) - perbarui bila periode sudah ada.
        Database::run(
            'INSERT INTO statistik_kunjungan (tahun, bulan, kategori_id, jumlah, sumber_data)
             VALUES (:t, :b, :k, :j, :s)
             ON DUPLICATE KEY UPDATE jumlah = VALUES(jumlah), sumber_data = VALUES(sumber_data)',
            ['t' => $tahun, 'b' => $bulan, 'k' => $kategoriId, 'j' => $jumlah, 's' => mb_substr($sumber, 0, 200)]
        );
    }

    public static function hapus(int $id): void
    {
        Database::run('DELETE FROM statistik_kunjungan WHERE id = :id', ['id' => $id]);
    }

    public static function adaData(): bool
    {
        return (int) Database::scalar('SELECT COUNT(*) FROM statistik_kunjungan') > 0;
    }
}
