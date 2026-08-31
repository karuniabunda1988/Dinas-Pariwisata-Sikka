<?php
declare(strict_types=1);

/** Layanan publik: pengaduan & tautan perizinan (§9.8). */
final class LayananController extends Controller
{
    public function index(): void
    {
        $this->cachePublik(600);
        $this->tampilkan('layanan/index', [
            'judul'     => Lang::inggris() ? 'Public Services' : 'Layanan Publik',
            'deskripsi' => Lang::inggris()
                ? 'Traveller feedback, complaints and links to tourism business licensing services.'
                : 'Masukan dan pengaduan wisatawan serta tautan layanan perizinan usaha pariwisata.',
        ]);
    }

    public function formPengaduan(): void
    {
        $this->tampilkan('layanan/pengaduan', [
            'judul'     => Lang::inggris() ? 'Feedback & Complaints' : 'Pengaduan & Masukan',
            'deskripsi' => Lang::inggris()
                ? 'Report damaged facilities, cleanliness issues or other concerns at destinations in Sikka.'
                : 'Laporkan fasilitas rusak, masalah kebersihan, atau kendala lain di destinasi wisata Sikka.',
            'destinasi' => Destinasi::daftar(['urut' => 'nama']),
        ]);
    }

    /**
     * FR-SVC-01 + kriteria UAT §21 butir 5:
     * pengaduan WAJIB tersimpan di basis data walaupun notifikasi gagal.
     */
    public function kirimPengaduan(): void
    {
        Csrf::wajib();

        // Honeypot - bot mengisi field yang disembunyikan CSS.
        if (post('website') !== '') {
            redirect('/layanan/pengaduan/terkirim');
        }

        if (!$this->lewatBatasLaju('pengaduan', (int) App::config('rate_limit')['pengaduan_per_jam'])) {
            Session::flash('error', 'Terlalu banyak pengaduan dikirim dari perangkat ini. Silakan coba lagi nanti.');
            Session::simpanInputLama($_POST);
            redirect('/layanan/pengaduan');
        }

        $isi = post('isi');
        if (mb_strlen($isi) < 15) {
            Session::flash('error', 'Isi pengaduan minimal 15 karakter agar dapat ditindaklanjuti.');
            Session::simpanInputLama($_POST);
            redirect('/layanan/pengaduan');
        }

        $destinasiId = (int) post('destinasi_id', '0') ?: null;
        if ($destinasiId !== null && Destinasi::cariId($destinasiId) === null) {
            $destinasiId = null;
        }

        // 1) SIMPAN LEBIH DULU - ini yang tidak boleh gagal.
        $id = Pengaduan::buat($isi, post('nama'), post('kontak'), $destinasiId);

        // 2) Baru kirim notifikasi. Kegagalan di sini tidak membatalkan apa pun.
        try {
            $pesan = "[Pengaduan Wisata Sikka #{$id}]\n"
                   . 'Dari: ' . (post('nama') !== '' ? post('nama') : 'Anonim') . "\n"
                   . 'Kontak: ' . (post('kontak') !== '' ? post('kontak') : '-') . "\n"
                   . ($destinasiId !== null
                        ? 'Destinasi: ' . (Destinasi::cariId($destinasiId)['nama'] ?? '-') . "\n" : '')
                   . "Isi: " . ringkas($isi, 500);

            $tingkat = Notifier::kirim($pesan, 'pengaduan');
            Pengaduan::tandaiNotifikasi($id, $tingkat);
        } catch (Throwable $e) {
            error_log('[Pengaduan] Notifikasi gagal total: ' . $e->getMessage());
            Pengaduan::tandaiNotifikasi($id, 'gagal');
        }

        Session::flash('sukses', 'Pengaduan Anda tersimpan dengan nomor #' . $id . '. Terima kasih.');
        redirect('/layanan/pengaduan/terkirim');
    }

    public function pengaduanTerkirim(): void
    {
        $this->tampilkan('layanan/terkirim', [
            'judul'   => Lang::inggris() ? 'Report Submitted' : 'Pengaduan Terkirim',
            'noindex' => true,
        ]);
    }
}
