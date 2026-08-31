<?php
declare(strict_types=1);

/**
 * Proteksi CSRF untuk seluruh form POST (publik maupun admin).
 */
final class Csrf
{
    public static function token(): string
    {
        $token = Session::get('_csrf');
        if (!is_string($token) || $token === '') {
            $token = bin2hex(random_bytes(32));
            Session::set('_csrf', $token);
        }
        return $token;
    }

    public static function field(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . e(self::token()) . '">';
    }

    public static function periksa(): bool
    {
        $dikirim = (string) ($_POST['csrf_token'] ?? '');
        $asli    = (string) Session::get('_csrf', '');
        return $asli !== '' && $dikirim !== '' && hash_equals($asli, $dikirim);
    }

    /** Hentikan permintaan bila token tidak valid. */
    public static function wajib(): void
    {
        if (!self::periksa()) {
            http_response_code(419);
            // Jangan buang isian pengguna. Sesi yang kedaluwarsa saat admin
            // mengetik form destinasi panjang tidak boleh berujung pada
            // hilangnya seluruh input - itu pengalaman yang membuat staf
            // enggan memakai sistem.
            Session::simpanInputLama($_POST);
            Session::flash('error', 'Sesi Anda kedaluwarsa. Isian Anda masih tersimpan di formulir - silakan periksa lalu kirim ulang.');
            $rujukan = (string) ($_SERVER['HTTP_REFERER'] ?? '');
            if ($rujukan !== '' && str_starts_with($rujukan, base_origin())) {
                header('Location: ' . $rujukan);
            } else {
                header('Location: ' . url('/'));
            }
            exit;
        }
    }
}
