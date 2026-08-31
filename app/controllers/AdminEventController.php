<?php
declare(strict_types=1);

final class AdminEventController extends Controller
{
    public function index(): void
    {
        Auth::wajibPeran('super_admin', 'admin_konten');
        $this->tampilkanAdmin('admin/event/index', [
            'judul'  => 'Kelola Event & Budaya',
            'daftar' => EventWisata::daftar(['status' => 'semua', 'limit' => 200]),
        ]);
    }

    public function formBaru(): void
    {
        Auth::wajibPeran('super_admin', 'admin_konten');
        $this->tampilkanAdmin('admin/event/form', [
            'judul' => 'Tambah Event',
            'e'     => null,
            'aksi'  => url('/admin/event/baru'),
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
            redirect('/admin/event/baru');
        }

        $data['slug'] = EventWisata::slugUnik($data['slug']);
        $foto = Upload::simpan($_FILES['foto'] ?? [], 'event');
        $data['foto'] = $foto['nama'];

        $id = EventWisata::buat($data);
        LogAktivitas::catat('tambah', 'event', $id, 'Menambah event: ' . $data['nama']);
        Session::flash('sukses', 'Event tersimpan.');
        redirect('/admin/event');
    }

    public function formUbah(string $id): void
    {
        Auth::wajibPeran('super_admin', 'admin_konten');
        $e = EventWisata::cariId((int) $id);
        if ($e === null) {
            App::halaman404();
            return;
        }
        $this->tampilkanAdmin('admin/event/form', [
            'judul' => 'Ubah Event',
            'e'     => $e,
            'aksi'  => url('/admin/event/' . (int) $id . '/ubah'),
        ]);
    }

    public function simpanUbah(string $id): void
    {
        Auth::wajibPeran('super_admin', 'admin_konten');
        Csrf::wajib();

        $idInt = (int) $id;
        $lama  = EventWisata::cariId($idInt);
        if ($lama === null) {
            App::halaman404();
            return;
        }

        $data  = $this->ambilInput();
        $galat = $this->validasi($data);
        if ($galat !== []) {
            Session::flash('error', implode(' ', $galat));
            Session::simpanInputLama($_POST);
            redirect('/admin/event/' . $idInt . '/ubah');
        }

        $data['slug'] = EventWisata::slugUnik($data['slug'], $idInt);
        $foto = Upload::simpan($_FILES['foto'] ?? [], 'event');
        if ($foto['ok']) {
            if ((string) $lama['foto'] !== '') {
                Upload::hapus((string) $lama['foto'], 'event');
            }
            $data['foto'] = $foto['nama'];
        } else {
            $data['foto'] = (string) $lama['foto'];
        }

        EventWisata::perbarui($idInt, $data);
        LogAktivitas::catat('ubah', 'event', $idInt, 'Mengubah event: ' . $data['nama']);
        Session::flash('sukses', 'Perubahan tersimpan.');
        redirect('/admin/event');
    }

    public function hapus(string $id): void
    {
        Auth::wajibPeran('super_admin', 'admin_konten');
        Csrf::wajib();

        $idInt = (int) $id;
        $e = EventWisata::cariId($idInt);
        if ($e === null) {
            App::halaman404();
            return;
        }
        EventWisata::hapus($idInt);
        LogAktivitas::catat('hapus', 'event', $idInt, 'Menghapus event: ' . $e['nama']);
        Session::flash('sukses', 'Event dihapus.');
        redirect('/admin/event');
    }

    private function ambilInput(): array
    {
        $nama = post('nama');
        $slug = post('slug');
        $selesai = post('tanggal_selesai');

        return [
            'nama'                 => $nama,
            'slug'                 => buat_slug($slug !== '' ? $slug : $nama),
            'tanggal_mulai'        => post('tanggal_mulai'),
            'tanggal_selesai'      => $selesai !== '' ? $selesai : null,
            'lokasi_teks'          => post('lokasi_teks'),
            'destinasi_terkait_id' => ((int) post('destinasi_terkait_id')) ?: null,
            'deskripsi'            => post('deskripsi'),
            'status'               => post('status') === 'aktif' ? 'aktif' : 'draft',
        ];
    }

    private function validasi(array $d): array
    {
        $galat = [];
        if ($d['nama'] === '') {
            $galat[] = 'Nama event wajib diisi.';
        }
        if ($d['tanggal_mulai'] === '' || strtotime((string) $d['tanggal_mulai']) === false) {
            $galat[] = 'Tanggal mulai wajib diisi dengan format tanggal yang benar.';
        }
        if ($d['tanggal_selesai'] !== null) {
            if (strtotime((string) $d['tanggal_selesai']) === false) {
                $galat[] = 'Tanggal selesai tidak valid.';
            } elseif (strtotime((string) $d['tanggal_selesai']) < strtotime((string) $d['tanggal_mulai'])) {
                $galat[] = 'Tanggal selesai tidak boleh mendahului tanggal mulai.';
            }
        }
        if ($d['destinasi_terkait_id'] !== null && Destinasi::cariId((int) $d['destinasi_terkait_id']) === null) {
            $galat[] = 'Destinasi terkait tidak dikenali.';
        }
        return $galat;
    }
}
