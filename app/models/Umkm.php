<?php
declare(strict_types=1);

/** Direktori UMKM / akomodasi / ekonomi kreatif (§9.5). */
final class Umkm
{
    public const JENIS = [
        'kuliner'         => 'Kuliner',
        'kerajinan'       => 'Kerajinan & Tenun Ikat',
        'penginapan'      => 'Penginapan / Homestay',
        'biro_perjalanan' => 'Biro Perjalanan / Operator Dive',
    ];

    public const JENIS_EN = [
        'kuliner'         => 'Culinary',
        'kerajinan'       => 'Crafts & Ikat Weaving',
        'penginapan'      => 'Lodging / Homestay',
        'biro_perjalanan' => 'Tour Operator / Dive Operator',
    ];

    public static function labelJenis(string $jenis): string
    {
        $peta = Lang::inggris() ? self::JENIS_EN : self::JENIS;
        return $peta[$jenis] ?? $jenis;
    }

    /** @return array<int,array<string,mixed>> */
    public static function daftar(array $filter = []): array
    {
        [$where, $params] = self::bangunFilter($filter);

        $sql = "SELECT u.*, d.nama AS destinasi_nama, d.slug AS destinasi_slug,
                       kc.nama AS kecamatan_nama
                FROM umkm u
                LEFT JOIN destinasi d  ON d.id = u.destinasi_terdekat_id
                LEFT JOIN kecamatan kc ON kc.id = u.kecamatan_id
                WHERE " . implode(' AND ', $where) . '
                ORDER BY u.nama';

        if (isset($filter['limit'])) {
            $sql .= ' LIMIT :limit OFFSET :offset';
            $params['limit']  = max(1, (int) $filter['limit']);
            $params['offset'] = max(0, (int) ($filter['offset'] ?? 0));
        }
        return Database::all($sql, $params);
    }

    public static function hitung(array $filter = []): int
    {
        [$where, $params] = self::bangunFilter($filter);
        return (int) Database::scalar(
            'SELECT COUNT(*) FROM umkm u
             LEFT JOIN destinasi d  ON d.id = u.destinasi_terdekat_id
             LEFT JOIN kecamatan kc ON kc.id = u.kecamatan_id
             WHERE ' . implode(' AND ', $where),
            $params
        );
    }

    /** @return array{0:array<int,string>,1:array<string,mixed>} */
    private static function bangunFilter(array $filter): array
    {
        $where  = ['1 = 1'];
        $params = [];

        $status = $filter['status'] ?? 'terverifikasi';
        if ($status !== 'semua') {
            $where[]          = 'u.status_verifikasi = :status';
            $params['status'] = $status;
        }
        if (!empty($filter['jenis'])) {
            $where[]         = 'u.jenis = :jenis';
            $params['jenis'] = (string) $filter['jenis'];
        }
        if (!empty($filter['destinasi_id'])) {
            $where[]      = 'u.destinasi_terdekat_id = :did';
            $params['did']= (int) $filter['destinasi_id'];
        }
        if (!empty($filter['kecamatan'])) {
            $where[]        = 'kc.slug = :kec';
            $params['kec']  = (string) $filter['kecamatan'];
        }
        if (!empty($filter['pemilik_id'])) {
            $where[]         = 'u.pemilik_user_id = :pid';
            $params['pid']   = (int) $filter['pemilik_id'];
        }
        if (!empty($filter['cari'])) {
            $kata = '%' . str_replace(['%', '_'], ['\%', '\_'], (string) $filter['cari']) . '%';
            $where[]          = '(u.nama LIKE :cari1 OR u.deskripsi LIKE :cari2 OR u.alamat LIKE :cari3)';
            $params['cari1']  = $kata;
            $params['cari2']  = $kata;
            $params['cari3']  = $kata;
        }
        return [$where, $params];
    }

