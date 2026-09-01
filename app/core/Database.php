<?php
declare(strict_types=1);

/**
 * Pembungkus PDO tunggal (singleton) untuk seluruh aplikasi.
 * Menggunakan prepared statement di semua tempat - tidak ada string SQL
 * yang dirangkai dari input pengguna.
 */
final class Database
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $cfg = App::config('db');
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            $cfg['host'],
            $cfg['name'],
            $cfg['charset']
        );

        try {
            self::$pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            throw new RuntimeException(
                'Koneksi basis data gagal. Periksa app/config/config.local.php. ' .
                (App::config('app')['debug'] ? $e->getMessage() : ''),
                0,
                $e
            );
        }

        self::samakanZonaWaktu();

        return self::$pdo;
    }

    /**
     * Menyamakan zona waktu sesi MySQL dengan zona waktu aplikasi (WITA).
     *
     * Tanpa ini, PHP dan MySQL bisa hidup di zona berbeda - lazim terjadi
     * karena server hosting umumnya berjalan pada UTC. Akibatnya nyata dan
     * membingungkan: admin menekan "Publish" pada sebuah artikel, PHP
     * menyimpan published_at pukul 08.57 WITA, sementara NOW() milik MySQL
     * masih 00.57 UTC, sehingga syarat "published_at <= NOW()" gagal dan
     * artikel itu baru muncul di situs delapan jam kemudian tanpa penjelasan
     * apa pun. Hal serupa dapat mengenai setiap perbandingan tanggal.
     *
     * Dipakai offset numerik, bukan nama zona, karena tabel zona waktu MySQL
     * sering tidak dimuat di shared hosting.
     */
    private static function samakanZonaWaktu(): void
    {
        try {
            $zona   = (string) (App::config('app')['timezone'] ?? 'Asia/Makassar');
            $offset = (new DateTimeImmutable('now', new DateTimeZone($zona)))->format('P');

            $stmt = self::$pdo->prepare('SET time_zone = ?');
            $stmt->execute([$offset]);
        } catch (Throwable $e) {
            // Sebagian hosting melarang SET time_zone. Jangan menggagalkan
            // permintaan hanya karena ini - cukup catat agar bisa ditelusuri.
            error_log('[Database] Gagal menyamakan zona waktu MySQL: ' . $e->getMessage());
        }
    }

    /**
     * Menjalankan query dengan prepared statement.
     *
     * Integer di-bind sebagai PARAM_INT. Ini penting karena emulasi
     * prepared statement dimatikan: MySQL menolak LIMIT/OFFSET yang
     * dikirim sebagai string.
     *
     * @param array<string,mixed> $params
     */
    public static function run(string $sql, array $params = []): PDOStatement
    {
        $stmt = self::pdo()->prepare($sql);

        foreach ($params as $kunci => $nilai) {
            $nama = is_int($kunci) ? $kunci + 1 : (str_starts_with((string) $kunci, ':') ? $kunci : ':' . $kunci);
            $tipe = match (true) {
                is_int($nilai)  => PDO::PARAM_INT,
                is_bool($nilai) => PDO::PARAM_BOOL,
                is_null($nilai) => PDO::PARAM_NULL,
                default         => PDO::PARAM_STR,
            };
            $stmt->bindValue($nama, $nilai, $tipe);
        }

        $stmt->execute();
        return $stmt;
    }

    /** @return array<int,array<string,mixed>> */
    public static function all(string $sql, array $params = []): array
    {
        return self::run($sql, $params)->fetchAll();
    }

    /** @return array<string,mixed>|null */
    public static function one(string $sql, array $params = []): ?array
    {
        $row = self::run($sql, $params)->fetch();
        return $row === false ? null : $row;
    }

    public static function scalar(string $sql, array $params = []): mixed
    {
        $val = self::run($sql, $params)->fetchColumn();
        return $val === false ? null : $val;
    }

    public static function lastId(): int
    {
        return (int) self::pdo()->lastInsertId();
    }
}
