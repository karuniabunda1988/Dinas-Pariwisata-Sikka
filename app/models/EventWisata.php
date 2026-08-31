<?php
declare(strict_types=1);

/**
 * Kalender event & perayaan budaya (§9.4).
 * Nama kelas memakai sufiks "Wisata" karena "Event" adalah kata yang
 * rawan bentrok; tabelnya tetap `event`.
 */
final class EventWisata
{
    /** @return array<int,array<string,mixed>> */
    public static function daftar(array $filter = []): array
    {
        $where  = ['1 = 1'];
        $params = [];

        $status = $filter['status'] ?? 'aktif';
        if ($status !== 'semua') {
            $where[]          = 'e.status = :status';
            $params['status'] = $status;
        }
        if (!empty($filter['mendatang'])) {
            $where[] = 'COALESCE(e.tanggal_selesai, e.tanggal_mulai) >= CURDATE()';
        }
        if (!empty($filter['lampau'])) {
            $where[] = 'COALESCE(e.tanggal_selesai, e.tanggal_mulai) < CURDATE()';
        }
        if (!empty($filter['bulan']) && !empty($filter['tahun'])) {
            $where[]         = 'MONTH(e.tanggal_mulai) = :bulan AND YEAR(e.tanggal_mulai) = :tahun';
            $params['bulan'] = (int) $filter['bulan'];
            $params['tahun'] = (int) $filter['tahun'];
        }

        $urut = !empty($filter['lampau']) ? 'e.tanggal_mulai DESC' : 'e.tanggal_mulai ASC';

        $sql = "SELECT e.*, d.nama AS destinasi_nama, d.slug AS destinasi_slug,
                       d.latitude AS destinasi_lat, d.longitude AS destinasi_lng
                FROM event e
                LEFT JOIN destinasi d ON d.id = e.destinasi_terkait_id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY {$urut}";

        if (isset($filter['limit'])) {
            $sql .= ' LIMIT :limit OFFSET :offset';
            $params['limit']  = max(1, (int) $filter['limit']);
            $params['offset'] = max(0, (int) ($filter['offset'] ?? 0));
        }
        return Database::all($sql, $params);
    }

    /** @return array<string,mixed>|null */
    public static function cariSlug(string $slug, bool $semuaStatus = false): ?array
    {
        $sql = "SELECT e.*, d.nama AS destinasi_nama, d.slug AS destinasi_slug
                FROM event e
                LEFT JOIN destinasi d ON d.id = e.destinasi_terkait_id
                WHERE e.slug = :slug";
        if (!$semuaStatus) {
            $sql .= " AND e.status = 'aktif'";
        }
        return Database::one($sql . ' LIMIT 1', ['slug' => $slug]);
    }

    /** @return array<string,mixed>|null */
    public static function cariId(int $id): ?array
    {
        return Database::one('SELECT * FROM event WHERE id = :id LIMIT 1', ['id' => $id]);
    }

    /** Event untuk grid kalender bulanan: dikelompokkan per tanggal. */
    /** @return array<string,array<int,array<string,mixed>>> */
    public static function petaKalender(int $tahun, int $bulan): array
    {
        $awal  = sprintf('%04d-%02d-01', $tahun, $bulan);
        $akhir = date('Y-m-t', strtotime($awal));

        $baris = Database::all(
            "SELECT * FROM event
             WHERE status = 'aktif'
               AND tanggal_mulai <= :akhir
               AND COALESCE(tanggal_selesai, tanggal_mulai) >= :awal
             ORDER BY tanggal_mulai",
            ['awal' => $awal, 'akhir' => $akhir]
        );

        $peta = [];
        foreach ($baris as $e) {
            $mulai   = strtotime((string) $e['tanggal_mulai']);
            $selesai = strtotime((string) ($e['tanggal_selesai'] ?: $e['tanggal_mulai']));
            for ($t = $mulai; $t <= $selesai; $t = strtotime('+1 day', $t)) {
                $tgl = date('Y-m-d', $t);
                if ($tgl >= $awal && $tgl <= $akhir) {
                    $peta[$tgl][] = $e;
                }
            }
        }
        return $peta;
    }

    private const KOLOM = [
        'nama', 'slug', 'tanggal_mulai', 'tanggal_selesai', 'lokasi_teks',
        'destinasi_terkait_id', 'deskripsi', 'foto', 'status',
    ];

    public static function buat(array $data): int
    {
        $sql = 'INSERT INTO event (' . implode(', ', self::KOLOM) . ')
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
        Database::run("UPDATE event SET {$set} WHERE id = :id", $params);
    }

    public static function hapus(int $id): void
    {
        $e = self::cariId($id);
        if ($e !== null && (string) $e['foto'] !== '') {
            Upload::hapus((string) $e['foto'], 'event');
        }
        Database::run('DELETE FROM event WHERE id = :id', ['id' => $id]);
    }

    public static function slugUnik(string $slug, ?int $kecualiId = null): string
    {
        $dasar = $slug;
        $n = 2;
        while (true) {
            $ada = Database::scalar(
                'SELECT id FROM event WHERE slug = :s' . ($kecualiId ? ' AND id <> :id' : '') . ' LIMIT 1',
                $kecualiId ? ['s' => $slug, 'id' => $kecualiId] : ['s' => $slug]
            );
            if ($ada === null) {
                return $slug;
            }
            $slug = $dasar . '-' . $n++;
        }
    }
}
