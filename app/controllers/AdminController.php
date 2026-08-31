<?php
declare(strict_types=1);

/**
 * Dasbor admin (FR-ADM-04): begitu login, admin langsung tahu apa yang
 * perlu dikerjakan.
 */
final class AdminController extends Controller
{
    public function dashboard(): void
    {
        Auth::wajibMasuk();

        // Mitra hanya melihat ringkasan entri miliknya sendiri.
        if (Auth::adalah('mitra')) {
            $this->tampilkanAdmin('admin/dashboard_mitra', [
                'judul' => 'Dasbor Mitra',
                'umkm'  => Umkm::daftar([
                    'status'     => 'semua',
                    'pemilik_id' => Auth::id(),
                ]),
            ]);
            return;
        }

        $ringkasan = Destinasi::ringkasan();

        $this->tampilkanAdmin('admin/dashboard', [
            'judul'            => 'Dasbor',
            'ringkasan'        => $ringkasan,
            'umkm'             => Umkm::ringkasan(),
            'pengaduanBaru'    => Pengaduan::jumlahBaru(),
            'ulasanMenunggu'   => Ulasan::aktif() ? Ulasan::jumlahMenunggu() : 0,
            'ulasanAktif'      => Ulasan::aktif(),
            'kontenBasi'       => Destinasi::kontenBasi(6, 10),
            'kecamatanTercakup'=> Kecamatan::jumlahTercakup(),
            'kecamatanTotal'   => count(Kecamatan::semua()),
            'pembaruanBulanIni'=> LogAktivitas::pembaruanBulanIni(),
            'logTerbaru'       => LogAktivitas::terbaru(8),
            'pengaduanTerbaru' => Pengaduan::daftar('baru', 5),
            // Target PRD §16 - ditampilkan agar admin melihat progres nyata
            'target'           => ['destinasi' => 80, 'umkm' => 50, 'pembaruan_bulanan' => 4],
        ]);
    }
}
