<?php
declare(strict_types=1);

/**
 * Ulasan & rating publik (FR-DEST-03 - Fase 2).
 * Moderasi wajib sebelum tayang; tidak ada jalur publikasi otomatis.
 */
final class Ulasan
{
    public static function aktif(): bool
    {
        return Pengaturan::ambil('ulasan_aktif', '0') === '1';
    }

    public static function kirim(int $destinasiId, string $nama, int $rating, string $komentar): int
    {
        Database::run(
            'INSERT INTO ulasan (destinasi_id, nama_penulis, rating, komentar, status_moderasi)
             VALUES (:d, :n, :r, :k, :s)',
            [
                'd' => $destinasiId,
                'n' => mb_substr($nama, 0, 120),
                'r' => max(1, min(5, $rating)),
                'k' => mb_substr($komentar, 0, 2000),
                's' => 'menunggu',
            ]
        );
        return Database::lastId();
    }

    /** @return array<int,array<string,mixed>> */
    public static function disetujui(int $destinasiId, int $limit = 20): array
    {
        return Database::all(
            "SELECT * FROM ulasan
             WHERE destinasi_id = :d AND status_moderasi = 'disetujui'
             ORDER BY created_at DESC LIMIT :limit",
            ['d' => $destinasiId, 'limit' => $limit]
        );
    }

    /** @return array{rata:float,jumlah:int} */
    public static function rataRata(int $destinasiId): array
    {
        $b = Database::one(
            "SELECT AVG(rating) AS rata, COUNT(*) AS jumlah
             FROM ulasan WHERE destinasi_id = :d AND status_moderasi = 'disetujui'",
            ['d' => $destinasiId]
        ) ?? [];
        return [
            'rata'   => round((float) ($b['rata'] ?? 0), 1),
            'jumlah' => (int) ($b['jumlah'] ?? 0),
        ];
    }

    /** @return array<int,array<string,mixed>> */
    public static function daftarModerasi(string $status = 'menunggu', int $limit = 100): array
    {
        $sql = 'SELECT u.*, d.nama AS destinasi_nama, d.slug AS destinasi_slug
                FROM ulasan u JOIN destinasi d ON d.id = u.destinasi_id';
        $params = ['limit' => $limit];
        if ($status !== 'semua') {
            $sql .= ' WHERE u.status_moderasi = :s';
            $params['s'] = $status;
        }
        return Database::all($sql . ' ORDER BY u.created_at DESC LIMIT :limit', $params);
    }

    public static function moderasi(int $id, string $status): void
    {
        if (!in_array($status, ['menunggu', 'disetujui', 'ditolak'], true)) {
            return;
        }
        Database::run(
            'UPDATE ulasan SET status_moderasi = :s WHERE id = :id',
            ['s' => $status, 'id' => $id]
        );
    }

    public static function jumlahMenunggu(): int
    {
        return (int) Database::scalar(
            "SELECT COUNT(*) FROM ulasan WHERE status_moderasi = 'menunggu'"
        );
    }
}
