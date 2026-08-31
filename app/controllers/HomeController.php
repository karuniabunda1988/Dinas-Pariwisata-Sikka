<?php
declare(strict_types=1);

final class HomeController extends Controller
{
    public function index(): void
    {
        $this->cachePublik(300);

        $ringkasan = Destinasi::ringkasan();

        $this->tampilkan('home/index', [
            'judul'      => Pengaturan::ambil('nama_situs', 'Pariwisata Kabupaten Sikka'),
            'deskripsi'  => Lang::inggris()
                ? Pengaturan::ambil('tagline_en', 'Official tourism map and information of Sikka Regency, Flores.')
                : Pengaturan::ambil('tagline', 'Peta dan informasi resmi wisata Kabupaten Sikka, Flores.'),
            'unggulan'   => Destinasi::daftar([
                'unggulan' => true,
                'limit'    => 8,
                'urut'     => 'nama',
            ]),
            'eventDekat' => EventWisata::daftar(['mendatang' => true, 'limit' => 4]),
            'artikel'    => Artikel::daftar(['limit' => 3]),
            'kategori'   => Kategori::denganJumlah(),
            'statistik'  => [
                'destinasi_aktif'    => $ringkasan['aktif'],
                'destinasi_ter_pin'  => $ringkasan['ter_pin'],
                'kecamatan_tercakup' => Kecamatan::jumlahTercakup(),
                'kecamatan_total'    => count(Kecamatan::semua()),
                'umkm'               => Umkm::ringkasan()['terverifikasi'],
            ],
            // Peta ringkas beranda (FR-HOME-01) - dimuat langsung, bukan gambar statis
            'pinAwal'    => Destinasi::untukPeta([]),
        ]);
    }
}
