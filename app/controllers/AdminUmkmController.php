<?php
declare(strict_types=1);

/**
 * CRUD UMKM. Mitra hanya boleh menyunting entri miliknya sendiri
 * (FR-ADM-01).
 */
final class AdminUmkmController extends Controller
{
    public function index(): void
    {
        Auth::wajibMasuk();

        $filter = [
            'status' => get_param('status', 'semua') ?: 'semua',
            'jenis'  => get_param('jenis'),
            'cari'   => get_param('q'),
            'limit'  => 100,
        ];
        if (Auth::adalah('mitra')) {
            $filter['pemilik_id'] = Auth::id();
        }

        $this->tampilkanAdmin('admin/umkm/index', [
            'judul'     => 'Kelola UMKM & Mitra',
            'daftar'    => Umkm::daftar($filter),
            'ringkasan' => Umkm::ringkasan(),
        ]);
    }

    public function formBaru(): void
    {
        Auth::wajibMasuk();
        $this->tampilkanAdmin('admin/umkm/form', [
            'judul' => 'Tambah UMKM / Mitra',
            'u'     => null,
            'aksi'  => url('/admin/umkm/baru'),
        ]);
    }

    public function simpanBaru(): void
    {
        Auth::wajibMasuk();
        Csrf::wajib();

        $data  = $this->ambilInput(null);
        $galat = $this->validasi($data);
        if ($galat !== []) {
            Session::flash('error', implode(' ', $galat));
            Session::simpanInputLama($_POST);
            redirect('/admin/umkm/baru');
        }

        $data['slug'] = Umkm::slugUnik($data['slug']);

        $foto = Upload::simpan($_FILES['foto'] ?? [], 'umkm');
        if (!$foto['ok'] && $foto['pesan'] !== '') {
            Session::flash('error', $foto['pesan']);
            Session::simpanInputLama($_POST);
            redirect('/admin/umkm/baru');
        }
        $data['foto'] = $foto['nama'];

        $id = Umkm::buat($data);
        LogAktivitas::catat('tambah', 'umkm', $id, 'Menambah UMKM: ' . $data['nama']);
        Session::flash('sukses', 'Data UMKM tersimpan.');
        redirect('/admin/umkm/' . $id . '/ubah');
    }

    public function formUbah(string $id): void
    {
        Auth::wajibMasuk();
        $u = Umkm::cariId((int) $id);
        if ($u === null) {
            App::halaman404();
            return;
        }
        if (!Auth::bolehSuntingUmkm($u)) {
            Auth::wajibPeran('super_admin', 'admin_konten');
        }
        $this->tampilkanAdmin('admin/umkm/form', [
            'judul' => 'Ubah UMKM / Mitra',
            'u'     => $u,
            'aksi'  => url('/admin/umkm/' . (int) $id . '/ubah'),
        ]);
    }

    public function simpanUbah(string $id): void
    {
        Auth::wajibMasuk();
        Csrf::wajib();

        $idInt = (int) $id;
        $lama  = Umkm::cariId($idInt);
        if ($lama === null) {
            App::halaman404();
            return;
        }
        if (!Auth::bolehSuntingUmkm($lama)) {
            Auth::wajibPeran('super_admin', 'admin_konten');
        }

        $data  = $this->ambilInput($lama);
        $galat = $this->validasi($data);
        if ($galat !== []) {
            Session::flash('error', implode(' ', $galat));
            Session::simpanInputLama($_POST);
            redirect('/admin/umkm/' . $idInt . '/ubah');
        }

        $data['slug'] = Umkm::slugUnik($data['slug'], $idInt);

        $foto = Upload::simpan($_FILES['foto'] ?? [], 'umkm');
        if ($foto['ok']) {
            if ((string) $lama['foto'] !== '') {
                Upload::hapus((string) $lama['foto'], 'umkm');
            }
            $data['foto'] = $foto['nama'];
        } else {
            if ($foto['pesan'] !== '') {
                Session::flash('error', $foto['pesan']);
            }
            $data['foto'] = (string) $lama['foto'];
        }

        Umkm::perbarui($idInt, $data);
        LogAktivitas::catat('ubah', 'umkm', $idInt, 'Mengubah UMKM: ' . $data['nama']);
        Session::flash('sukses', 'Perubahan tersimpan.');
        redirect('/admin/umkm/' . $idInt . '/ubah');
    }

