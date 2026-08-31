<?php
declare(strict_types=1);

/**
 * Driver notifikasi bertingkat (PRD §13.1 / FR-SVC-01).
 *
 *   1. Gateway WhatsApp pihak ketiga  - dipakai bila URL+token terisi
 *   2. Tautan wa.me                    - disiapkan agar admin bisa klik manual
 *   3. Gagal senyap ke log aktivitas   - selalu berhasil
 *
 * ATURAN UTAMA: kegagalan notifikasi TIDAK PERNAH menggagalkan alur inti.
 * Pemanggil menyimpan data lebih dulu, baru memanggil kelas ini.
 */
final class Notifier
{
    /**
     * @return string Tingkat yang akhirnya berhasil: gateway|wa_link|log
     */
    public static function kirim(string $pesan, string $konteks = 'umum'): string
    {
        $tujuan = trim((string) Pengaturan::ambil('wa_notifikasi', ''));
        $gwUrl  = trim((string) Pengaturan::ambil('wa_gateway_url', ''));
        $gwToken= trim((string) Pengaturan::ambil('wa_gateway_token', ''));

        // Tingkat 1 - gateway
        if ($gwUrl !== '' && $tujuan !== '') {
            try {
                if (self::viaGateway($gwUrl, $gwToken, $tujuan, $pesan)) {
                    self::catat('gateway', $konteks, 'Notifikasi terkirim via gateway WhatsApp');
                    return 'gateway';
                }
            } catch (Throwable $e) {
                error_log('[Notifier] Gateway gagal: ' . $e->getMessage());
            }
        }

        // Tingkat 2 - tautan wa.me siap klik (disimpan di log agar admin
        // dapat menindaklanjuti manual dari panel).
        if ($tujuan !== '') {
            $tautan = self::tautanWa($tujuan, $pesan);
            self::catat('wa_link', $konteks, 'Notifikasi disiapkan sebagai tautan wa.me: ' . $tautan);
            return 'wa_link';
        }

        // Tingkat 3 - gagal senyap ke log
        self::catat('log', $konteks, 'Nomor WA notifikasi belum diatur; pesan disimpan di log: ' . ringkas($pesan, 250));
        return 'log';
    }

    public static function tautanWa(string $nomor, string $pesan): string
    {
        $nomor = nomor_wa($nomor);
        return 'https://wa.me/' . $nomor . '?text=' . rawurlencode($pesan);
    }

    private static function viaGateway(string $url, string $token, string $tujuan, string $pesan): bool
    {
        $payload = json_encode(
            ['target' => $tujuan, 'message' => $pesan],
            JSON_UNESCAPED_UNICODE
        );

        // cURL tersedia default di XAMPP maupun cPanel; bila tidak, mundur
        // ke tingkat berikutnya tanpa error.
        if (!function_exists('curl_init')) {
            return false;
        }

        $ch = curl_init($url);
        if ($ch === false) {
            return false;
        }
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 8,   // jangan menahan permintaan pengguna
            CURLOPT_CONNECTTIMEOUT => 4,
            CURLOPT_HTTPHEADER     => array_filter([
                'Content-Type: application/json',
                $token !== '' ? 'Authorization: Bearer ' . $token : null,
            ]),
        ]);
        $hasil = curl_exec($ch);
        $kode  = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $hasil !== false && $kode >= 200 && $kode < 300;
    }

    private static function catat(string $tingkat, string $konteks, string $keterangan): void
    {
        try {
            LogAktivitas::catat(
                'notifikasi',
                $konteks,
                null,
                '[' . $tingkat . '] ' . ringkas($keterangan, 380)
            );
        } catch (Throwable $e) {
            // Tingkat terakhir: error_log server. Tidak pernah melempar.
            error_log('[Notifier] ' . $tingkat . ' | ' . $konteks . ' | ' . $keterangan);
        }
    }
}
