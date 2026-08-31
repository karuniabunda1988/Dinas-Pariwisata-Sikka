<?php
declare(strict_types=1);

final class Session
{
    public static function mulai(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }
        $aman = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
             || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => App::basePath() === '' ? '/' : App::basePath() . '/',
            'httponly' => true,
            'secure'   => $aman,
            'samesite' => 'Lax',
        ]);
        session_name('SIKKAPAR');
        session_start();
    }

    public static function get(string $kunci, mixed $default = null): mixed
    {
        return $_SESSION[$kunci] ?? $default;
    }

    public static function set(string $kunci, mixed $nilai): void
    {
        $_SESSION[$kunci] = $nilai;
    }

    public static function hapus(string $kunci): void
    {
        unset($_SESSION[$kunci]);
    }

    /** Pesan sekali-tampil (flash message). */
    public static function flash(string $tipe, string $pesan): void
    {
        $_SESSION['_flash'][] = ['tipe' => $tipe, 'pesan' => $pesan];
    }

    /** @return array<int,array{tipe:string,pesan:string}> */
    public static function ambilFlash(): array
    {
        $data = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
        return $data;
    }

    /** Simpan input form agar tidak hilang saat validasi gagal. */
    public static function simpanInputLama(array $data): void
    {
        unset($data['csrf_token'], $data['password'], $data['password_ulang']);
        $_SESSION['_old'] = $data;
    }

    public static function inputLama(string $kunci, mixed $default = ''): mixed
    {
        return $_SESSION['_old'][$kunci] ?? $default;
    }

    public static function bersihkanInputLama(): void
    {
        unset($_SESSION['_old']);
    }

    public static function regenerasi(): void
    {
        session_regenerate_id(true);
    }

    public static function musnahkan(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }
}