    public function hapus(string $id): void
    {
        Auth::wajibPeran('super_admin', 'admin_konten');
        Csrf::wajib();

        $idInt = (int) $id;
        $u = Umkm::cariId($idInt);
        if ($u === null) {
            App::halaman404();
            return;
        }
        Umkm::hapus($idInt);
        LogAktivitas::catat('hapus', 'umkm', $idInt, 'Menghapus UMKM: ' . $u['nama']);
        Session::flash('sukses', 'Data UMKM dihapus.');
        redirect('/admin/umkm');
    }

    /** Verifikasi mitra - hanya staf Dinas, bukan mitra sendiri. */
    public function ubahVerifikasi(string $id): void
    {
        Auth::wajibPeran('super_admin', 'admin_konten');
        Csrf::wajib();

        $idInt = (int) $id;
        if (Umkm::cariId($idInt) === null) {
            App::halaman404();
            return;
        }
        $status = post('status_verifikasi');
        Umkm::ubahVerifikasi($idInt, $status);
        LogAktivitas::catat('ubah', 'umkm', $idInt, 'Status verifikasi menjadi: ' . $status);
        Session::flash('sukses', 'Status verifikasi diperbarui.');
        redirect('/admin/umkm');
    }

    /** @param array<string,mixed>|null $lama */
    private function ambilInput(?array $lama): array
    {
        $nama = post('nama');
        $slug = post('slug');
        $lat  = post('latitude');
        $lng  = post('longitude');

        $data = [
            'nama'          => $nama,
            'slug'          => buat_slug($slug !== '' ? $slug : $nama),
            'jenis'         => array_key_exists(post('jenis'), Umkm::JENIS) ? post('jenis') : 'kuliner',
            'deskripsi'     => post('deskripsi'),
            'alamat'        => post('alamat'),
            'kontak_telepon'=> post('kontak_telepon'),
            'kontak_wa'     => post('kontak_wa'),
            'latitude'      => $lat !== '' ? (float) $lat : null,
            'longitude'     => $lng !== '' ? (float) $lng : null,
            'kecamatan_id'  => ((int) post('kecamatan_id')) ?: null,
            'destinasi_terdekat_id' => ((int) post('destinasi_terdekat_id')) ?: null,
        ];

        // Mitra tidak boleh memverifikasi dirinya sendiri atau memindahkan
        // kepemilikan entri.
        if (Auth::adalah('mitra')) {
            $data['status_verifikasi'] = $lama !== null
                ? (string) $lama['status_verifikasi']
                : 'menunggu';
            $data['pemilik_user_id'] = $lama !== null
                ? ($lama['pemilik_user_id'] !== null ? (int) $lama['pemilik_user_id'] : Auth::id())
                : Auth::id();
        } else {
            $status = post('status_verifikasi');
            $data['status_verifikasi'] = in_array($status, ['menunggu', 'terverifikasi', 'ditolak'], true)
                ? $status : 'menunggu';
            $data['pemilik_user_id'] = ((int) post('pemilik_user_id')) ?: ($lama['pemilik_user_id'] ?? null);
        }

        return $data;
    }

    /** @return array<int,string> */
    private function validasi(array $d): array
    {
        $galat = [];
        if ($d['nama'] === '') {
            $galat[] = 'Nama UMKM wajib diisi.';
        }
        if ($d['destinasi_terdekat_id'] !== null && Destinasi::cariId((int) $d['destinasi_terdekat_id']) === null) {
            $galat[] = 'Destinasi terdekat tidak dikenali.';
        }
        if (($d['latitude'] === null) !== ($d['longitude'] === null)) {
            $galat[] = 'Koordinat harus diisi lengkap atau dikosongkan seluruhnya.';
        }
        if ($d['latitude'] !== null && !koordinat_masuk_akal((float) $d['latitude'], (float) $d['longitude'])) {
            $galat[] = 'Koordinat berada di luar wilayah Kabupaten Sikka.';
        }
        return $galat;
    }
}
