<?php
declare(strict_types=1);

/**
 * Autentikasi & otorisasi panel admin (FR-ADM-01: peran berjenjang).
 *
 *  super_admin   - akses penuh termasuk pengguna & pengaturan
 *  admin_konten  - kelola seluruh konten, tanpa kelola pengguna/pengaturan
 *  mitra         - hanya entri UMKM miliknya sendiri
 */
final class Auth
{
    public const PERAN = ['super_admin', 'admin_konten', 'mitra'];

    public static function coba(string $username, string $password): bool
    {
        $user = Database::one(
            'SELECT * FROM pengguna_admin WHERE username = :u LIMIT 1',
            ['u' => $username]
        );

        // Selalu jalankan verifikasi agar waktu respons tidak membocorkan
        // ada/tidaknya username.
        $hash = $user['password_hash'] ?? '$2y$12$invalidinvalidinvalidinvalidinvalidinvalidinvalidinvalidinv';
        $cocok = password_verify($password, $hash);

        if (!$cocok || $user === null || (int) $user['aktif'] !== 1) {
            return false;
        }

        if (password_needs_rehash($user['password_hash'], PASSWORD_BCRYPT)) {
            Database::run(
                'UPDATE pengguna_admin SET password_hash = :h WHERE id = :id',
                ['h' => password_hash($password, PASSWORD_BCRYPT), 'id' => $user['id']]
            );
        }

        Session::regenerasi();
        Session::set('user', [
            'id'    => (int) $user['id'],
            'nama'  => $user['nama'],
            'username' => $user['username'],
            'peran' => $user['peran'],
        ]);
        Database::run(
            'UPDATE pengguna_admin SET login_terakhir = NOW() WHERE id = :id',
            ['id' => $user['id']]
        );
        LogAktivitas::catat('login', 'pengguna_admin', (int) $user['id'], 'Login berhasil');
        return true;
    }

    public static function keluar(): void
    {
        if (self::masuk()) {
            LogAktivitas::catat('logout', 'pengguna_admin', self::id(), 'Logout');
        }
        Session::musnahkan();
    }

    public static function masuk(): bool
    {
        return is_array(Session::get('user'));
    }

    /** @return array<string,mixed>|null */
    public static function pengguna(): ?array
    {
        $u = Session::get('user');
        return is_array($u) ? $u : null;
    }

    public static function id(): ?int
    {
        return self::pengguna()['id'] ?? null;
    }

    public static function peran(): string
    {
        return (string) (self::pengguna()['peran'] ?? '');
    }

    public static function adalah(string ...$peran): bool
    {
        return in_array(self::peran(), $peran, true);
    }

    /** Wajib login - kalau belum, lempar ke halaman login. */
    public static function wajibMasuk(): void
    {
        if (!self::masuk()) {
            Session::set('_tujuan_setelah_login', App::uri());
            Session::flash('error', 'Silakan masuk terlebih dahulu.');
            redirect('/admin/login');
        }
    }

    /** Wajib peran tertentu - kalau tidak, 403. */
    public static function wajibPeran(string ...$peran): void
    {
        self::wajibMasuk();
        if (!self::adalah(...$peran)) {
            http_response_code(403);
            (new Controller())->tampilkan('errors/403', ['judul' => 'Akses ditolak']);
            exit;
        }
    }

    /** Mitra hanya boleh menyentuh entri miliknya sendiri. */
    public static function bolehSuntingUmkm(array $umkm): bool
    {
        if (self::adalah('super_admin', 'admin_konten')) {
            return true;
        }
        return self::adalah('mitra')
            && (int) ($umkm['pemilik_user_id'] ?? 0) === (int) self::id();
    }
}
