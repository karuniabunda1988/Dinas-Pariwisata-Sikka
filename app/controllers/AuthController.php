<?php
declare(strict_types=1);

final class AuthController extends Controller
{
    public function formLogin(): void
    {
        if (Auth::masuk()) {
            redirect('/admin');
        }
        $this->tampilkan('admin/login', [
            'judul'   => 'Masuk Panel Admin',
            'noindex' => true,
        ], 'kosong');
    }

    public function login(): void
    {
        Csrf::wajib();

        // Batas percobaan login untuk memperlambat serangan tebak sandi.
        if (!$this->lewatBatasLaju('login', 10)) {
            Session::flash('error', 'Terlalu banyak percobaan masuk. Coba lagi dalam satu jam.');
            redirect('/admin/login');
        }

        $username = post('username');
        $password = (string) ($_POST['password'] ?? '');

        if ($username === '' || $password === '') {
            Session::flash('error', 'Nama pengguna dan kata sandi wajib diisi.');
            redirect('/admin/login');
        }

        if (!Auth::coba($username, $password)) {
            LogAktivitas::catat('login_gagal', 'pengguna_admin', null, 'Percobaan masuk gagal: ' . $username);
            Session::flash('error', 'Nama pengguna atau kata sandi salah.');
            redirect('/admin/login');
        }

        $tujuan = (string) Session::get('_tujuan_setelah_login', '/admin');
        Session::hapus('_tujuan_setelah_login');
        Session::flash('sukses', 'Selamat datang, ' . (Auth::pengguna()['nama'] ?? '') . '.');
        redirect(str_starts_with($tujuan, '/admin') ? $tujuan : '/admin');
    }

    public function logout(): void
    {
        Csrf::wajib();
        Auth::keluar();
        Session::mulai();
        Session::flash('sukses', 'Anda telah keluar.');
        redirect('/admin/login');
    }
}
