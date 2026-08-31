<?php
declare(strict_types=1);

/**
 * Pengaduan & masukan wisatawan (FR-SVC-01).
 * Data pribadi disimpan seminimal mungkin sesuai prinsip UU PDP 27/2022.
 */
final class Pengaduan
{
    public static function buat(
        string $isi,
        string $nama,
        string $kontak,
        ?int $destinasiId
    ): int {
        Database::run(
            'INSERT INTO pengaduan (isi, nama_pelapor, kontak_pelapor, destinasi_terkait_id, ip_pelapor)
             VALUES (:isi, :nama, :kontak, :did, :ip)',
            [
                'isi'    => mb_substr($isi, 0, 4000),
                'nama'   => mb_substr($nama, 0, 120),
                'kontak' => mb_substr($kontak, 0, 120),
                'did'    => $destinasiId,
                'ip'     => ip_klien(),
            ]
        );
        return Database::lastId();
    }

    public static function tandaiNotifikasi(int $id, string $tingkat): void
    {
        Database::run(
            'UPDATE pengaduan SET status_notifikasi = :s WHERE id = :id',
            ['s' => $tingkat, 'id' => $id]
        );
    }

    /** @return array<int,array<string,mixed>> */
    public static function daftar(string $status = 'semua', int $limit = 100, int $offset = 0): array
    {
        $sql = 'SELECT p.*, d.nama AS destinasi_nama
                FROM pengaduan p
                LEFT JOIN destinasi d ON d.id = p.destinasi_terkait_id';
        $params = ['limit' => $limit, 'offset' => $offset];
        if ($status !== 'semua') {
            $sql .= ' WHERE p.status_tindak_lanjut = :s';
            $params['s'] = $status;
        }
        return Database::all(
            $sql . ' ORDER BY p.created_at DESC LIMIT :limit OFFSET :offset',
            $params
        );
    }

    /** @return array<string,mixed>|null */
    public static function cariId(int $id): ?array
    {
        return Database::one(
            'SELECT p.*, d.nama AS destinasi_nama
             FROM pengaduan p
             LEFT JOIN destinasi d ON d.id = p.destinasi_terkait_id
             WHERE p.id = :id LIMIT 1',
            ['id' => $id]
        );
    }

    public static function perbarui(int $id, string $status, string $catatan): void
    {
        if (!in_array($status, ['baru', 'diproses', 'selesai'], true)) {
            $status = 'baru';
        }
        Database::run(
            'UPDATE pengaduan SET status_tindak_lanjut = :s, catatan_admin = :c WHERE id = :id',
            ['s' => $status, 'c' => mb_substr($catatan, 0, 4000), 'id' => $id]
        );
    }

    public static function jumlahBaru(): int
    {
        return (int) Database::scalar(
            "SELECT COUNT(*) FROM pengaduan WHERE status_tindak_lanjut = 'baru'"
        );
    }
}
