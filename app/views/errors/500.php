<div class="container py-5" style="max-width: 760px">
  <div class="text-center">
    <p class="display-4 mb-2">500</p>
    <h1 class="h4 mb-3">Terjadi kesalahan pada server</h1>
    <p class="text-body-secondary">
      Kesalahan ini sudah dicatat di log server. Silakan coba beberapa saat lagi.
    </p>
    <a class="btn btn-teal" href="<?= e(url('/')) ?>"><?= e(Lang::teks('beranda')) ?></a>
  </div>

  <?php if (!empty($pesan)): ?>
    <div class="alert alert-danger mt-4">
      <strong>Mode debug aktif</strong> - pesan ini hanya tampil saat
      <code>debug</code> bernilai true di <code>app/config/config.php</code>.
      <pre class="small mt-2 mb-0" style="white-space: pre-wrap"><?= e($pesan) ?></pre>
    </div>
  <?php endif; ?>
</div>
