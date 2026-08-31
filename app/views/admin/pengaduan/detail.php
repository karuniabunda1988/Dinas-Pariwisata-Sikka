<div class="row g-4">
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header bg-white d-flex justify-content-between">
        <h2 class="h6 mb-0">Pengaduan #<?= (int) $p['id'] ?></h2>
        <span class="small text-secondary"><?= e(date('d/m/Y H:i', strtotime((string) $p['created_at']))) ?></span>
      </div>
      <div class="card-body">
        <?php if ($p['destinasi_nama'] !== null): ?>
          <p class="small text-secondary mb-2">Destinasi: <strong><?= e($p['destinasi_nama']) ?></strong></p>
        <?php endif; ?>
        <?= paragraf((string) $p['isi']) ?>
      </div>
    </div>

    <div class="card mt-3">
      <div class="card-header bg-white"><h2 class="h6 mb-0">Tindak Lanjut</h2></div>
      <div class="card-body">
        <form method="post" action="<?= e(url('/admin/pengaduan/' . (int) $p['id'])) ?>">
          <?= Csrf::field() ?>
          <div class="mb-3">
            <label class="form-label" for="status_tindak_lanjut">Status</label>
            <select class="form-select" id="status_tindak_lanjut" name="status_tindak_lanjut">
              <?php foreach (['baru' => 'Baru', 'diproses' => 'Diproses', 'selesai' => 'Selesai'] as $k => $v): ?>
                <option value="<?= e($k) ?>" <?= $p['status_tindak_lanjut'] === $k ? 'selected' : '' ?>><?= e($v) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label" for="catatan_admin">Catatan internal</label>
            <textarea class="form-control" id="catatan_admin" name="catatan_admin"
                      rows="4"><?= e((string) $p['catatan_admin']) ?></textarea>
            <div class="form-text">Catatan ini tidak ditampilkan ke publik.</div>
          </div>
          <button class="btn btn-teal" type="submit">Simpan tindak lanjut</button>
          <a class="btn btn-link" href="<?= e(url('/admin/pengaduan')) ?>">Kembali</a>
        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card">
      <div class="card-header bg-white"><h2 class="h6 mb-0">Pelapor</h2></div>
      <div class="card-body small">
        <p class="mb-1">Nama: <strong><?= e((string) $p['nama_pelapor'] !== '' ? $p['nama_pelapor'] : 'Anonim') ?></strong></p>
        <p class="mb-2">Kontak: <?= e((string) $p['kontak_pelapor'] !== '' ? $p['kontak_pelapor'] : '-') ?></p>

        <?php if ($waPelapor !== ''): ?>
          <a class="btn btn-sm btn-success w-100 mb-2" rel="noopener" target="_blank" href="<?= e($waPelapor) ?>">
            Balas via WhatsApp
          </a>
        <?php endif; ?>

        <p class="text-secondary mb-0">
          Status notifikasi masuk: <code><?= e($p['status_notifikasi']) ?></code><br>
          <span style="font-size:.75rem">
            <code>gateway</code> = terkirim otomatis;
            <code>wa_link</code> = tautan disiapkan di log;
            <code>log</code> = nomor WA belum diatur.
            Pengaduan selalu tersimpan apa pun hasil notifikasinya.
          </span>
        </p>
      </div>
    </div>
  </div>
</div>
