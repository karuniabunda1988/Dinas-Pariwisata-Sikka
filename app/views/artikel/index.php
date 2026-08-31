<header class="bg-brand-soft py-4">
  <div class="container">
    <h1 class="h3 mb-1"><?= e($judul) ?></h1>
    <p class="text-body-secondary mb-0"><?= e($meta['deskripsi']) ?></p>
  </div>
</header>

<div class="container py-4">
  <?php if ($daftar === []): ?>
    <div class="alert alert-light border"><?= e(Lang::teks('belum_ada_data')) ?></div>
  <?php else: ?>
    <div class="row row-cols-1 row-cols-md-3 g-3">
      <?php foreach ($daftar as $a): ?>
      <div class="col">
        <article class="card h-100">
          <div class="rasio-foto">
            <img src="<?= e(unggahan((string) $a['gambar_sampul'], 'artikel')) ?>"
                 alt="<?= e($a['judul']) ?>" loading="lazy" width="400" height="250">
          </div>
          <div class="card-body">
            <p class="small text-secondary mb-1">
              <?= e(tanggal_lokal((string) ($a['published_at'] ?: $a['created_at']))) ?>
            </p>
            <h2 class="h6">
              <a class="text-decoration-none text-body stretched-link"
                 href="<?= e(url('/artikel/' . $a['slug'])) ?>"><?= e($a['judul']) ?></a>
            </h2>
            <p class="small text-body-secondary mb-0"><?= e(ringkas((string) $a['ringkasan'], 120)) ?></p>
          </div>
        </article>
      </div>
      <?php endforeach; ?>
    </div>

    <?php if ($totalHalaman > 1): ?>
    <nav class="mt-4" aria-label="Navigasi halaman">
      <ul class="pagination justify-content-center">
        <?php for ($i = 1; $i <= $totalHalaman; $i++): ?>
          <li class="page-item <?= $i === $halaman ? 'active' : '' ?>">
            <a class="page-link" href="<?= e(url('/artikel', ['hal' => $i])) ?>"><?= $i ?></a>
          </li>
        <?php endfor; ?>
      </ul>
    </nav>
    <?php endif; ?>
  <?php endif; ?>
</div>
