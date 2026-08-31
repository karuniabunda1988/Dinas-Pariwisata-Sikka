<?php
declare(strict_types=1);

/**
 * Input manual statistik kunjungan (FR-STAT-01) + ekspor CSV (FR-STAT-02).
 */
final class AdminStatistikController extends Controller
{
    public function index(): void
    {
        Auth::wajibPeran('super_admin', 'admin_konten');

        $tahun = (int) (get_param('tahun') ?: date('Y'));
        $this->tampilkanAdmin('admin/statistik/index', [
            'judul'  => 'Input Statistik Kunjungan',
            'tahun'  => $tahun,
            'daftar' => Statistik::daftar($tahun),
            'tahunTersedia' => Statistik::tahunTersedia(),
        ]);
    }

    public function simpan(): void
    {
        Auth::wajibPeran('super_admin', 'admin_konten');
        Csrf::wajib();

        $tahun  = (int) post('tahun');
        $bulan  = (int) post('bulan');
        $jumlah = (int) post('jumlah');
        $kategoriId = ((int) post('kategori_id')) ?: null;
        $sumber = post('sumber_data');

        $galat = [];
        if ($tahun < 2000 || $tahun > 2100) {
            $galat[] = 'Tahun tidak valid.';
        }
        if ($bulan < 1 || $bulan > 12) {
            $galat[] = 'Bulan tidak valid.';
        }
        if ($jumlah < 0) {
            $galat[] = 'Jumlah kunjungan tidak boleh negatif.';
        }
        if ($kategoriId !== null && Kategori::cariId($kategoriId) === null) {
            $galat[] = 'Kategori tidak dikenali.';
        }
        // Sumber data wajib: angka statistik tanpa sumber tidak bisa diaudit
        // dan tidak layak dipakai untuk bahan rapat anggaran.
        if ($sumber === '') {
            $galat[] = 'Sumber data wajib diisi (mis. "Laporan bulanan UPT, Maret 2026").';
        }

        if ($galat !== []) {
            Session::flash('error', implode(' ', $galat));
            Session::simpanInputLama($_POST);
            redirect('/admin/statistik?tahun=' . $tahun);
        }

        Statistik::simpan($tahun, $bulan, $kategoriId, $jumlah, $sumber);
        LogAktivitas::catat('ubah', 'statistik', null, "Input statistik {$bulan}/{$tahun}: " . angka($jumlah));
        Session::flash('sukses', 'Data statistik tersimpan.');
        redirect('/admin/statistik?tahun=' . $tahun);
    }

    public function hapus(string $id): void
    {
        Auth::wajibPeran('super_admin', 'admin_konten');
        Csrf::wajib();

        Statistik::hapus((int) $id);
        LogAktivitas::catat('hapus', 'statistik', (int) $id, 'Menghapus baris statistik');
        Session::flash('sukses', 'Baris statistik dihapus.');
        redirect('/admin/statistik');
    }

    /** Ekspor CSV untuk bahan laporan DPRD (FR-STAT-02). */
    public function ekspor(): void
    {
        Auth::wajibPeran('super_admin', 'admin_konten');

        $tahun = get_param('tahun') !== '' ? (int) get_param('tahun') : null;
        $baris = Statistik::daftar($tahun);

        $namaBerkas = 'statistik-kunjungan-sikka' . ($tahun !== null ? "-{$tahun}" : '') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $namaBerkas . '"');

        $out = fopen('php://output', 'w');
        // BOM agar Excel di Windows membaca UTF-8 dengan benar.
        fwrite($out, "\xEF\xBB\xBF");

        // Parameter $escape ditulis eksplisit: PHP 8.4 memunculkan peringatan
        // deprecated bila dibiarkan default, dan peringatan itu ikut tercetak
        // ke dalam berkas CSV sehingga berkasnya rusak. String kosong berarti
        // tanpa karakter escape - sesuai RFC 4180 dan dibaca benar oleh Excel.
        fputcsv($out, ['Tahun', 'Bulan', 'Kategori', 'Jumlah Kunjungan', 'Sumber Data', 'Diinput Pada'], ',', '"', '');

        foreach ($baris as $b) {
            fputcsv($out, [
                $b['tahun'],
                $b['bulan'],
                $b['kategori_nama'] ?? 'Semua kategori',
                $b['jumlah'],
                $b['sumber_data'],
                $b['created_at'],
            ], ',', '"', '');
        }
        fclose($out);

        LogAktivitas::catat('ekspor', 'statistik', null, 'Ekspor CSV statistik' . ($tahun ? " tahun {$tahun}" : ''));
        exit;
    }
}
