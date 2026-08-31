<?php
$ig    = Pengaturan::ambil('instagram', '');
$ppid  = Pengaturan::ambil('link_ppid', '');
$tahun = date('Y');
?>
<footer class="footer-situs mt-5 pt-5 pb-4">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-4">
        <h2 class="h6 text-uppercase text-white-50 mb-3">
          <?= e(Pengaturan::ambil('instansi', 'Dinas Pariwisata Kabupaten Sikka')) ?>
        </h2>
        <p class="small mb-2"><?= e(Pengaturan::ambil('alamat_instansi', 'Maumere, Kabupaten Sikka, Nusa Tenggara Timur')) ?></p>
        <?php if (($tel = Pengaturan::ambil('telepon_instansi', '')) !== ''): ?>
          <p class="small mb-1">Telepon: <?= e($tel) ?></p>
        <?php endif; ?>
        <?php if (($mail = Pengaturan::ambil('email_instansi', '')) !== ''): ?>
          <p class="small mb-1">Email: <a class="link-light" href="mailto:<?= e($mail) ?>"><?= e($mail) ?></a></p>
        <?php endif; ?>
        <?php if ($ig !== ''): ?>
          <p class="small mb-0">
            Instagram:
            <a class="link-light" rel="noopener" target="_blank"
               href="https://instagram.com/<?= e(ltrim($ig, '@')) ?>">@<?= e(ltrim($ig, '@')) ?></a>
          </p>
        <?php endif; ?>
      </div>

      <div class="col-6 col-lg-2">
        <h2 class="h6 text-uppercase text-white-50 mb-3">Jelajahi</h2>
        <ul class="list-unstyled small">
          <li><a class="link-light" href="<?= e(url('/peta')) ?>"><?= e(Lang::teks('peta_wisata')) ?></a></li>
          <li><a class="link-light" href="<?= e(url('/destinasi')) ?>"><?= e(Lang::teks('destinasi')) ?></a></li>
          <li><a class="link-light" href="<?= e(url('/event')) ?>"><?= e(Lang::teks('event_budaya')) ?></a></li>
          <li><a class="link-light" href="<?= e(url('/umkm')) ?>"><?= e(Lang::teks('umkm_akomodasi')) ?></a></li>
          <li><a class="link-light" href="<?= e(url('/artikel')) ?>"><?= e(Lang::teks('artikel')) ?></a></li>
        </ul>
      </div>

      <div class="col-6 col-lg-3">
        <h2 class="h6 text-uppercase text-white-50 mb-3">Kategori</h2>
        <ul class="list-unstyled small">
          <?php foreach (Kategori::semua() as $k): ?>
          <li>
            <a class="link-light" href="<?= e(url('/destinasi/kategori/' . $k['slug'])) ?>">
              <span class="titik-kategori" style="background: <?= e($k['warna']) ?>" aria-hidden="true"></span>
              <?= e(Lang::inggris() && $k['nama_en'] !== '' ? $k['nama_en'] : $k['nama']) ?>
            </a>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>

      <div class="col-lg-3">
        <h2 class="h6 text-uppercase text-white-50 mb-3">Layanan &amp; Informasi</h2>
        <ul class="list-unstyled small">
          <li><a class="link-light" href="<?= e(url('/layanan/pengaduan')) ?>"><?= e(Lang::teks('pengaduan')) ?></a></li>
          <li><a class="link-light" href="<?= e(url('/statistik')) ?>"><?= e(Lang::teks('statistik')) ?></a></li>
          <li><a class="link-light" href="<?= e(url('/profil')) ?>"><?= e(Lang::teks('profil_dinas')) ?></a></li>
          <?php if ($ppid !== ''): ?>
          <li><a class="link-light" rel="noopener" target="_blank" href="<?= e($ppid) ?>">PPID Kabupaten Sikka</a></li>
          <?php endif; ?>
          <li><a class="link-light" href="<?= e(url('/sitemap.xml')) ?>">Sitemap</a></li>
        </ul>
      </div>
    </div>

    <hr class="border-secondary my-4">

    <div class="d-flex flex-column flex-md-row justify-content-between gap-2 small text-white-50">
      <p class="mb-0">
        &copy; <?= e($tahun) ?> <?= e(Pengaturan::ambil('instansi', 'Dinas Pariwisata Kabupaten Sikka')) ?>.
      </p>
      <p class="mb-0">
        Dikembangkan oleh
        <strong class="text-white"><?= e(Pengaturan::ambil('hak_cipta', 'Karunia Bunda IT Training Center Maumere')) ?></strong>
      </p>
    </div>
    <p class="small text-white-50 mt-2 mb-0">
      Data peta &copy; kontributor
      <a class="link-light" rel="noopener" target="_blank" href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>.
    </p>
  </div>
</footer>
