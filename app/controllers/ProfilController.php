<?php
declare(strict_types=1);

final class ProfilController extends Controller
{
    public function index(): void
    {
        $this->cachePublik(900);
        $this->tampilkan('profil/index', [
            'judul'     => Lang::inggris()
                ? 'About the Sikka Regency Tourism Office'
                : 'Tentang Dinas Pariwisata Kabupaten Sikka',
            'deskripsi' => Lang::inggris()
                ? 'Profile, contact details and public information of the Sikka Regency Tourism Office.'
                : 'Profil, kontak, dan informasi publik Dinas Pariwisata Kabupaten Sikka.',
        ]);
    }
}
