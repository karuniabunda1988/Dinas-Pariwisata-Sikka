<?php
declare(strict_types=1);

/**
 * Model destinasi - sumber data seluruh pin peta (§10.3).
 */
final class Destinasi
{
    private const SELECT_KARTU = "d.id, d.nama, d.nama_en, d.slug, d.kategori_id, d.kecamatan_id,
        d.latitude, d.longitude, d.deskripsi_singkat, d.deskripsi_singkat_en, d.jam_operasional,
        d.kisaran_tarif, d.foto_utama, d.unggulan, d.status, d.jarak_dari_maumere_km,
        d.waktu_tempuh_menit, d.perlu_verifikasi_lapangan, d.updated_at,
        k.nama AS kategori_nama, k.nama_en AS kategori_nama_en, k.slug AS kategori_slug,
        k.warna AS kategori_warna, k.ikon AS kategori_ikon,
        kc.nama AS kecamatan_nama, kc.slug AS kecamatan_slug";

    /**
     * Daftar destinasi dengan filter. Dipakai peta, arsip, dan API.
     *
     * @param array{
     *   status?:string, kategori?:string|int, kecamatan?:string|int, cari?:string,
     *   unggulan?:bool, bbox?:array{0:float,1:float,2:float,3:float},
     *   ada_koordinat?:bool, limit?:int, offset?:int, urut?:string
     * } $filter
     * @return array<int,array<string,mixed>>
     */
    public static function daftar(array $filter = []): array
    {
        [$where, $params] = self::bangunFilter($filter);

        $urut = match ($filter['urut'] ?? 'nama') {
            'terbaru'  => 'd.created_at DESC',
            'diubah'   => 'd.updated_at DESC',
            'unggulan' => 'd.unggulan DESC, d.nama ASC',
            default    => 'd.nama ASC',
        };

        $sql = 'SELECT ' . self::SELECT_KARTU . '
                FROM destinasi d
                JOIN kategori k ON k.id = d.kategori_id
                LEFT JOIN kecamatan kc ON kc.id = d.kecamatan_id
                WHERE ' . implode(' AND ', $where) . '
                ORDER BY ' . $urut;

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
        unset($params['limit'], $params['offset']);

        return (int) Database::scalar(
            'SELECT COUNT(*) FROM destinasi d
             JOIN kategori k ON k.id = d.kategori_id
             LEFT JOIN kecamatan kc ON kc.id = d.kecamatan_id
             WHERE ' . implode(' AND ', $where),
            $params
        );
    }

    /**
     * @return array{0:array<int,string>,1:array<string,mixed>}
     */
    private static function bangunFilter(array $filter): array
    {
        $where  = ['1 = 1'];
        $params = [];

        $status = $filter['status'] ?? 'aktif';
        if ($status !== 'semua') {
            $where[]          = 'd.status = :status';
            $params['status'] = $status;
        }

        if (!empty($filter['kategori'])) {
            if (is_numeric($filter['kategori'])) {
                $where[]        = 'd.kategori_id = :kat';
                $params['kat']  = (int) $filter['kategori'];
            } else {
                $where[]        = 'k.slug = :kat';
                $params['kat']  = (string) $filter['kategori'];
            }
        }

        if (!empty($filter['kecamatan'])) {
            if (is_numeric($filter['kecamatan'])) {
                $where[]        = 'd.kecamatan_id = :kec';
                $params['kec']  = (int) $filter['kecamatan'];
            } else {
                $where[]        = 'kc.slug = :kec';
                $params['kec']  = (string) $filter['kecamatan'];
            }
        }

        if (!empty($filter['cari'])) {
            // Placeholder tidak diulang: sebagian versi PDO/MySQL menolak
            // nama placeholder yang sama dipakai lebih dari sekali.
            $where[] = '(d.nama LIKE :cari1 OR d.nama_en LIKE :cari2
                         OR d.deskripsi_singkat LIKE :cari3 OR kc.nama LIKE :cari4)';
            $kata = '%' . str_replace(['%', '_'], ['\%', '\_'], (string) $filter['cari']) . '%';
            $params['cari1'] = $kata;
            $params['cari2'] = $kata;
            $params['cari3'] = $kata;
            $params['cari4'] = $kata;
        }

