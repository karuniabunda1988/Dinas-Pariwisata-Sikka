<?php
declare(strict_types=1);

final class AdminPengaduanController extends Controller
{
    public function index(): void
    {
        Auth::wajibPeran('super_admin', 'admin_konten');
        $status = get_param('status', 'semua') ?: 'semua';

        $this->tampilkanAdmin('admin/pengaduan/index', [
            'judul'       => 'Pengaduan & Masukan',
            'daftar'      => Pengaduan::daftar($status),
            'statusAktif' => $status,
            'jumlahBaru'  => Pengaduan::jumlahBaru(),
        ]);
    }

    public function detail(string $id): void
    {
        Auth::wajibPeran('super_admin', 'admin_konten');
        $p = Pengaduan::cariId((int) $id);
        if ($p === null) {
            App::halaman404();
            return;
        }

        // Tautan wa.me siap klik agar admin bisa membalas pelapor langsung
        // walau gateway otomatis tidak tersedia (§13.1 tingkat 2).
        $waPelapor = '';
        if (trim((string) $p['kontak_pelapor']) !== '' && preg_match('/\d{8,}/', (string) $p['kontak_pelapor'])) {
            $waPelapor = Notifier::tautanWa(
                (string) $p['kontak_pelapor'],
                'Halo, terima kasih atas laporan Anda ke Dinas Pariwisata Kabupaten Sikka (nomor #' . (int) $p['id'] . ').'
            );
        }

        $this->tampilkanAdmin('admin/pengaduan/detail', [
            'judul'     => 'Pengaduan #' . (int) $p['id'],
            'p'         => $p,
            'waPelapor' => $waPelapor,
        ]);
    }

    public function perbarui(string $id): void
    {
        Auth::wajibPeran('super_admin', 'admin_konten');
        Csrf::wajib();

        $idInt = (int) $id;
        if (Pengaduan::cariId($idInt) === null) {
            App::halaman404();
            return;
        }
        Pengaduan::perbarui($idInt, post('status_tindak_lanjut'), post('catatan_admin'));
        LogAktivitas::catat('ubah', 'pengaduan', $idInt, 'Tindak lanjut: ' . post('status_tindak_lanjut'));
        Session::flash('sukses', 'Tindak lanjut pengaduan tersimpan.');
        redirect('/admin/pengaduan/' . $idInt);
    }
}
