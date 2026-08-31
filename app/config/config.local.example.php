<?php
/**
 * Salin berkas ini menjadi config.local.php di server produksi (cPanel),
 * lalu isi kredensial basis data hosting. Berkas config.local.php
 * diabaikan oleh git.
 */
return [
    'db' => [
        'host' => 'localhost',
        'name' => 'namauser_sikka',
        'user' => 'namauser_dbuser',
        'pass' => 'KATA_SANDI_DATABASE',
    ],
    'app' => [
        'debug' => false,
    ],
];
