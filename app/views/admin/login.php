<div class="container py-5">
  <div class="mx-auto" style="max-width: 420px">
    <div class="text-center mb-4">
      <span class="brand-mark d-inline-grid mb-2" style="width:52px;height:52px;font-size:1.1rem">SK</span>
      <h1 class="h4 mb-1">Panel Admin</h1>
      <p class="small text-secondary mb-0">Dinas Pariwisata Kabupaten Sikka</p>
    </div>

    <div class="card">
      <div class="card-body">
        <form method="post" action="<?= e(url('/admin/login')) ?>">
          <?= Csrf::field() ?>

          <div class="mb-3">
            <label class="form-label" for="username">Nama pengguna</label>
            <input class="form-control" type="text" id="username" name="username"
                   required autocomplete="username" autofocus>
          </div>

          <div class="mb-3">
            <label class="form-label" for="password">Kata sandi</label>
            <input class="form-control" type="password" id="password" name="password"
                   required autocomplete="current-password">
          </div>

          <button class="btn btn-teal w-100" type="submit">Masuk</button>
        </form>
      </div>
    </div>

    <p class="text-center small text-secondary mt-3">
      <a href="<?= e(url('/')) ?>">&larr; Kembali ke situs</a>
    </p>
  </div>
</div>
