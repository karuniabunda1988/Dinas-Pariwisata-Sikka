<?php
declare(strict_types=1);

/** Jejak audit setiap perubahan data (§11, FR-ADM-03). */
final class LogAktivitas
{
    public static function catat(
        string $aksi,
        string $entitas = '',
        ?int $entitasId = null,
        string $keterangan = ''
    ): void {
        try {
            $u = Auth::masuk() ? Auth::pengguna() : null;
            Database::run(
                'INSERT INTO log_aktivitas
                   (pengguna_id, nama_pengguna, aksi, entitas, entitas_id, keterangan, ip)
                 VALUES (:pid, :nama, :aksi, :entitas, :eid, :ket, :ip)',
                [
                    'pid'     => $u['id']   ?? null,
                    'nama'    => $u['nama'] ?? 'publik',
                    'aksi'    => $aksi,
                    'entitas' => $entitas,
                    'eid'     => $entitasId,
                    'ket'     => mb_substr($keterangan, 0, 400),
                    'ip'      => ip_klien(),
                ]
            );
        } catch (Throwable $e) {
            // Pencatatan log tidak boleh menggagalkan aksi utama.
            error_log('[LogAktivitas] ' . $e->getMessage());
        }
    }

    /** @return array<int,array<string,mixed>> */
    public static function terbaru(int $limit = 50, int $offset = 0): array
    {
        return Database::all(
            'SELECT * FROM log_aktivitas ORDER BY created_at DESC, id DESC LIMIT :l OFFSET :o',
            [':l' => $limit, ':o' => $offset]
        );
    }

    public static function jumlah(): int
    {
        return (int) Database::scalar('SELECT COUNT(*) FROM log_aktivitas');
    }

    /** Hitung pembaruan konten pada bulan berjalan (metrik §16 PRD). */
    public static function pembaruanBulanIni(): int
    {
        return (int) Database::scalar(
            "SELECT COUNT(*) FROM log_aktivitas
             WHERE aksi IN ('tambah','ubah')
               AND YEAR(created_at) = YEAR(CURDATE())
               AND MONTH(created_at) = MONTH(CURDATE())"
        );
    }
}
