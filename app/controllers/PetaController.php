<?php
declare(strict_types=1);

/** Halaman peta interaktif layar penuh - fitur inti (§9.2, §10). */
final class PetaController extends Controller
{
    /**
     * Lapisan peta yang diaktifkan admin (FR-MAP-10).
     * @return array<int,array<string,mixed>>
     */
    private function lapisanAktif(): array
    {
        $keluar = [];
        foreach ((array) (App::config('peta')['lapisan'] ?? []) as $l) {
            if (empty($l['aktif'])) {
                continue;
            }
            $keluar[] = [
                'nama'     => Lang::inggris() && !empty($l['nama_en']) ? $l['nama_en'] : $l['nama'],
                'url'      => $l['url'],
                'atribusi' => $l['atribusi'],
                'zoomMaks' => (int) ($l['zoom_maks'] ?? 18),
                'bawaan'   => (bool) ($l['bawaan'] ?? false),
            ];
        }
        return $keluar;
    }

    /**
     * URL berkas batas kecamatan - hanya bila berkasnya benar-benar ada.
     * Tanpa berkas resmi dari BIG/Setda, opsi lapisan ini tidak ditawarkan
     * sama sekali daripada menampilkan batas wilayah yang tidak sah.
     */
    private function urlBatasKecamatan(): ?string
    {
        $relatif = trim((string) (App::config('peta')['batas_kecamatan'] ?? ''));
        if ($relatif === '') {
            return null;
        }
        $absolut = dirname(__DIR__, 2) . '/public/' . ltrim($relatif, '/');
        return is_file($absolut) ? aset($relatif) : null;
    }

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
            'lapisan'   => $this->lapisanAktif(),
            'urlBatas'  => $this->urlBatasKecamatan(),
        ]);
    }
}
