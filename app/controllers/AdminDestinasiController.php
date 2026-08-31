<?php
declare(strict_types=1);

/**
 * CRUD destinasi (FR-MAP-12, FR-ADM-02).
 * Koordinat diisi lewat pemilih titik di peta - lihat view form dan
 * public/assets/js/admin-peta.js (§10.6).
 */
final class AdminDestinasiController extends Controller
{
    private const PER_HALAMAN = 20;

    public function index(): void
    {
        Auth::wajibPeran('super_admin', 'admin_konten');

        $halaman = max(1, (int) get_param('hal', '1'));
        $filter  = [
            'status'    => get_param('status', 'semua') ?: 'semua',
            'kategori'  => get_param('kategori'),
            'kecamatan' => get_param('kecamatan'),
            'cari'      => get_param('q'),
            'limit'     => self::PER_HALAMAN,
            'offset'    => ($halaman - 1) * self::PER_HALAMAN,
            'urut'      => 'diubah',
        ];
        $total = Destinasi::hitung($filter);

        $this->tampilkanAdmin('admin/destinasi/index', [
            'judul'        => 'Kelola Destinasi',
            'daftar'       => Destinasi::daftar($filter),
            'total'        => $total,
            'halaman'      => $halaman,
            'totalHalaman' => max(1, (int) ceil($total / self::PER_HALAMAN)),
            'ringkasan'    => Destinasi::ringkasan(),
        ]);
    }

    public function formBaru(): void
    {
        Auth::wajibPeran('super_admin', 'admin_konten');
        $this->tampilkanAdmin('admin/destinasi/form', [
            'judul' => 'Tambah Destinasi',
            'd'     => null,
            'galeri'=> [],
            'aksi'  => url('/admin/destinasi/baru'),
        ]);
    }

    public function simpanBaru(): void
    {
        Auth::wajibPeran('super_admin', 'admin_konten');
        Csrf::wajib();

        $data = $this->ambilInput();
        $galat = $this->validasi($data);

        if ($galat !== []) {
            Session::flash('error', implode(' ', $galat));
            Session::simpanInputLama($_POST);
            redirect('/admin/destinasi/baru');
        }

        $data['slug'] = Destinasi::slugUnik($data['slug']);

        // Foto utama bersifat opsional saat simpan pertama agar admin dapat
        // menyimpan draft cepat lalu melengkapi foto belakangan.
        $foto = Upload::simpan($_FILES['foto_utama'] ?? [], 'destinasi');
        if (!$foto['ok'] && $foto['pesan'] !== '') {
            Session::flash('error', $foto['pesan']);
            Session::simpanInputLama($_POST);
            redirect('/admin/destinasi/baru');
        }
        $data['foto_utama'] = $foto['nama'];

        $id = Destinasi::buat($data);

        // Galeri (drag-and-drop, FR-ADM-02)
        if (isset($_FILES['galeri']) && is_array($_FILES['galeri']['name'] ?? null)) {
            $hasil = Upload::simpanBanyak($_FILES['galeri'], 'destinasi');
            foreach ($hasil['tersimpan'] as $i => $berkas) {
                Database::run(
                    'INSERT INTO destinasi_galeri (destinasi_id, file, alt_text, urutan)
                     VALUES (:d, :f, :a, :u)',
                    ['d' => $id, 'f' => $berkas, 'a' => $data['nama'] . ' - foto ' . ($i + 1), 'u' => $i]
                );
            }
            if ($hasil['galat'] !== []) {
                Session::flash('error', 'Sebagian foto galeri gagal: ' . implode('; ', $hasil['galat']));
            }
        }

        LogAktivitas::catat('tambah', 'destinasi', $id, 'Menambah destinasi: ' . $data['nama']);
        Session::flash('sukses', 'Destinasi "' . $data['nama'] . '" tersimpan.');
        redirect('/admin/destinasi/' . $id . '/ubah');
    }

    public function formUbah(string $id): void
    {
        Auth::wajibPeran('super_admin', 'admin_konten');

        $d = Destinasi::cariId((int) $id);
        if ($d === null) {
            App::halaman404();
            return;
        }
        $this->tampilkanAdmin('admin/destinasi/form', [
            'judul' => 'Ubah Destinasi',
            'd'     => $d,
            'galeri'=> Destinasi::galeri((int) $id),
            'aksi'  => url('/admin/destinasi/' . (int) $id . '/ubah'),
        ]);
    }

