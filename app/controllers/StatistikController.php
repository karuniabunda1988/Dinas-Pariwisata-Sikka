<?php
declare(strict_types=1);

/** Dasbor statistik terbuka (§9.7). */
final class StatistikController extends Controller
{
    public function index(): void
    {
        $this->cachePublik(600);

        $tahunTersedia = Statistik::tahunTersedia();
        $tahun = (int) (get_param('tahun') ?: ($tahunTersedia[0] ?? date('Y')));

        $tren = array_reverse(Statistik::trenBulanan(24));

        $this->tampilkan('statistik/index', [
            'judul'     => Lang::inggris() ? 'Open Tourism Data' : 'Statistik Pariwisata Terbuka',
            'deskripsi' => Lang::inggris()
                ? 'Open dashboard of visitor trends and tourism sector data for Sikka Regency.'
                : 'Dasbor terbuka tren kunjungan dan data sektor pariwisata Kabupaten Sikka.',
            'tahun'         => $tahun,
            'tahunTersedia' => $tahunTersedia,
            'tren'          => $tren,
            'perKategori'   => Statistik::perKategori($tahun),
            'totalTahun'    => Statistik::totalTahun($tahun),
            'adaData'       => Statistik::adaData(),
            'ringkasan'     => Destinasi::ringkasan(),
            'umkm'          => Umkm::ringkasan(),
            'kecamatanTercakup' => Kecamatan::jumlahTercakup(),
            'kecamatanTotal'    => count(Kecamatan::semua()),
        ]);
    }
}
