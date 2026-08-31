<?php
declare(strict_types=1);

final class AdminPengaturanController extends Controller
{
    /** Kunci yang boleh disunting lewat form - mencegah injeksi kunci baru. */
    private const KUNCI_DIIZINKAN = [
        'nama_situs', 'tagline', 'tagline_en', 'instansi', 'alamat_instansi',
        'email_instansi', 'telepon_instansi', 'wa_notifikasi', 'wa_gateway_url',
        'wa_gateway_token', 'running_text', 'instagram', 'peta_lat_awal',
        'peta_lng_awal', 'peta_zoom_awal', 'link_ppid', 'link_oss', 'link_dpmptsp',
        'ulasan_aktif', 'hak_cipta',
    ];

    public function index(): void
    {
        Auth::wajibPeran('super_admin');
        $this->tampilkanAdmin('admin/pengaturan/index', [
            'judul' => 'Pengaturan Situs',
            'data'  => Pengaturan::semua(),
            'kunci' => self::KUNCI_DIIZINKAN,
        ]);
    }

    public function simpan(): void
    {
        Auth::wajibPeran('super_admin');
        Csrf::wajib();

        $diubah = 0;
        foreach (self::KUNCI_DIIZINKAN as $kunci) {
            if (!array_key_exists($kunci, $_POST)) {
                // Checkbox yang tidak dicentang tidak terkirim - set ke '0'.
                if ($kunci === 'ulasan_aktif') {
                    Pengaturan::simpan($kunci, '0');
                    $diubah++;
                }
                continue;
            }
            $nilai = is_string($_POST[$kunci]) ? trim($_POST[$kunci]) : '';

            if ($kunci === 'ulasan_aktif') {
                $nilai = $nilai !== '' ? '1' : '0';
            }
            if (in_array($kunci, ['link_ppid', 'link_oss', 'link_dpmptsp', 'wa_gateway_url'], true)
                && $nilai !== ''
                && !filter_var($nilai, FILTER_VALIDATE_URL)) {
                Session::flash('error', 'URL pada kolom "' . $kunci . '" tidak valid dan tidak disimpan.');
                continue;
            }
            if ($kunci === 'wa_notifikasi' && $nilai !== '') {
                $nilai = nomor_wa($nilai);
            }

            Pengaturan::simpan($kunci, mb_substr($nilai, 0, 2000));
            $diubah++;
        }

        LogAktivitas::catat('ubah', 'pengaturan', null, "Memperbarui {$diubah} pengaturan situs");
        Session::flash('sukses', 'Pengaturan tersimpan.');
        redirect('/admin/pengaturan');
    }
}
