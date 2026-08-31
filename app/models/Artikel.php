<?php
declare(strict_types=1);

/** CMS artikel & panduan perjalanan (§9.6). */
final class Artikel
{
    /** @return array<int,array<string,mixed>> */
    public static function daftar(array $filter = []): array
    {
        $where  = ['1 = 1'];
        $params = [];

        $status = $filter['status'] ?? 'publish';
        if ($status !== 'semua') {
            $where[]          = 'a.status = :status';
            $params['status'] = $status;
        }
        if ($status === 'publish') {
            $where[] = '(a.published_at IS NULL OR a.published_at <= NOW())';
        }
        if (!empty($filter['kategori'])) {
            $where[]           = 'a.kategori = :kategori';
            $params['kategori']= (string) $filter['kategori'];
        }
        if (!empty($filter['cari'])) {
            $kata = '%' . str_replace(['%', '_'], ['\%', '\_'], (string) $filter['cari']) . '%';
            $where[]         = '(a.judul LIKE :cari1 OR a.ringkasan LIKE :cari2)';
            $params['cari1'] = $kata;
            $params['cari2'] = $kata;
        }

        $sql = 'SELECT a.*, p.nama AS penulis_nama
                FROM artikel a
                LEFT JOIN pengguna_admin p ON p.id = a.penulis_id
                WHERE ' . implode(' AND ', $where) . '
                ORDER BY COALESCE(a.published_at, a.created_at) DESC';

        if (isset($filter['limit'])) {
            $sql .= ' LIMIT :limit OFFSET :offset';
            $params['limit']  = max(1, (int) $filter['limit']);
            $params['offset'] = max(0, (int) ($filter['offset'] ?? 0));
        }
        return Database::all($sql, $params);
    }

    public static function hitung(array $filter = []): int
    {
        $status = $filter['status'] ?? 'publish';
        if ($status === 'semua') {
            return (int) Database::scalar('SELECT COUNT(*) FROM artikel');
        }
        return (int) Database::scalar(
            'SELECT COUNT(*) FROM artikel WHERE status = :s',
            ['s' => $status]
        );
    }

    /** @return array<string,mixed>|null */
    public static function cariSlug(string $slug, bool $semuaStatus = false): ?array
    {
        $sql = 'SELECT a.*, p.nama AS penulis_nama
                FROM artikel a
                LEFT JOIN pengguna_admin p ON p.id = a.penulis_id
                WHERE a.slug = :slug';
        if (!$semuaStatus) {
            $sql .= " AND a.status = 'publish' AND (a.published_at IS NULL OR a.published_at <= NOW())";
        }
        return Database::one($sql . ' LIMIT 1', ['slug' => $slug]);
    }

    /** @return array<string,mixed>|null */
    public static function cariId(int $id): ?array
    {
        return Database::one('SELECT * FROM artikel WHERE id = :id LIMIT 1', ['id' => $id]);
    }

    private const KOLOM = [
        'judul', 'slug', 'ringkasan', 'isi', 'gambar_sampul', 'kategori',
        'penulis_id', 'status', 'published_at',
    ];

    public static function buat(array $data): int
    {
        $sql = 'INSERT INTO artikel (' . implode(', ', self::KOLOM) . ')
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
        Database::run("UPDATE artikel SET {$set} WHERE id = :id", $params);
    }

    public static function hapus(int $id): void
    {
        $a = self::cariId($id);
        if ($a !== null && (string) $a['gambar_sampul'] !== '') {
            Upload::hapus((string) $a['gambar_sampul'], 'artikel');
        }
        Database::run('DELETE FROM artikel WHERE id = :id', ['id' => $id]);
    }

    public static function slugUnik(string $slug, ?int $kecualiId = null): string
    {
        $dasar = $slug;
        $n = 2;
        while (true) {
            $ada = Database::scalar(
                'SELECT id FROM artikel WHERE slug = :s' . ($kecualiId ? ' AND id <> :id' : '') . ' LIMIT 1',
                $kecualiId ? ['s' => $slug, 'id' => $kecualiId] : ['s' => $slug]
            );
            if ($ada === null) {
                return $slug;
            }
            $slug = $dasar . '-' . $n++;
        }
    }
}
