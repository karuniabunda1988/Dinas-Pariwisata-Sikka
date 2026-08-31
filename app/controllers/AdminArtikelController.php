<?php
declare(strict_types=1);

final class AdminArtikelController extends Controller
{
    public function index(): void
    {
        Auth::wajibPeran('super_admin', 'admin_konten');
        $this->tampilkanAdmin('admin/artikel/index', [
            'judul'  => 'Kelola Artikel',
            'daftar' => Artikel::daftar(['status' => 'semua', 'limit' => 100]),
        ]);
    }

    public function formBaru(): void
    {
        Auth::wajibPeran('super_admin', 'admin_konten');
        $this->tampilkanAdmin('admin/artikel/form', [
            'judul' => 'Tulis Artikel',
            'a'     => null,
            'aksi'  => url('/admin/artikel/baru'),
        ]);
    }

    public function simpanBaru(): void
    {
        Auth::wajibPeran('super_admin', 'admin_konten');
        Csrf::wajib();

        $data  = $this->ambilInput();
        $galat = $this->validasi($data);
        if ($galat !== []) {
            Session::flash('error', implode(' ', $galat));
            Session::simpanInputLama($_POST);
            redirect('/admin/artikel/baru');
        }

        $data['slug']       = Artikel::slugUnik($data['slug']);
        $data['penulis_id'] = Auth::id();

        $foto = Upload::simpan($_FILES['gambar_sampul'] ?? [], 'artikel');
        $data['gambar_sampul'] = $foto['nama'];

        $id = Artikel::buat($data);
        LogAktivitas::catat('tambah', 'artikel', $id, 'Menulis artikel: ' . $data['judul']);
        Session::flash('sukses', 'Artikel tersimpan.');
        redirect('/admin/artikel');
    }

    public function formUbah(string $id): void
    {
        Auth::wajibPeran('super_admin', 'admin_konten');
        $a = Artikel::cariId((int) $id);
        if ($a === null) {
            App::halaman404();
            return;
        }
        $this->tampilkanAdmin('admin/artikel/form', [
            'judul' => 'Ubah Artikel',
            'a'     => $a,
            'aksi'  => url('/admin/artikel/' . (int) $id . '/ubah'),
        ]);
    }

    public function simpanUbah(string $id): void
    {
        Auth::wajibPeran('super_admin', 'admin_konten');
        Csrf::wajib();

        $idInt = (int) $id;
        $lama  = Artikel::cariId($idInt);
        if ($lama === null) {
            App::halaman404();
            return;
        }

        $data  = $this->ambilInput();
        $galat = $this->validasi($data);
        if ($galat !== []) {
            Session::flash('error', implode(' ', $galat));
            Session::simpanInputLama($_POST);
            redirect('/admin/artikel/' . $idInt . '/ubah');
        }

        $data['slug']       = Artikel::slugUnik($data['slug'], $idInt);
        $data['penulis_id'] = $lama['penulis_id'] !== null ? (int) $lama['penulis_id'] : Auth::id();

        $foto = Upload::simpan($_FILES['gambar_sampul'] ?? [], 'artikel');
        if ($foto['ok']) {
            if ((string) $lama['gambar_sampul'] !== '') {
                Upload::hapus((string) $lama['gambar_sampul'], 'artikel');
            }
            $data['gambar_sampul'] = $foto['nama'];
        } else {
            $data['gambar_sampul'] = (string) $lama['gambar_sampul'];
        }

        Artikel::perbarui($idInt, $data);
        LogAktivitas::catat('ubah', 'artikel', $idInt, 'Mengubah artikel: ' . $data['judul']);
        Session::flash('sukses', 'Perubahan tersimpan.');
        redirect('/admin/artikel');
    }

    public function hapus(string $id): void
    {
        Auth::wajibPeran('super_admin', 'admin_konten');
        Csrf::wajib();

        $idInt = (int) $id;
        $a = Artikel::cariId($idInt);
        if ($a === null) {
            App::halaman404();
            return;
        }
        Artikel::hapus($idInt);
        LogAktivitas::catat('hapus', 'artikel', $idInt, 'Menghapus artikel: ' . $a['judul']);
        Session::flash('sukses', 'Artikel dihapus.');
        redirect('/admin/artikel');
    }

    private function ambilInput(): array
    {
        $judul  = post('judul');
        $slug   = post('slug');
        $status = post('status') === 'publish' ? 'publish' : 'draft';

        return [
            'judul'         => $judul,
            'slug'          => buat_slug($slug !== '' ? $slug : $judul),
            'ringkasan'     => mb_substr(post('ringkasan'), 0, 400),
            'isi'           => (string) ($_POST['isi'] ?? ''),
            'kategori'      => post('kategori', 'panduan') ?: 'panduan',
            'status'        => $status,
            'published_at'  => $status === 'publish'
                ? (post('published_at') !== '' ? post('published_at') : date('Y-m-d H:i:s'))
                : null,
        ];
    }

    private function validasi(array $d): array
    {
        $galat = [];
        if ($d['judul'] === '') {
            $galat[] = 'Judul artikel wajib diisi.';
        }
        if ($d['status'] === 'publish' && trim((string) $d['isi']) === '') {
            $galat[] = 'Isi artikel wajib diisi sebelum dipublikasikan.';
        }
        return $galat;
    }
}
