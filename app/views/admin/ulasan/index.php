<?php if (!$aktif): ?>
<div class="alert alert-info small">
  Fitur ulasan publik saat ini <strong>nonaktif</strong> (Fase 2 PRD).
  Super Admin dapat mengaktifkannya di
  <a href="<?= e(url('/admin/pengaturan')) ?>">Pengaturan Situs</a>.
  Ulasan yang sudah masuk tetap tersimpan dan dapat dimoderasi di sini.
</div>
<?php endif; ?>

<form class="mb-3" method="get">
  <label class="form-label small" for="m-status">Status moderasi</label>
  <select class="form-select form-select-sm" id="m-status" name="status" style="max-width:220px"
          onchange="this.form.submit()">
    <?php foreach (['menunggu' => 'Menunggu', 'disetujui' => 'Disetujui', 'ditolak' => 'Ditolak', 'semua' => 'Semua'] as $k => $v): ?>
      <option value="<?= e($k) ?>" <?= $statusAktif === $k ? 'selected' : '' ?>><?= e($v) ?></option>
    <?php endforeach; ?>
  </select>
  <noscript><button class="btn btn-sm btn-outline-secondary mt-1" type="submit">Tampilkan</button></noscript>
</form>

<?php if ($daftar === []): ?>
  <div class="card"><div class="card-body small text-secondary">Tidak ada ulasan pada status ini.</div></div>
<?php else: ?>
  <?php foreach ($daftar as $ul): ?>
  <div class="card mb-2">
    <div class="card-body">
      <div class="d-flex justify-content-between flex-wrap gap-2">
        <div>
          <strong><?= e($ul['nama_penulis']) ?></strong>
          <span class="text-warning"><?= str_repeat('★', (int) $ul['rating']) ?></span>
          <span class="small text-secondary">
            &middot; <a href="<?= e(url('/destinasi/' . $ul['destinasi_slug'])) ?>"><?= e($ul['destinasi_nama']) ?></a>
            &middot; <?= e(date('d/m/Y H:i', strtotime((string) $ul['created_at']))) ?>
          </span>
        </div>
        <span class="badge text-bg-light border"><?= e($ul['status_moderasi']) ?></span>
      </div>

      <p class="mt-2 mb-2"><?= e($ul['komentar']) ?></p>

      <form method="post" action="<?= e(url('/admin/ulasan/' . (int) $ul['id'] . '/moderasi')) ?>"
            class="d-flex gap-2">
        <?= Csrf::field() ?>
        <button class="btn btn-sm btn-success" type="submit" name="status" value="disetujui">Setujui</button>
        <button class="btn btn-sm btn-outline-danger" type="submit" name="status" value="ditolak">Tolak</button>
      </form>
    </div>
  </div>
  <?php endforeach; ?>
<?php endif; ?>
