<?php $namaBulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']; ?>

<div class="alert alert-light border small">
  <strong>Catatan penting.</strong> Sistem ini tidak menghitung wisatawan
  secara otomatis. Angka di bawah diinput manual dari data yang sudah
  dikumpulkan OPD. Kolom <em>sumber data</em> wajib diisi agar angka dapat
  diaudit dan dipertanggungjawabkan saat dipakai untuk bahan rapat anggaran.
</div>

<div class="row g-4">
  <div class="col-lg-5">
    <div class="card">
      <div class="card-header bg-white"><h2 class="h6 mb-0">Input / Perbarui Data</h2></div>
      <div class="card-body">
        <form method="post" action="<?= e(url('/admin/statistik')) ?>">
          <?= Csrf::field() ?>
          <div class="row g-3">
            <div class="col-6">
              <label class="form-label" for="tahun">Tahun</label>
              <input class="form-control" type="number" id="tahun" name="tahun" min="2000" max="2100"
                     required value="<?= e(Session::inputLama('tahun', (string) $tahun)) ?>">
            </div>
            <div class="col-6">
              <label class="form-label" for="bulan">Bulan</label>
              <select class="form-select" id="bulan" name="bulan" required>
                <?php for ($b = 1; $b <= 12; $b++): ?>
                  <option value="<?= $b ?>" <?= (int) Session::inputLama('bulan', (string) date('n')) === $b ? 'selected' : '' ?>>
                    <?= e($namaBulan[$b]) ?>
                  </option>
                <?php endfor; ?>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label" for="kategori_id">Kategori destinasi</label>
              <select class="form-select" id="kategori_id" name="kategori_id">
                <option value="">Semua kategori (angka total)</option>
                <?php foreach (Kategori::semua() as $k): ?>
                  <option value="<?= (int) $k['id'] ?>"><?= e($k['nama']) ?></option>
                <?php endforeach; ?>
              </select>
              <div class="form-text">
                Satu baris per periode per kategori. Menginput periode yang
                sama akan memperbarui angkanya, bukan menduplikasi.
              </div>
            </div>
            <div class="col-12">
              <label class="form-label" for="jumlah">Jumlah kunjungan</label>
              <input class="form-control" type="number" id="jumlah" name="jumlah" min="0" required
                     value="<?= e(Session::inputLama('jumlah', '0')) ?>">
            </div>
            <div class="col-12">
              <label class="form-label" for="sumber_data">Sumber data <span class="text-danger">*</span></label>
              <input class="form-control" type="text" id="sumber_data" name="sumber_data" required
                     maxlength="200" placeholder="mis. Laporan bulanan UPT Pantai Koka, Maret 2026"
                     value="<?= e(Session::inputLama('sumber_data')) ?>">
            </div>
            <div class="col-12">
              <button class="btn btn-teal" type="submit">Simpan data</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-7">
    <div class="card">
      <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h2 class="h6 mb-0">Data Tahun <?= (int) $tahun ?></h2>
        <div class="d-flex gap-2">
          <form method="get" class="d-flex gap-1">
            <select class="form-select form-select-sm" name="tahun" onchange="this.form.submit()" aria-label="Pilih tahun">
              <?php
              $tahunOpsi = $tahunTersedia;
              if (!in_array((int) date('Y'), $tahunOpsi, true)) {
                  array_unshift($tahunOpsi, (int) date('Y'));
              }
              foreach ($tahunOpsi as $th): ?>
                <option value="<?= (int) $th ?>" <?= (int) $th === (int) $tahun ? 'selected' : '' ?>><?= (int) $th ?></option>
              <?php endforeach; ?>
            </select>
          </form>
          <a class="btn btn-sm btn-outline-secondary"
             href="<?= e(url('/admin/statistik/ekspor', ['tahun' => $tahun])) ?>">Ekspor CSV</a>
        </div>
      </div>
      <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th scope="col">Periode</th>
              <th scope="col">Kategori</th>
              <th scope="col" class="text-end">Jumlah</th>
              <th scope="col">Sumber</th>
              <th scope="col"></th>
            </tr>
          </thead>
          <tbody>
          <?php if ($daftar === []): ?>
            <tr><td colspan="5" class="text-secondary small p-3">Belum ada data untuk tahun ini.</td></tr>
          <?php else: ?>
            <?php foreach ($daftar as $s): ?>
            <tr>
              <th scope="row" class="fw-normal small"><?= e($namaBulan[(int) $s['bulan']]) ?> <?= (int) $s['tahun'] ?></th>
              <td class="small"><?= e($s['kategori_nama'] ?? 'Semua kategori') ?></td>
              <td class="text-end"><?= e(angka((int) $s['jumlah'])) ?></td>
              <td class="small text-secondary"><?= e(ringkas((string) $s['sumber_data'], 40)) ?></td>
              <td class="text-end">
                <form method="post" action="<?= e(url('/admin/statistik/' . (int) $s['id'] . '/hapus')) ?>"
                      data-konfirmasi="Hapus baris statistik ini?">
                  <?= Csrf::field() ?>
                  <button class="btn btn-sm btn-link text-danger p-0" type="submit">Hapus</button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
