<?php
declare(strict_types=1);

/**
 * Controller dasar: merender view, mengatur meta SEO, dan memuat data
 * yang dibutuhkan seluruh halaman (navigasi, pengaturan situs).
 */
class Controller
{
    /** @var array<string,mixed> Meta SEO halaman saat ini (§12). */
    protected array $meta = [];

    /** Merender view publik di dalam layout utama. */
    public function tampilkan(string $view, array $data = [], string $layout = 'utama'): void
    {
        $data['meta']       = $this->metaFinal($data);
        $data['pengaturan'] = Pengaturan::semua();
        $data['flash']      = Session::ambilFlash();

        $this->render($view, $data, 'layouts/' . $layout);
        Session::bersihkanInputLama();
    }

    /** Merender view admin di dalam layout admin. */
    public function tampilkanAdmin(string $view, array $data = []): void
    {
        $data['meta']       = ['judul' => $data['judul'] ?? 'Panel Admin', 'noindex' => true];
        $data['pengaturan'] = Pengaturan::semua();
        $data['flash']      = Session::ambilFlash();
        $data['pengguna']   = Auth::pengguna();

        $this->render($view, $data, 'layouts/admin');
        Session::bersihkanInputLama();
    }

    private function render(string $view, array $data, string $layout): void
    {
        $berkasView = $this->pathView($view);
        if (!is_file($berkasView)) {
            throw new RuntimeException("View {$view} tidak ditemukan.");
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $berkasView;
        $isiHalaman = (string) ob_get_clean();

        $berkasLayout = $this->pathView($layout);
        if (!is_file($berkasLayout)) {
            echo $isiHalaman;
            return;
        }
        require $berkasLayout;
    }

    /** Merender potongan view (partial) dan mengembalikan HTML-nya. */
    protected function potongan(string $view, array $data = []): string
    {
        $berkas = $this->pathView($view);
        if (!is_file($berkas)) {
            return '';
        }
        extract($data, EXTR_SKIP);
        ob_start();
        require $berkas;
        return (string) ob_get_clean();
    }

    private function pathView(string $view): string
    {
        $view = str_replace(['..', '\\'], '', $view);
        return dirname(__DIR__) . '/views/' . $view . '.php';
    }

    /** @return array<string,mixed> */
    private function metaFinal(array $data): array
    {
        $situs = (string) Pengaturan::ambil('nama_situs', App::config('app')['nama']);
        $judul = (string) ($data['judul'] ?? $this->meta['judul'] ?? $situs);

        return array_merge([
            'judul'      => $judul,
            'judul_penuh'=> $judul === $situs ? $situs : $judul . ' | ' . $situs,
            'deskripsi'  => (string) Pengaturan::ambil('tagline', ''),
            'gambar'     => '',
            'kanonik'    => url_absolut(App::uri()),
            'noindex'    => false,
            'jsonld'     => [],
        ], $this->meta, array_intersect_key($data, array_flip(
            ['judul', 'judul_penuh', 'deskripsi', 'gambar', 'kanonik', 'noindex', 'jsonld']
        )));
    }

    /** Terapkan header cache pendek untuk halaman publik statis. */
    protected function cachePublik(int $detik = 300): void
    {
        if (!Auth::masuk()) {
            header('Cache-Control: public, max-age=' . $detik);
        }
    }

    /**
     * Batas laju sederhana berbasis sesi + IP untuk form publik (§12).
     * Cukup untuk shared hosting tanpa Redis.
     */
    protected function lewatBatasLaju(string $kunci, int $maksPerJam): bool
    {
        $sekarang = time();
        $riwayat  = (array) Session::get('_rate_' . $kunci, []);
        $riwayat  = array_values(array_filter($riwayat, static fn($t) => $sekarang - (int) $t < 3600));

        if (count($riwayat) >= $maksPerJam) {
            return false;
        }
        $riwayat[] = $sekarang;
        Session::set('_rate_' . $kunci, $riwayat);
        return true;
    }
}
