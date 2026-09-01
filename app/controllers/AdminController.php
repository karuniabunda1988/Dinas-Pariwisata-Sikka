<?php
declare(strict_types=1);

/**
 * Dasbor admin (FR-ADM-04): begitu login, admin langsung tahu apa yang
 * perlu dikerjakan.
 */
final class AdminController extends Controller
{
    /**
     * Peringatan konfigurasi yang berbahaya bila terbawa ke produksi.
     *
     * Nilai bawaan config.php sengaja ramah untuk pengembangan di XAMPP,
     * tetapi kalau ikut terpasang di server publik dampaknya nyata: pesan
     * galat lengkap beserta jejak kode terpampang ke pengunjung. Daripada
     * hanya menuliskannya di dokumentasi yang mudah terlewat, sistem
     * memberi tahu langsung di dasbor - hanya kepada Super Admin, dan hanya
     * ketika situs benar-benar diakses dari host non-lokal.
     *
     * @return array<int,string>
     */
    private function peringatanKeamanan(): array
    {
        if (!Auth::adalah('super_admin')) {
            return [];
        }

        $host  = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
        $lokal = str_starts_with($host, 'localhost')
              || str_starts_with($host, '127.0.0.1')
              || str_ends_with($host, '.test')
              || str_ends_with($host, '.local');

        if ($lokal) {
            return [];
        }

        $peringatan = [];

        if (!empty(App::config('app')['debug'])) {
            $peringatan[] = 'Mode debug masih AKTIF. Pesan galat lengkap beserta '
                . 'jejak kode dapat terlihat oleh pengunjung. Setel "debug" => false '
                . 'pada app/config/config.local.php sekarang juga.';
        }

        $aman = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
             || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
        if (!$aman) {
            $peringatan[] = 'Situs diakses tanpa HTTPS. Kata sandi admin dan data '
                . 'pengaduan dikirim tanpa enkripsi. Aktifkan sertifikat SSL '
                . '(tersedia gratis lewat cPanel) sebelum dipakai publik.';
        }

        if (Auth::pengguna()['username'] === 'superadmin'
            && password_verify('SikkaAdmin2026!', (string) (Pengguna::cariId((int) Auth::id())['password_hash'] ?? ''))) {
            $peringatan[] = 'Akun ini masih memakai kata sandi bawaan yang tercantum '
                . 'di berkas instalasi. Segera ganti melalui menu Pengguna.';
        }

        return $peringatan;
    }

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
            'peringatanKeamanan'=> $this->peringatanKeamanan(),
        ]);
    }
}
