<?php
declare(strict_types=1);

/** Kelola akun admin & mitra - hanya Super Admin (FR-ADM-01). */
final class AdminPenggunaController extends Controller
{
    public function index(): void
    {
        Auth::wajibPeran('super_admin');
        $this->tampilkanAdmin('admin/pengguna/index', [
            'judul'  => 'Kelola Pengguna',
            'daftar' => Pengguna::semua(),
        ]);
    }

    public function formBaru(): void
    {
        Auth::wajibPeran('super_admin');
        $this->tampilkanAdmin('admin/pengguna/form', [
            'judul' => 'Tambah Pengguna',
            'u'     => null,
            'aksi'  => url('/admin/pengguna/baru'),
        ]);
    }

    public function simpanBaru(): void
    {
        Auth::wajibPeran('super_admin');
        Csrf::wajib();

        $nama     = post('nama');
        $username = strtolower(preg_replace('/[^a-zA-Z0-9_.]/', '', post('username')) ?? '');
        $email    = post('email');
        $peran    = post('peran');
        $sandi    = (string) ($_POST['password'] ?? '');
        $ulang    = (string) ($_POST['password_ulang'] ?? '');

        $galat = [];
        if ($nama === '') {
            $galat[] = 'Nama wajib diisi.';
        }
        if (mb_strlen($username) < 4) {
            $galat[] = 'Nama pengguna minimal 4 karakter (huruf, angka, titik, garis bawah).';
        } elseif (Pengguna::usernameDipakai($username)) {
            $galat[] = 'Nama pengguna sudah dipakai.';
        }
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $galat[] = 'Format email tidak valid.';
        }
        if (mb_strlen($sandi) < 10) {
            $galat[] = 'Kata sandi minimal 10 karakter.';
        }
        if ($sandi !== $ulang) {
            $galat[] = 'Konfirmasi kata sandi tidak cocok.';
        }
        if (!in_array($peran, Auth::PERAN, true)) {
            $galat[] = 'Peran tidak dikenali.';
        }

        if ($galat !== []) {
            Session::flash('error', implode(' ', $galat));
            Session::simpanInputLama($_POST);
            redirect('/admin/pengguna/baru');
        }

        $id = Pengguna::buat($nama, $username, $email, $sandi, $peran);
        LogAktivitas::catat('tambah', 'pengguna_admin', $id, "Menambah pengguna {$username} ({$peran})");
        Session::flash('sukses', 'Pengguna baru dibuat.');
        redirect('/admin/pengguna');
    }

    public function formUbah(string $id): void
    {
        Auth::wajibPeran('super_admin');
        $u = Pengguna::cariId((int) $id);
        if ($u === null) {
            App::halaman404();
            return;
        }
        $this->tampilkanAdmin('admin/pengguna/form', [
            'judul' => 'Ubah Pengguna',
            'u'     => $u,
            'aksi'  => url('/admin/pengguna/' . (int) $id . '/ubah'),
        ]);
    }

    public function simpanUbah(string $id): void
    {
        Auth::wajibPeran('super_admin');
        Csrf::wajib();

        $idInt = (int) $id;
        $u = Pengguna::cariId($idInt);
        if ($u === null) {
            App::halaman404();
            return;
        }

        $nama  = post('nama');
        $email = post('email');
        $peran = post('peran');
        $aktif = isset($_POST['aktif']);
        $sandi = (string) ($_POST['password'] ?? '');
        $ulang = (string) ($_POST['password_ulang'] ?? '');

        $galat = [];
        if ($nama === '') {
            $galat[] = 'Nama wajib diisi.';
        }
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $galat[] = 'Format email tidak valid.';
        }
        if (!in_array($peran, Auth::PERAN, true)) {
            $galat[] = 'Peran tidak dikenali.';
        }
        if ($sandi !== '' && mb_strlen($sandi) < 10) {
            $galat[] = 'Kata sandi baru minimal 10 karakter.';
        }
        if ($sandi !== '' && $sandi !== $ulang) {
            $galat[] = 'Konfirmasi kata sandi tidak cocok.';
        }

        // Jangan sampai sistem kehilangan seluruh super admin.
        $turunPeran = $u['peran'] === 'super_admin' && $peran !== 'super_admin';
        $nonaktif   = $u['peran'] === 'super_admin' && !$aktif;
        if (($turunPeran || $nonaktif) && Pengguna::jumlahSuperAdmin() <= 1) {
            $galat[] = 'Tidak dapat menurunkan/menonaktifkan Super Admin terakhir.';
        }

        if ($galat !== []) {
            Session::flash('error', implode(' ', $galat));
            Session::simpanInputLama($_POST);
            redirect('/admin/pengguna/' . $idInt . '/ubah');
        }

        Pengguna::perbarui($idInt, $nama, $email, $peran, $aktif);
        if ($sandi !== '') {
            Pengguna::gantiPassword($idInt, $sandi);
            LogAktivitas::catat('ubah', 'pengguna_admin', $idInt, 'Mengganti kata sandi pengguna');
        }
        LogAktivitas::catat('ubah', 'pengguna_admin', $idInt, 'Mengubah data pengguna: ' . $u['username']);
        Session::flash('sukses', 'Data pengguna diperbarui.');
        redirect('/admin/pengguna');
    }

    public function hapus(string $id): void
    {
        Auth::wajibPeran('super_admin');
        Csrf::wajib();

        $idInt = (int) $id;
        $u = Pengguna::cariId($idInt);
        if ($u === null) {
            App::halaman404();
            return;
        }
        if ($idInt === Auth::id()) {
            Session::flash('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
            redirect('/admin/pengguna');
        }
        if ($u['peran'] === 'super_admin' && Pengguna::jumlahSuperAdmin() <= 1) {
            Session::flash('error', 'Tidak dapat menghapus Super Admin terakhir.');
            redirect('/admin/pengguna');
        }

        Pengguna::hapus($idInt);
        LogAktivitas::catat('hapus', 'pengguna_admin', $idInt, 'Menghapus pengguna: ' . $u['username']);
        Session::flash('sukses', 'Pengguna dihapus.');
        redirect('/admin/pengguna');
    }
}
