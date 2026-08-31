<?php
declare(strict_types=1);

/** Halaman peta interaktif layar penuh - fitur inti (§9.2, §10). */
final class PetaController extends Controller
{
    public function index(): void
    {
        $slugTerpilih = get_param('destinasi');   // FR-MAP-09: URL unik per pin
        $terpilih     = $slugTerpilih !== '' ? Destinasi::cariSlug($slugTerpilih) : null;

        $judul = Lang::inggris() ? 'Interactive Tourism Map' : 'Peta Wisata Interaktif';
        $desk  = Lang::inggris()
            ? 'Interactive map of tourist destinations across the 21 districts of Sikka Regency, Flores.'
            : 'Peta interaktif destinasi wisata di 21 kecamatan Kabupaten Sikka, Flores.';

        if ($terpilih !== null) {
            $judul = Lang::kolom($terpilih, 'nama') . ' - ' . $judul;
            $desk  = ringkas(Lang::kolom($terpilih, 'deskripsi_singkat'), 160) ?: $desk;
        }

        $this->tampilkan('peta/index', [
            'judul'     => $judul,
            'deskripsi' => $desk,
            'kanonik'   => $terpilih !== null
                ? url_absolut('/peta') . '?destinasi=' . rawurlencode($terpilih['slug'])
                : url_absolut('/peta'),
            'kategori'  => Kategori::denganJumlah(),
            'kecamatan' => Kecamatan::denganJumlah(),
            'terpilih'  => $terpilih,
            // Fallback daftar teks bila JS/peta gagal dimuat (§10.7)
            'daftarTeks'=> Destinasi::daftar(['urut' => 'nama']),
            'pin'       => Destinasi::untukPeta([]),
        ]);
    }
}
