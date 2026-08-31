<header class="bg-brand-soft py-4">
  <div class="container">
    <nav aria-label="Remah roti">
      <ol class="breadcrumb small mb-2">
        <li class="breadcrumb-item"><a href="<?= e(url('/')) ?>"><?= e(Lang::teks('beranda')) ?></a></li>
        <li class="breadcrumb-item"><a href="<?= e(url('/event')) ?>"><?= e(Lang::teks('event_budaya')) ?></a></li>
        <li class="breadcrumb-item active" aria-current="page"><?= e($e['nama']) ?></li>
      </ol>
    </nav>
    <h1 class="h3 mb-1"><?= e($e['nama']) ?></h1>
    <p class="text-body-secondary mb-0">
      <?= e(tanggal_lokal((string) $e['tanggal_mulai'])) ?>
      <?php if ($e['tanggal_selesai'] !== null && $e['tanggal_selesai'] !== $e['tanggal_mulai']): ?>
        &ndash; <?= e(tanggal_lokal((string) $e['tanggal_selesai'])) ?>
      <?php endif; ?>
      <?php if ((string) $e['lokasi_teks'] !== ''): ?>
        &middot; <?= e($e['lokasi_teks']) ?>
      <?php endif; ?>
    </p>
  </div>
</header>

<div class="container py-4">
  <div class="row g-4">
    <div class="col-lg-8">
      <?php if ((string) $e['foto'] !== ''): ?>
        <img class="img-fluid rounded mb-3" src="<?= e(unggahan((string) $e['foto'], 'event')) ?>"
             alt="<?= e($e['nama']) ?>" width="800" height="450">
      <?php endif; ?>
      <?= paragraf((string) $e['deskripsi']) ?>
    </div>

    <div class="col-lg-4">
      <?php if ($e['destinasi_slug'] !== null): ?>
      <div class="card mb-3">
        <div class="card-body">
          <h2 class="h6 text-uppercase text-secondary mb-2"><?= e(Lang::inggris() ? 'Related destination' : 'Destinasi terkait') ?></h2>
          <a class="btn btn-sm btn-outline-teal w-100" href="<?= e(url('/destinasi/' . $e['destinasi_slug'])) ?>">
            <?= e($e['destinasi_nama']) ?>
          </a>
          <a class="btn btn-sm btn-link w-100 mt-1" href="<?= e(url('/peta', ['destinasi' => $e['destinasi_slug']])) ?>">
            <?= e(Lang::inggris() ? 'View on map' : 'Lihat di peta') ?>
          </a>
        </div>
      </div>
      <?php endif; ?>

      <?php if ($lainnya !== []): ?>
      <h2 class="h6 text-uppercase text-secondary mb-2"><?= e(Lang::teks('event_terdekat')) ?></h2>
      <ul class="list-unstyled small">
        <?php foreach ($lainnya as $ev): ?>
          <?php if ((int) $ev['id'] === (int) $e['id']) { continue; } ?>
          <li class="mb-2">
            <a class="text-decoration-none" href="<?= e(url('/event/' . $ev['slug'])) ?>">
              <strong><?= e(tanggal_lokal((string) $ev['tanggal_mulai'])) ?></strong><br><?= e($ev['nama']) ?>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
      <?php endif; ?>
    </div>
  </div>
</div>
