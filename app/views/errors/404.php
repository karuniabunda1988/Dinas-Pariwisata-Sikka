<div class="container py-5 text-center" style="max-width: 600px">
  <p class="display-4 mb-2">404</p>
  <h1 class="h4 mb-3"><?= e(Lang::inggris() ? 'Page not found' : 'Halaman tidak ditemukan') ?></h1>
  <p class="text-body-secondary">
    <?= e(Lang::inggris()
          ? 'The page you are looking for may have moved or no longer exists.'
          : 'Halaman yang Anda cari mungkin sudah dipindahkan atau tidak lagi tersedia.') ?>
  </p>
  <div class="d-flex gap-2 justify-content-center mt-4">
    <a class="btn btn-teal" href="<?= e(url('/peta')) ?>"><?= e(Lang::teks('buka_peta')) ?></a>
    <a class="btn btn-outline-secondary" href="<?= e(url('/destinasi')) ?>"><?= e(Lang::teks('destinasi')) ?></a>
    <a class="btn btn-outline-secondary" href="<?= e(url('/')) ?>"><?= e(Lang::teks('beranda')) ?></a>
  </div>
</div>
