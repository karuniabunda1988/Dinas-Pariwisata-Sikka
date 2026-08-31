<?php
/**
 * Front controller.
 *
 * Sistem Informasi & Peta Interaktif Pariwisata Kabupaten Sikka
 * (c) Karunia Bunda IT Training Center Maumere
 *
 * Seluruh permintaan diarahkan ke berkas ini oleh .htaccess.
 */
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/core/App.php';

App::jalankan();