    public function simpanUbah(string $id): void
    {
        Auth::wajibPeran('super_admin', 'admin_konten');
        Csrf::wajib();

        $idInt = (int) $id;
        $lama  = Destinasi::cariId($idInt);
        if ($lama === null) {
            App::halaman404();
            return;
        }

        $data  = $this->ambilInput();
        $galat = $this->validasi($data);
        if ($galat !== []) {
            Session::flash('error', implode(' ', $galat));
            Session::simpanInputLama($_POST);
            redirect('/admin/destinasi/' . $idInt . '/ubah');
        }

        $data['slug'] = Destinasi::slugUnik($data['slug'], $idInt);

        $foto = Upload::simpan($_FILES['foto_utama'] ?? [], 'destinasi');
        if ($foto['ok']) {
            if ((string) $lama['foto_utama'] !== '') {
                Upload::hapus((string) $lama['foto_utama'], 'destinasi');
            }
            $data['foto_utama'] = $foto['nama'];
        } elseif ($foto['pesan'] !== '') {
            Session::flash('error', $foto['pesan']);
            $data['foto_utama'] = (string) $lama['foto_utama'];
        } else {
            $data['foto_utama'] = (string) $lama['foto_utama'];
        }

        Destinasi::perbarui($idInt, $data);
        LogAktivitas::catat('ubah', 'destinasi', $idInt, 'Mengubah destinasi: ' . $data['nama']);
        Session::flash('sukses', 'Perubahan tersimpan.');
        redirect('/admin/destinasi/' . $idInt . '/ubah');
    }

    public function hapus(string $id): void
    {
        Auth::wajibPeran('super_admin');
        Csrf::wajib();

        $idInt = (int) $id;
        $d = Destinasi::cariId($idInt);
        if ($d === null) {
            App::halaman404();
            return;
        }
        Destinasi::hapus($idInt);
        LogAktivitas::catat('hapus', 'destinasi', $idInt, 'Menghapus destinasi: ' . $d['nama']);
        Session::flash('sukses', 'Destinasi dihapus.');
        redirect('/admin/destinasi');
    }

    /** FR-ADM-03: admin menyatakan data sudah diverifikasi ulang. */
    public function tandaiTerverifikasi(string $id): void
    {
        Auth::wajibPeran('super_admin', 'admin_konten');
        Csrf::wajib();

        $idInt = (int) $id;
        if (Destinasi::cariId($idInt) === null) {
            App::halaman404();
            return;
        }
        Destinasi::tandaiTerverifikasi($idInt);
        LogAktivitas::catat('verifikasi', 'destinasi', $idInt, 'Menandai data terverifikasi ulang');
        Session::flash('sukses', 'Destinasi ditandai sudah diverifikasi hari ini.');
        redirect('/admin/destinasi/' . $idInt . '/ubah');
    }

    public function tambahGaleri(string $id): void
    {
        Auth::wajibPeran('super_admin', 'admin_konten');
        Csrf::wajib();

        $idInt = (int) $id;
        if (Destinasi::cariId($idInt) === null) {
            App::halaman404();
            return;
        }

        if (!isset($_FILES['galeri']) || !is_array($_FILES['galeri']['name'] ?? null)) {
            Session::flash('error', 'Tidak ada foto yang dipilih.');
            redirect('/admin/destinasi/' . $idInt . '/ubah');
        }

        $hasil = Upload::simpanBanyak($_FILES['galeri'], 'destinasi');
        $urutAwal = (int) Database::scalar(
            'SELECT COALESCE(MAX(urutan), -1) + 1 FROM destinasi_galeri WHERE destinasi_id = :d',
            ['d' => $idInt]
        );
        $alt = trim((string) post('alt_text'));

        foreach ($hasil['tersimpan'] as $i => $berkas) {
            Database::run(
                'INSERT INTO destinasi_galeri (destinasi_id, file, alt_text, urutan) VALUES (:d, :f, :a, :u)',
                [
                    'd' => $idInt,
                    'f' => $berkas,
                    'a' => $alt !== '' ? $alt : 'Foto destinasi',
                    'u' => $urutAwal + $i,
                ]
            );
        }

        if ($hasil['galat'] !== []) {
            Session::flash('error', implode('; ', $hasil['galat']));
        }
        if ($hasil['tersimpan'] !== []) {
            LogAktivitas::catat('ubah', 'destinasi', $idInt, 'Menambah ' . count($hasil['tersimpan']) . ' foto galeri');
            Session::flash('sukses', count($hasil['tersimpan']) . ' foto ditambahkan ke galeri.');
        }
        redirect('/admin/destinasi/' . $idInt . '/ubah');
    }

