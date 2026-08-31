<?php $destinasiTerpilih = (int) get_param('destinasi'); ?>

<div class="container py-4" style="max-width: 720px">
  <nav aria-label="Remah roti">
    <ol class="breadcrumb small">
      <li class="breadcrumb-item"><a href="<?= e(url('/')) ?>"><?= e(Lang::teks('beranda')) ?></a></li>
      <li class="breadcrumb-item"><a href="<?= e(url('/layanan')) ?>"><?= e(Lang::teks('layanan_publik')) ?></a></li>
      <li class="breadcrumb-item active" aria-current="page"><?= e(Lang::teks('pengaduan')) ?></li>
    </ol>
  </nav>

  <h1 class="h3 mb-2"><?= e($judul) ?></h1>
  <p class="text-body-secondary"><?= e($meta['deskripsi']) ?></p>

  <form method="post" action="<?= e(url('/layanan/pengaduan')) ?>" class="card">
    <div class="card-body">
      <?= Csrf::field() ?>

      <!-- Honeypot: disembunyikan dari pengguna, diisi bot -->
      <div class="visually-hidden" aria-hidden="true">
        <label for="website">Website</label>
        <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
      </div>

      <div class="mb-3">
        <label class="form-label" for="p-destinasi">
          <?= e(Lang::inggris() ? 'Related destination (optional)' : 'Destinasi terkait (opsional)') ?>
        </label>
        <select class="form-select" id="p-destinasi" name="destinasi_id">
          <option value="">&mdash; <?= e(Lang::inggris() ? 'Not specific to one destination' : 'Tidak terkait destinasi tertentu') ?> &mdash;</option>
          <?php foreach ($destinasi as $d): ?>
          <option value="<?= (int) $d['id'] ?>" <?= $destinasiTerpilih === (int) $d['id'] ? 'selected' : '' ?>>
            <?= e($d['nama']) ?><?= $d['kecamatan_nama'] !== null ? ' (Kec. ' . e($d['kecamatan_nama']) . ')' : '' ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label" for="p-isi">
          <?= e(Lang::inggris() ? 'Your report' : 'Isi pengaduan / masukan') ?>
          <span class="text-danger" aria-hidden="true">*</span>
        </label>
        <textarea class="form-control" id="p-isi" name="isi" rows="5" required minlength="15" maxlength="4000"
                  placeholder="<?= e(Lang::inggris()
                        ? 'Describe what you found, and where exactly if possible.'
                        : 'Jelaskan apa yang Anda temukan, dan di bagian mana persisnya bila memungkinkan.') ?>"><?= e(Session::inputLama('isi')) ?></textarea>
        <div class="form-text">
          <?= e(Lang::inggris() ? 'Minimum 15 characters.' : 'Minimal 15 karakter.') ?>
        </div>
      </div>

      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label" for="p-nama"><?= e(Lang::inggris() ? 'Your name (optional)' : 'Nama Anda (opsional)') ?></label>
          <input class="form-control" type="text" id="p-nama" name="nama" maxlength="120"
                 value="<?= e(Session::inputLama('nama')) ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label" for="p-kontak">
            <?= e(Lang::inggris() ? 'Phone / WhatsApp / email (optional)' : 'Telepon / WhatsApp / email (opsional)') ?>
          </label>
          <input class="form-control" type="text" id="p-kontak" name="kontak" maxlength="120"
                 value="<?= e(Session::inputLama('kontak')) ?>">
        </div>
      </div>

      <!-- Kepatuhan UU PDP 27/2022 (§12): sampaikan tujuan pengumpulan data -->
      <div class="alert alert-light border small mt-3 mb-0">
        <?= e(Lang::inggris()
              ? 'Name and contact details are optional and used solely to follow up on your report. Leave them blank to report anonymously - your report will still be recorded.'
              : 'Nama dan kontak bersifat opsional dan hanya digunakan untuk menindaklanjuti laporan Anda. Kosongkan bila ingin melapor secara anonim - laporan tetap tercatat.') ?>
      </div>
    </div>

    <div class="card-footer bg-white">
      <button class="btn btn-teal" type="submit"><?= e(Lang::teks('kirim')) ?></button>
      <a class="btn btn-link" href="<?= e(url('/layanan')) ?>">Batal</a>
    </div>
  </form>
</div>
