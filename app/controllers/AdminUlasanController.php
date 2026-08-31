<?php
declare(strict_types=1);

/** Moderasi ulasan (FR-DEST-03). Tidak ada publikasi otomatis. */
final class AdminUlasanController extends Controller
{
    public function index(): void
    {
        Auth::wajibPeran('super_admin', 'admin_konten');
        $status = get_param('status', 'menunggu') ?: 'menunggu';

        $this->tampilkanAdmin('admin/ulasan/index', [
            'judul'       => 'Moderasi Ulasan',
            'daftar'      => Ulasan::daftarModerasi($status),
            'statusAktif' => $status,
            'aktif'       => Ulasan::aktif(),
        ]);
    }

    public function moderasi(string $id): void
    {
        Auth::wajibPeran('super_admin', 'admin_konten');
        Csrf::wajib();

        $status = post('status');
        Ulasan::moderasi((int) $id, $status);
        LogAktivitas::catat('moderasi', 'ulasan', (int) $id, 'Ulasan di-' . $status);
        Session::flash('sukses', 'Status ulasan diperbarui.');
        redirect('/admin/ulasan');
    }
}
