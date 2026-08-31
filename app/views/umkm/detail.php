<header class="bg-brand-soft py-4">
  <div class="container">
    <nav aria-label="Remah roti">
      <ol class="breadcrumb small mb-2">
        <li class="breadcrumb-item"><a href="<?= e(url('/')) ?>"><?= e(Lang::teks('beranda')) ?></a></li>
        <li class="breadcrumb-item"><a href="<?= e(url('/umkm')) ?>"><?= e(Lang::teks('umkm_akomodasi')) ?></a></li>
        <li class="breadcrumb-item active" aria-current="page"><?= e($u['nama']) ?></li>
      </ol>
    </nav>
    <span class="badge text-bg-light border mb-2"><?= e(Umkm::labelJenis((string) $u['jenis'])) ?></span>
    <h1 class="h3 mb-1"><?= e($u['nama']) ?></h1>
    <?php if ((string) $u['alamat'] !== ''): ?>
      <p class="text-body-secondary mb-0"><?= e($u['alamat']) ?></p>
    <?php endif; ?>
  </div>
</header>

<div class="container py-4">
  <div class="row g-4">
    <div class="col-lg-8">
      <?php if ((string) $u['foto'] !== ''): ?>
        <img class="img-fluid rounded mb-3" src="<?= e(unggahan((string) $u['foto'], 'umkm')) ?>"
             alt="<?= e($u['nama']) ?>" width="800" height="500">
      <?php endif; ?>
      <?= paragraf((string) $u['deskripsi']) ?>
    </div>

    <div class="col-lg-4">
      <div class="card mb-3">
        <div class="card-body">
          <h2 class="h6 text-uppercase text-secondary mb-3"><?= e(Lang::teks('kontak')) ?></h2>
          <?php if ((string) $u['kontak_wa'] !== ''): ?>
            <a class="btn btn-success w-100 mb-2" rel="noopener" target="_blank"
               href="https://wa.me/<?= e(nomor_wa((string) $u['kontak_wa'])) ?>">WhatsApp</a>
          <?php endif; ?>
          <?php if ((string) $u['kontak_telepon'] !== ''): ?>
            <a class="btn btn-outline-secondary w-100 mb-2" href="tel:<?= e($u['kontak_telepon']) ?>">
              <?= e($u['kontak_telepon']) ?>
            </a>
          <?php endif; ?>
          <?php if ((string) $u['kontak_wa'] === '' && (string) $u['kontak_telepon'] === ''): ?>
            <p class="small text-secondary mb-0">
              <?= e(Lang::inggris() ? 'Contact details not yet provided.' : 'Kontak belum tersedia.') ?>
            </p>
          <?php endif; ?>
        </div>
      </div>

      <?php if ($u['destinasi_slug'] !== null): ?>
      <div class="card mb-3">
        <div class="card-body">
          <h2 class="h6 text-uppercase text-secondary mb-2"><?= e(Lang::inggris() ? 'Nearest destination' : 'Destinasi terdekat') ?></h2>
          <a class="btn btn-sm btn-outline-teal w-100" href="<?= e(url('/destinasi/' . $u['destinasi_slug'])) ?>">
            <?= e($u['destinasi_nama']) ?>
          </a>
        </div>
      </div>
      <?php endif; ?>

      <?php if ($u['latitude'] !== null): ?>
        <a class="btn btn-outline-secondary btn-sm w-100" rel="noopener" target="_blank"
           href="https://www.google.com/maps/dir/?api=1&destination=<?= e($u['latitude']) ?>,<?= e($u['longitude']) ?>">
          <?= e(Lang::teks('rute_ke_sini')) ?>
        </a>
      <?php endif; ?>
    </div>
  </div>
</div>
