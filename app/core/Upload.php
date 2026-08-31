<?php
declare(strict_types=1);

/**
 * Penanganan unggah foto destinasi/UMKM/event/artikel.
 * Memvalidasi tipe berkas berdasarkan isi (finfo), bukan hanya ekstensi.
 */
final class Upload
{
    /**
     * @param array<string,mixed> $berkas Satu elemen dari $_FILES
     * @return array{ok:bool, nama:string, pesan:string}
     */
    public static function simpan(array $berkas, string $folder): array
    {
        $kode = (int) ($berkas['error'] ?? UPLOAD_ERR_NO_FILE);

        if ($kode === UPLOAD_ERR_NO_FILE) {
            return ['ok' => false, 'nama' => '', 'pesan' => ''];
        }
        if ($kode === UPLOAD_ERR_INI_SIZE || $kode === UPLOAD_ERR_FORM_SIZE) {
            return ['ok' => false, 'nama' => '', 'pesan' => 'Ukuran berkas melebihi batas server.'];
        }
        if ($kode !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'nama' => '', 'pesan' => 'Gagal mengunggah berkas (kode ' . $kode . ').'];
        }

        $cfg  = App::config('upload');
        $tmp  = (string) $berkas['tmp_name'];
        $size = (int) $berkas['size'];

        if (!is_uploaded_file($tmp)) {
            return ['ok' => false, 'nama' => '', 'pesan' => 'Berkas tidak valid.'];
        }
        if ($size > $cfg['maks_byte']) {
            $mb = round($cfg['maks_byte'] / 1048576, 1);
            return ['ok' => false, 'nama' => '', 'pesan' => "Ukuran foto maksimal {$mb} MB."];
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = (string) $finfo->file($tmp);
        if (!in_array($mime, $cfg['mime'], true)) {
            return ['ok' => false, 'nama' => '', 'pesan' => 'Format foto harus JPG, PNG, atau WebP.'];
        }
        // Pastikan benar-benar gambar yang bisa dibaca.
        if (@getimagesize($tmp) === false) {
            return ['ok' => false, 'nama' => '', 'pesan' => 'Berkas bukan gambar yang valid.'];
        }

        $ext = match ($mime) {
            'image/png'  => 'png',
            'image/webp' => 'webp',
            default      => 'jpg',
        };

        $dir = self::direktori($folder);
        if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
            return ['ok' => false, 'nama' => '', 'pesan' => 'Folder unggahan tidak dapat dibuat.'];
        }

        $nama = date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
        if (!move_uploaded_file($tmp, $dir . '/' . $nama)) {
            return ['ok' => false, 'nama' => '', 'pesan' => 'Berkas gagal disimpan ke server.'];
        }
        @chmod($dir . '/' . $nama, 0644);

        return ['ok' => true, 'nama' => $nama, 'pesan' => ''];
    }

    /**
     * Unggah banyak berkas sekaligus (galeri drag-and-drop, FR-ADM-02).
     * @return array{tersimpan:array<int,string>, galat:array<int,string>}
     */
    public static function simpanBanyak(array $berkasBanyak, string $folder, int $maks = 10): array
    {
        $tersimpan = [];
        $galat     = [];
        $jumlah    = is_array($berkasBanyak['name'] ?? null) ? count($berkasBanyak['name']) : 0;

        for ($i = 0; $i < min($jumlah, $maks); $i++) {
            $satu = [
                'name'     => $berkasBanyak['name'][$i],
                'type'     => $berkasBanyak['type'][$i],
                'tmp_name' => $berkasBanyak['tmp_name'][$i],
                'error'    => $berkasBanyak['error'][$i],
                'size'     => $berkasBanyak['size'][$i],
            ];
            $hasil = self::simpan($satu, $folder);
            if ($hasil['ok']) {
                $tersimpan[] = $hasil['nama'];
            } elseif ($hasil['pesan'] !== '') {
                $galat[] = $berkasBanyak['name'][$i] . ': ' . $hasil['pesan'];
            }
        }
        return ['tersimpan' => $tersimpan, 'galat' => $galat];
    }

    public static function hapus(string $nama, string $folder): void
    {
        $nama = basename($nama);
        if ($nama === '') {
            return;
        }
        $path = self::direktori($folder) . '/' . $nama;
        if (is_file($path)) {
            @unlink($path);
        }
    }

    private static function direktori(string $folder): string
    {
        $folder = preg_replace('/[^a-z0-9_\-]/', '', strtolower($folder)) ?: 'lain';
        return dirname(__DIR__, 2) . '/public/uploads/' . $folder;
    }
}
