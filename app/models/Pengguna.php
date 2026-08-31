<?php
declare(strict_types=1);

/** Akun admin & mitra (FR-ADM-01). */
final class Pengguna
{
    public const LABEL_PERAN = [
        'super_admin'  => 'Super Admin',
        'admin_konten' => 'Admin Konten',
        'mitra'        => 'Mitra (UMKM/Desa Wisata)',
    ];

    /** @return array<int,array<string,mixed>> */
    public static function semua(): array
    {
        return Database::all(
            'SELECT id, nama, username, email, peran, aktif, login_terakhir, created_at
             FROM pengguna_admin ORDER BY peran, nama'
        );
    }

    /** @return array<string,mixed>|null */
    public static function cariId(int $id): ?array
    {
        return Database::one('SELECT * FROM pengguna_admin WHERE id = :id LIMIT 1', ['id' => $id]);
    }

    public static function usernameDipakai(string $username, ?int $kecualiId = null): bool
    {
        $sql = 'SELECT id FROM pengguna_admin WHERE username = :u';
        $params = ['u' => $username];
        if ($kecualiId !== null) {
            $sql .= ' AND id <> :id';
            $params['id'] = $kecualiId;
        }
        return Database::scalar($sql . ' LIMIT 1', $params) !== null;
    }

    public static function buat(string $nama, string $username, string $email, string $password, string $peran): int
    {
        Database::run(
            'INSERT INTO pengguna_admin (nama, username, email, password_hash, peran, aktif)
             VALUES (:n, :u, :e, :h, :p, 1)',
            [
                'n' => $nama,
                'u' => $username,
                'e' => $email,
                'h' => password_hash($password, PASSWORD_BCRYPT),
                'p' => in_array($peran, Auth::PERAN, true) ? $peran : 'admin_konten',
            ]
        );
        return Database::lastId();
    }

    public static function perbarui(int $id, string $nama, string $email, string $peran, bool $aktif): void
    {
        Database::run(
            'UPDATE pengguna_admin SET nama = :n, email = :e, peran = :p, aktif = :a WHERE id = :id',
            [
                'n'  => $nama,
                'e'  => $email,
                'p'  => in_array($peran, Auth::PERAN, true) ? $peran : 'admin_konten',
                'a'  => $aktif ? 1 : 0,
                'id' => $id,
            ]
        );
    }

    public static function gantiPassword(int $id, string $password): void
    {
        Database::run(
            'UPDATE pengguna_admin SET password_hash = :h WHERE id = :id',
            ['h' => password_hash($password, PASSWORD_BCRYPT), 'id' => $id]
        );
    }

    public static function hapus(int $id): void
    {
        Database::run('DELETE FROM pengguna_admin WHERE id = :id', ['id' => $id]);
    }

    public static function jumlahSuperAdmin(): int
    {
        return (int) Database::scalar(
            "SELECT COUNT(*) FROM pengguna_admin WHERE peran = 'super_admin' AND aktif = 1"
        );
    }
}