        if (!empty($filter['unggulan'])) {
            $where[] = 'd.unggulan = 1';
        }

        if (!empty($filter['ada_koordinat'])) {
            $where[] = 'd.latitude IS NOT NULL AND d.longitude IS NOT NULL';
        }

        // Filter viewport peta (§10.4: GET /api/destinasi?bbox=)
        if (!empty($filter['bbox']) && count((array) $filter['bbox']) === 4) {
            [$minLng, $minLat, $maxLng, $maxLat] = array_map('floatval', $filter['bbox']);
            $where[] = 'd.latitude BETWEEN :minLat AND :maxLat
                        AND d.longitude BETWEEN :minLng AND :maxLng';
            $params['minLat'] = min($minLat, $maxLat);
            $params['maxLat'] = max($minLat, $maxLat);
            $params['minLng'] = min($minLng, $maxLng);
            $params['maxLng'] = max($minLng, $maxLng);
        }

        return [$where, $params];
    }

    /** @return array<string,mixed>|null */
    public static function cariSlug(string $slug, bool $termasukDraft = false): ?array
    {
        $sql = "SELECT d.*, k.nama AS kategori_nama, k.nama_en AS kategori_nama_en,
                       k.slug AS kategori_slug, k.warna AS kategori_warna, k.ikon AS kategori_ikon,
                       kc.nama AS kecamatan_nama, kc.slug AS kecamatan_slug
                FROM destinasi d
                JOIN kategori k ON k.id = d.kategori_id
                LEFT JOIN kecamatan kc ON kc.id = d.kecamatan_id
                WHERE d.slug = :slug";
        if (!$termasukDraft) {
            $sql .= " AND d.status = 'aktif'";
        }
        return Database::one($sql . ' LIMIT 1', ['slug' => $slug]);
    }

    /** @return array<string,mixed>|null */
    public static function cariId(int $id): ?array
    {
        return Database::one(
            "SELECT d.*, k.nama AS kategori_nama, k.slug AS kategori_slug, k.warna AS kategori_warna,
                    kc.nama AS kecamatan_nama
             FROM destinasi d
             JOIN kategori k ON k.id = d.kategori_id
             LEFT JOIN kecamatan kc ON kc.id = d.kecamatan_id
             WHERE d.id = :id LIMIT 1",
            ['id' => $id]
        );
    }

    /** @return array<int,array<string,mixed>> */
    public static function galeri(int $destinasiId): array
    {
        return Database::all(
            'SELECT * FROM destinasi_galeri WHERE destinasi_id = :id ORDER BY urutan, id',
            ['id' => $destinasiId]
        );
    }

    /**
     * Destinasi aktif terdekat dari sebuah titik (FR-DEST-02).
     * Perhitungan haversine di PHP - jumlah baris masih kecil (target 126),
     * jadi tidak perlu ekstensi spasial yang belum tentu ada di shared hosting.
     *
     * @return array<int,array<string,mixed>>
     */
    public static function terdekat(float $lat, float $lng, int $limit = 4, ?int $kecualiId = null): array
    {
        $semua = self::daftar(['ada_koordinat' => true]);
        $hasil = [];

        foreach ($semua as $d) {
            if ($kecualiId !== null && (int) $d['id'] === $kecualiId) {
                continue;
            }
            $d['jarak_km'] = round(
                jarak_km($lat, $lng, (float) $d['latitude'], (float) $d['longitude']),
                1
            );
            $hasil[] = $d;
        }

        usort($hasil, static fn($a, $b) => $a['jarak_km'] <=> $b['jarak_km']);
        return array_slice($hasil, 0, $limit);
    }

    /** Autocomplete pencarian peta (FR-MAP-04). */
    /** @return array<int,array<string,mixed>> */
    public static function saran(string $kata, int $limit = 8): array
    {
        $kata = trim($kata);
        if (mb_strlen($kata) < 2) {
            return [];
        }
        $like = '%' . str_replace(['%', '_'], ['\%', '\_'], $kata) . '%';

        return Database::all(
            "SELECT d.id, d.nama, d.nama_en, d.slug, d.latitude, d.longitude,
                    k.nama AS kategori_nama, k.warna AS kategori_warna,
                    kc.nama AS kecamatan_nama
             FROM destinasi d
             JOIN kategori k ON k.id = d.kategori_id
             LEFT JOIN kecamatan kc ON kc.id = d.kecamatan_id
             WHERE d.status = 'aktif'
               AND (d.nama LIKE :q1 OR d.nama_en LIKE :q2 OR kc.nama LIKE :q3)
             ORDER BY (d.nama LIKE :awal) DESC, d.nama
             LIMIT :limit",
            ['q1' => $like, 'q2' => $like, 'q3' => $like, 'awal' => $kata . '%', 'limit' => $limit]
        );
    }

    /**
     * Payload ringan untuk peta (§10.2: tanpa deskripsi panjang/galeri penuh).
     * @return array<int,array<string,mixed>>
     */
    public static function untukPeta(array $filter = []): array
    {
        $filter['ada_koordinat'] = true;
        $baris = self::daftar($filter);
        $pin   = [];

        foreach ($baris as $d) {
            $pin[] = [
                'id'        => (int) $d['id'],
                'nama'      => Lang::kolom($d, 'nama'),
                'slug'      => $d['slug'],
                'lat'       => (float) $d['latitude'],
                'lng'       => (float) $d['longitude'],
                'kategori'  => [
                    'id'    => (int) $d['kategori_id'],
                    'slug'  => $d['kategori_slug'],
                    'nama'  => Lang::inggris() && $d['kategori_nama_en'] !== ''
                                ? $d['kategori_nama_en'] : $d['kategori_nama'],
                    'warna' => $d['kategori_warna'],
                    'ikon'  => $d['kategori_ikon'],
                ],
                'kecamatan' => $d['kecamatan_nama'] ?? '',
                'ringkas'   => ringkas(Lang::kolom($d, 'deskripsi_singkat'), 130),
                'jam'       => $d['jam_operasional'],
                'tarif'     => $d['kisaran_tarif'],
                'foto'      => $d['foto_utama'] !== '' ? unggahan($d['foto_utama']) : '',
                'url'       => url('/destinasi/' . $d['slug']),
                'jarak_km'  => $d['jarak_dari_maumere_km'] !== null ? (float) $d['jarak_dari_maumere_km'] : null,
                'menit'     => $d['waktu_tempuh_menit'] !== null ? (int) $d['waktu_tempuh_menit'] : null,
            ];
        }
        return $pin;
    }

    // -----------------------------------------------------------------
    // Operasi tulis (panel admin)
    // -----------------------------------------------------------------

    /** @param array<string,mixed> $data */
    public static function buat(array $data): int
    {
        $kolom = self::kolomTulis();
        $sql = 'INSERT INTO destinasi (' . implode(', ', $kolom) . ', dibuat_oleh)
                VALUES (:' . implode(', :', $kolom) . ', :dibuat_oleh)';

        $params = [];
        foreach ($kolom as $k) {
            $params[$k] = $data[$k] ?? null;
        }
        $params['dibuat_oleh'] = Auth::id();

        Database::run($sql, $params);
        return Database::lastId();
    }

    /** @param array<string,mixed> $data */
    public static function perbarui(int $id, array $data): void
    {
        $kolom = self::kolomTulis();
        $set   = implode(', ', array_map(static fn($k) => "$k = :$k", $kolom));

        $params = ['id' => $id];
        foreach ($kolom as $k) {
            $params[$k] = $data[$k] ?? null;
        }
        Database::run("UPDATE destinasi SET {$set} WHERE id = :id", $params);
    }

    public static function hapus(int $id): void
    {
        foreach (self::galeri($id) as $g) {
            Upload::hapus((string) $g['file'], 'destinasi');
        }
        $d = self::cariId($id);
        if ($d !== null && (string) $d['foto_utama'] !== '') {
            Upload::hapus((string) $d['foto_utama'], 'destinasi');
        }
        Database::run('DELETE FROM destinasi WHERE id = :id', ['id' => $id]);
    }

    public static function tandaiTerverifikasi(int $id): void
    {
        Database::run(
            'UPDATE destinasi SET terakhir_diverifikasi = CURDATE(), perlu_verifikasi_lapangan = 0
             WHERE id = :id',
            ['id' => $id]
        );
    }

    /** @return array<int,string> */
    private static function kolomTulis(): array
    {
        return [
            'nama', 'nama_en', 'slug', 'kategori_id', 'kecamatan_id', 'latitude', 'longitude',
            'deskripsi_singkat', 'deskripsi_singkat_en', 'deskripsi_lengkap', 'deskripsi_lengkap_en',
            'jam_operasional', 'kisaran_tarif', 'fasilitas', 'cara_mencapai',
            'kontak_nama', 'kontak_telepon', 'jarak_dari_maumere_km', 'waktu_tempuh_menit',
            'foto_utama', 'unggulan', 'status', 'sumber_data', 'perlu_verifikasi_lapangan',
        ];
    }

    /** Pastikan slug unik; tambahkan sufiks angka bila bentrok. */
    public static function slugUnik(string $slug, ?int $kecualiId = null): string
    {
        $dasar = $slug;
        $n     = 2;
        while (true) {
            $ada = Database::scalar(
                'SELECT id FROM destinasi WHERE slug = :s' . ($kecualiId ? ' AND id <> :id' : '') . ' LIMIT 1',
                $kecualiId ? ['s' => $slug, 'id' => $kecualiId] : ['s' => $slug]
            );
            if ($ada === null) {
                return $slug;
            }
            $slug = $dasar . '-' . $n++;
        }
    }

    /**
     * Destinasi yang tidak diperbarui > N bulan (FR-ADM-03: konten basi).
     * @return array<int,array<string,mixed>>
     */
    public static function kontenBasi(int $bulan = 6, int $limit = 50): array
    {
        return Database::all(
            "SELECT d.id, d.nama, d.slug, d.status, d.updated_at, d.terakhir_diverifikasi
             FROM destinasi d
             WHERE (d.terakhir_diverifikasi IS NULL AND d.updated_at < DATE_SUB(NOW(), INTERVAL :b MONTH))
                OR (d.terakhir_diverifikasi IS NOT NULL
                    AND d.terakhir_diverifikasi < DATE_SUB(CURDATE(), INTERVAL :b2 MONTH))
             ORDER BY COALESCE(d.terakhir_diverifikasi, d.updated_at) ASC
             LIMIT :limit",
            ['b' => $bulan, 'b2' => $bulan, 'limit' => $limit]
        );
    }

    /** Ringkasan angka untuk beranda & dasbor. */
    /** @return array<string,int> */
    public static function ringkasan(): array
    {
        $baris = Database::one(
            "SELECT
               SUM(status = 'aktif')  AS aktif,
               SUM(status = 'draft')  AS draft,
               SUM(status = 'nonaktif') AS nonaktif,
               SUM(status = 'aktif' AND latitude IS NOT NULL) AS ter_pin,
               SUM(perlu_verifikasi_lapangan = 1) AS perlu_verifikasi,
               COUNT(*) AS total
             FROM destinasi"
        ) ?? [];

        return [
            'aktif'            => (int) ($baris['aktif'] ?? 0),
            'draft'            => (int) ($baris['draft'] ?? 0),
            'nonaktif'         => (int) ($baris['nonaktif'] ?? 0),
            'ter_pin'          => (int) ($baris['ter_pin'] ?? 0),
            'perlu_verifikasi' => (int) ($baris['perlu_verifikasi'] ?? 0),
            'total'            => (int) ($baris['total'] ?? 0),
        ];
    }
}
