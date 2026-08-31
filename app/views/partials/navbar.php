<?php /** Navigasi utama - arsitektur informasi §8. */ ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-brand sticky-top" aria-label="Navigasi utama">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center gap-2" href="<?= e(url('/')) ?>">
      <span class="brand-mark" aria-hidden="true">SK</span>
      <span class="lh-sm">
        <strong class="d-block"><?= e(Pengaturan::ambil('nama_situs', 'Pariwisata Sikka')) ?></strong>
        <small class="opacity-75 d-none d-sm-block">Kabupaten Sikka &middot; Flores &middot; NTT</small>
      </span>
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
            data-bs-target="#navUtama" aria-controls="navUtama"
            aria-expanded="false" aria-label="Buka menu navigasi">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navUtama">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link<?= menu_aktif('/') ?>" href="<?= e(url('/')) ?>"><?= e(Lang::teks('beranda')) ?></a></li>
        <li class="nav-item"><a class="nav-link<?= menu_aktif('/peta') ?>" href="<?= e(url('/peta')) ?>"><?= e(Lang::teks('peta_wisata')) ?></a></li>
        <li class="nav-item"><a class="nav-link<?= menu_aktif('/destinasi') ?>" href="<?= e(url('/destinasi')) ?>"><?= e(Lang::teks('destinasi')) ?></a></li>
        <li class="nav-item"><a class="nav-link<?= menu_aktif('/event') ?>" href="<?= e(url('/event')) ?>"><?= e(Lang::teks('event_budaya')) ?></a></li>
        <li class="nav-item"><a class="nav-link<?= menu_aktif('/umkm') ?>" href="<?= e(url('/umkm')) ?>"><?= e(Lang::teks('umkm_akomodasi')) ?></a></li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Lainnya
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="<?= e(url('/artikel')) ?>"><?= e(Lang::teks('artikel')) ?></a></li>
            <li><a class="dropdown-item" href="<?= e(url('/statistik')) ?>"><?= e(Lang::teks('statistik')) ?></a></li>
            <li><a class="dropdown-item" href="<?= e(url('/layanan')) ?>"><?= e(Lang::teks('layanan_publik')) ?></a></li>
            <li><a class="dropdown-item" href="<?= e(url('/profil')) ?>"><?= e(Lang::teks('profil_dinas')) ?></a></li>
          </ul>
        </li>
      </ul>

      <div class="ms-lg-3 d-flex align-items-center gap-2">
        <div class="btn-group btn-group-sm" role="group" aria-label="Pilih bahasa">
          <a class="btn btn-outline-light<?= Lang::aktif() === 'id' ? ' active' : '' ?>"
             href="<?= e(url_bahasa('id')) ?>" hreflang="id"
             <?= Lang::aktif() === 'id' ? 'aria-current="true"' : '' ?>>ID</a>
          <a class="btn btn-outline-light<?= Lang::aktif() === 'en' ? ' active' : '' ?>"
             href="<?= e(url_bahasa('en')) ?>" hreflang="en"
             <?= Lang::aktif() === 'en' ? 'aria-current="true"' : '' ?>>EN</a>
        </div>
      </div>
    </div>
  </div>
</nav>
