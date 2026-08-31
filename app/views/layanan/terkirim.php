<div class="container py-5 text-center" style="max-width: 620px">
  <div class="display-6 mb-3" aria-hidden="true">✅</div>
  <h1 class="h3 mb-2"><?= e(Lang::inggris() ? 'Report submitted' : 'Pengaduan Terkirim') ?></h1>
  <p class="text-body-secondary">
    <?= e(Lang::inggris()
          ? 'Your report has been recorded in the system and forwarded to the Tourism Office. Thank you for helping improve tourism in Sikka.'
          : 'Laporan Anda sudah tercatat di sistem dan diteruskan ke Dinas Pariwisata. Terima kasih telah membantu memperbaiki pariwisata Sikka.') ?>
  </p>
  <div class="d-flex gap-2 justify-content-center mt-4">
    <a class="btn btn-teal" href="<?= e(url('/peta')) ?>"><?= e(Lang::teks('buka_peta')) ?></a>
    <a class="btn btn-outline-secondary" href="<?= e(url('/')) ?>"><?= e(Lang::teks('beranda')) ?></a>
  </div>
</div>
