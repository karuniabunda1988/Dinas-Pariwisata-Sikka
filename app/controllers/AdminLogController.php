<?php
declare(strict_types=1);

/** Jejak audit - siapa mengubah apa dan kapan (§11, FR-ADM-03). */
final class AdminLogController extends Controller
{
    private const PER_HALAMAN = 50;

    public function index(): void
    {
        Auth::wajibPeran('super_admin');

        $halaman = max(1, (int) get_param('hal', '1'));
        $total   = LogAktivitas::jumlah();

        $this->tampilkanAdmin('admin/log/index', [
            'judul'        => 'Log Aktivitas',
            'daftar'       => LogAktivitas::terbaru(self::PER_HALAMAN, ($halaman - 1) * self::PER_HALAMAN),
            'halaman'      => $halaman,
            'totalHalaman' => max(1, (int) ceil($total / self::PER_HALAMAN)),
            'total'        => $total,
        ]);
    }
}