    /** @return array<string,mixed>|null */
    public static function cariSlug(string $slug, bool $semuaStatus = false): ?array
    {
        $sql = "SELECT u.*, d.nama AS destinasi_nama, d.slug AS destinasi_slug,
                       kc.nama AS kecamatan_nama
                FROM umkm u
                LEFT JOIN destinasi d  ON d.id = u.destinasi_terdekat_id
                LEFT JOIN kecamatan kc ON kc.id = u.kecamatan_id
                WHERE u.slug = :slug";
        if (!$semuaStatus) {
            $sql .= " AND u.status_verifikasi = 'terverifikasi'";
        }
        return Database::one($sql . ' LIMIT 1', ['slug' => $slug]);
    }

    /** @return array<string,mixed>|null */
    public static function cariId(int $id): ?array
    {
        return Database::one('SELECT * FROM umkm WHERE id = :id LIMIT 1', ['id' => $id]);
    }

    /** UMKM terverifikasi yang terhubung ke destinasi tertentu (FR-UMKM-02). */
    /** @return array<int,array<string,mixed>> */
    public static function untukDestinasi(int $destinasiId, int $limit = 6): array
    {
        return Database::all(
            "SELECT * FROM umkm
             WHERE destinasi_terdekat_id = :id AND status_verifikasi = 'terverifikasi'
             ORDER BY nama LIMIT :limit",
            ['id' => $destinasiId, 'limit' => $limit]
        );
    }

    private const KOLOM = [
        'nama', 'slug', 'jenis', 'deskripsi', 'alamat', 'kontak_telepon', 'kontak_wa',
        'latitude', 'longitude', 'kecamatan_id', 'destinasi_terdekat_id', 'foto',
        'status_verifikasi', 'pemilik_user_id',
    ];

    public static function buat(array $data): int
    {
        $sql = 'INSERT INTO umkm (' . implode(', ', self::KOLOM) . ')
                VALUES (:' . implode(', :', self::KOLOM) . ')';
        $params = [];
        foreach (self::KOLOM as $k) {
            $params[$k] = $data[$k] ?? null;
        }
        Database::run($sql, $params);
        return Database::lastId();
    }

    public static function perbarui(int $id, array $data): void
    {
        $set    = implode(', ', array_map(static fn($k) => "$k = :$k", self::KOLOM));
        $params = ['id' => $id];
        foreach (self::KOLOM as $k) {
            $params[$k] = $data[$k] ?? null;
        }
        Database::run("UPDATE umkm SET {$set} WHERE id = :id", $params);
    }

    public static function hapus(int $id): void
    {
        $u = self::cariId($id);
        if ($u !== null && (string) $u['foto'] !== '') {
            Upload::hapus((string) $u['foto'], 'umkm');
        }
        Database::run('DELETE FROM umkm WHERE id = :id', ['id' => $id]);
    }

    public static function ubahVerifikasi(int $id, string $status): void
    {
        if (!in_array($status, ['menunggu', 'terverifikasi', 'ditolak'], true)) {
            return;
        }
        Database::run(
            'UPDATE umkm SET status_verifikasi = :s WHERE id = :id',
            ['s' => $status, 'id' => $id]
        );
    }

    public static function slugUnik(string $slug, ?int $kecualiId = null): string
    {
        $dasar = $slug;
        $n = 2;
        while (true) {
            $ada = Database::scalar(
                'SELECT id FROM umkm WHERE slug = :s' . ($kecualiId ? ' AND id <> :id' : '') . ' LIMIT 1',
                $kecualiId ? ['s' => $slug, 'id' => $kecualiId] : ['s' => $slug]
            );
            if ($ada === null) {
                return $slug;
            }
            $slug = $dasar . '-' . $n++;
        }
    }

    /** @return array<string,int> */
    public static function ringkasan(): array
    {
        $b = Database::one(
            "SELECT SUM(status_verifikasi = 'terverifikasi') AS terverifikasi,
                    SUM(status_verifikasi = 'menunggu')      AS menunggu,
                    COUNT(*) AS total
             FROM umkm"
        ) ?? [];
        return [
            'terverifikasi' => (int) ($b['terverifikasi'] ?? 0),
            'menunggu'      => (int) ($b['menunggu'] ?? 0),
            'total'         => (int) ($b['total'] ?? 0),
        ];
    }
}