    public function hapusGaleri(string $id): void
    {
        Auth::wajibPeran('super_admin', 'admin_konten');
        Csrf::wajib();

        $foto = Database::one('SELECT * FROM destinasi_galeri WHERE id = :id', ['id' => (int) $id]);
        if ($foto === null) {
            App::halaman404();
            return;
        }
        Upload::hapus((string) $foto['file'], 'destinasi');
        Database::run('DELETE FROM destinasi_galeri WHERE id = :id', ['id' => (int) $id]);
        LogAktivitas::catat('ubah', 'destinasi', (int) $foto['destinasi_id'], 'Menghapus foto galeri');
        Session::flash('sukses', 'Foto galeri dihapus.');
        redirect('/admin/destinasi/' . (int) $foto['destinasi_id'] . '/ubah');
    }

    /** @return array<string,mixed> */
    private function ambilInput(): array
    {
        $nama = post('nama');
        $slugManual = post('slug');

        $lat = post('latitude');
        $lng = post('longitude');

        return [
            'nama'                 => $nama,
            'nama_en'              => post('nama_en'),
            'slug'                 => buat_slug($slugManual !== '' ? $slugManual : $nama),
            'kategori_id'          => (int) post('kategori_id'),
            'kecamatan_id'         => ((int) post('kecamatan_id')) ?: null,
            'latitude'             => $lat !== '' ? (float) $lat : null,
            'longitude'            => $lng !== '' ? (float) $lng : null,
            'deskripsi_singkat'    => mb_substr(post('deskripsi_singkat'), 0, 400),
            'deskripsi_singkat_en' => mb_substr(post('deskripsi_singkat_en'), 0, 400),
            'deskripsi_lengkap'    => post('deskripsi_lengkap'),
            'deskripsi_lengkap_en' => post('deskripsi_lengkap_en'),
            'jam_operasional'      => post('jam_operasional'),
            'kisaran_tarif'        => post('kisaran_tarif'),
            'fasilitas'            => post('fasilitas'),
            'cara_mencapai'        => post('cara_mencapai'),
            'kontak_nama'          => post('kontak_nama'),
            'kontak_telepon'       => post('kontak_telepon'),
            'jarak_dari_maumere_km'=> post('jarak_dari_maumere_km') !== '' ? (float) post('jarak_dari_maumere_km') : null,
            'waktu_tempuh_menit'   => post('waktu_tempuh_menit') !== '' ? (int) post('waktu_tempuh_menit') : null,
            'unggulan'             => isset($_POST['unggulan']) ? 1 : 0,
            'status'               => in_array(post('status'), ['aktif', 'nonaktif', 'draft'], true) ? post('status') : 'draft',
            'sumber_data'          => post('sumber_data'),
            'perlu_verifikasi_lapangan' => isset($_POST['perlu_verifikasi_lapangan']) ? 1 : 0,
        ];
    }

    /** @return array<int,string> */
    private function validasi(array $d): array
    {
        $galat = [];

        if ($d['nama'] === '') {
            $galat[] = 'Nama destinasi wajib diisi.';
        }
        if ($d['kategori_id'] <= 0 || Kategori::cariId($d['kategori_id']) === null) {
            $galat[] = 'Kategori wajib dipilih.';
        }
        if ($d['kecamatan_id'] !== null && Kecamatan::cariId($d['kecamatan_id']) === null) {
            $galat[] = 'Kecamatan tidak dikenali.';
        }

        $adaLat = $d['latitude'] !== null;
        $adaLng = $d['longitude'] !== null;

        if ($adaLat !== $adaLng) {
            $galat[] = 'Koordinat harus diisi lengkap (lintang dan bujur) - gunakan pemilih titik di peta.';
        }
        // Destinasi berstatus aktif wajib punya titik peta, kalau tidak
        // pin-nya tidak akan pernah muncul.
        if ($d['status'] === 'aktif' && !$adaLat) {
            $galat[] = 'Destinasi berstatus aktif wajib memiliki titik koordinat di peta. Simpan sebagai draft bila lokasi belum diketahui.';
        }
        if ($adaLat && $adaLng && !koordinat_masuk_akal((float) $d['latitude'], (float) $d['longitude'])) {
            $galat[] = 'Koordinat berada di luar wilayah Kabupaten Sikka. Periksa kembali titik yang dipilih di peta.';
        }
        if ($d['status'] === 'aktif' && $d['deskripsi_singkat'] === '') {
            $galat[] = 'Deskripsi singkat wajib diisi untuk destinasi aktif.';
        }
        return $galat;
    }
}
