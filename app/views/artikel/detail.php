<article class="container py-4" style="max-width: 780px">
  <nav aria-label="Remah roti">
    <ol class="breadcrumb small">
      <li class="breadcrumb-item"><a href="<?= e(url('/')) ?>"><?= e(Lang::teks('beranda')) ?></a></li>
      <li class="breadcrumb-item"><a href="<?= e(url('/artikel')) ?>"><?= e(Lang::teks('artikel')) ?></a></li>
      <li class="breadcrumb-item active" aria-current="page"><?= e(ringkas((string) $a['judul'], 40)) ?></li>
    </ol>
  </nav>

  <h1 class="h2 mb-2"><?= e($a['judul']) ?></h1>
  <p class="text-secondary small">
    <?= e(tanggal_lokal((string) ($a['published_at'] ?: $a['created_at']))) ?>
    <?php if (!empty($a['penulis_nama'])): ?>
      &middot; <?= e($a['penulis_nama']) ?>
    <?php endif; ?>
  </p>

  <?php if ((string) $a['gambar_sampul'] !== ''): ?>
    <img class="img-fluid rounded mb-4" src="<?= e(unggahan((string) $a['gambar_sampul'], 'artikel')) ?>"
         alt="<?= e($a['judul']) ?>" width="780" height="440">
  <?php endif; ?>

  <?php if ((string) $a['ringkasan'] !== ''): ?>
    <p class="lead"><?= e($a['ringkasan']) ?></p>
  <?php endif; ?>

  <?= paragraf((string) $a['isi']) ?>
</article>

<?php if ($lainnya !== []): ?>
<section class="bg-body-tertiary py-4">
  <div class="container">
    <h2 class="h6 text-uppercase text-secondary mb-3"><?= e(Lang::inggris() ? 'More articles' : 'Artikel lainnya') ?></h2>
    <div class="row row-cols-1 row-cols-md-3 g-3">
      <?php foreach ($lainnya as $lain): ?>
        <?php if ((int) $lain['id'] === (int) $a['id']) { continue; } ?>
        <div class="col">
          <div class="card h-100">
            <div class="card-body">
              <h3 class="h6 mb-1">
                <a class="text-decoration-none text-body stretched-link"
                   href="<?= e(url('/artikel/' . $lain['slug'])) ?>"><?= e($lain['judul']) ?></a>
              </h3>
              <p class="small text-secondary mb-0"><?= e(ringkas((string) $lain['ringkasan'], 90)) ?></p>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>
