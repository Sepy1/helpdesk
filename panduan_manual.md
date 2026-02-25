# Panduan Manual — Helpdesk App

Dokumen ini adalah panduan penggunaan dan administrasi untuk aplikasi Helpdesk App.

## Daftar Isi
- Ringkasan Aplikasi
- Persyaratan Sistem & Instalasi
- Struktur Menu & Area Pengguna
- Panduan Fitur Detail
  - Masuk / Registrasi
  - Membuat Tiket
  - Melihat & Menindaklanjuti Tiket
  - Menutup Tiket
  - Komentar & Lampiran
  - Notifikasi
  - Master Data (Admin)
  - Laporan & Ekspor
  - Integrasi Email
  - Keamanan & Akses
- Bagian Admin / Operasional
- Troubleshooting Umum
- FAQ Singkat
- Lampiran Teknis

---

## Ringkasan Aplikasi
- **Nama**: Helpdesk App
- **Tujuan**: Mencatat, melacak, dan menindaklanjuti keluhan/permintaan sampai terselesaikan.
- **Pengguna**: Pelapor (user), petugas/teknisi (agent), admin/manajer, tim IT.

## Persyaratan Sistem & Instalasi

- PHP 8.x atau lebih tinggi
- Composer
- Node.js & npm
- MySQL/MariaDB
- Web server (Apache/Nginx)

Langkah singkat instalasi:

1. Clone repo ke web root (contoh: `c:\laragon\www\helpdesk-app`).
2. Install dependency PHP:

```bash
composer install
```

3. Install frontend dan build:

```bash
npm install
npm run build
```

4. Siapkan environment:

- Salin `.env.example` menjadi `.env` dan sesuaikan `DB_*`, `MAIL_*`, `APP_URL`.
- Generate key aplikasi:

```bash
php artisan key:generate
```

5. Migrasi dan seed data awal:

```bash
php artisan migrate --seed
```

6. Jalankan server (development):

```bash
php artisan serve
npm run dev
```

Catatan: Pastikan permission untuk `storage/` dan `bootstrap/cache/` benar.

## Struktur Menu Utama & Area Pengguna

- **Dashboard**: Ringkasan tiket dan statistik.
- **Buat Tiket**: Form untuk membuat tiket baru.
- **Daftar Tiket**: Inbox, My Tickets, All Tickets dengan filter.
- **Detail Tiket**: Riwayat, komentar, lampiran, log aktivitas.
- **Komentar/Tindak Lanjut**: Tambah komentar publik atau internal.
- **Master Data**: Kelola `Category`, `Subcategory`, `RootCause`, user, role.
- **Notifikasi**: Email dan notifikasi aplikasi.
- **Pengaturan**: Mail, SLA, cabang/department.
- **Laporan/Export**: Ekspor CSV/PDF dan laporan statistik.

## Panduan Fitur Detail

### Masuk / Registrasi

- Login dengan email dan password.
- Fitur lupa password akan mengirim tautan reset melalui email.

### Membuat Tiket

- Lokasi: Menu "Buat Tiket" atau tombol pada dashboard.
- Isi form: Judul, deskripsi, `Category`, `Subcategory`, `Priority`, `Cabang`, lampiran.
- Submit membuat tiket dengan status awal (mis. `Submitted`).
- Notifikasi dikirim kepada pelapor dan petugas sesuai aturan penugasan.

### Melihat & Menindaklanjuti Tiket

- Gunakan filter dan pencarian di daftar tiket.
- Di detail tiket: lihat deskripsi, lampiran, timeline `TicketHistory`.
- Tambah komentar publik atau catatan internal; lampirkan file bila perlu.
- Assign/reassign tiket melalui kontrol `Assignee`.
- Ubah status (In Progress, Pending, Resolved, Closed) dengan catatan jika diperlukan.
- SLA dan escalation dijalankan sesuai pengaturan.

### Menutup Tiket

- Petugas menambahkan catatan akhir lalu ubah status ke `Closed`.
- Opsional: sistem mengirim konfirmasi ke pelapor sebelum penutupan final.

### Komentar & Lampiran

- Dukung file: PDF, JPG, PNG, DOCX (sesuaikan di konfigurasi).
- Periksa `upload_max_filesize` dan `post_max_size` di `php.ini` jika upload gagal.

### Notifikasi

- Email: template di `Mail/` (mis. `TicketSubmitted.php`, `TicketClosed.php`).
- Notifikasi aplikasi: di `Notifications/`.
- Pengaturan dapat diubah via profil atau admin.

### Master Data (Admin)

- Kelola kategori, subkategori, root cause.
- Tambah/kelola user dan role.
- Kelola cabang/department untuk penugasan.

### Laporan & Ekspor

- Laporan standar: tiket per periode, SLA, kategori terbanyak.
- Ekspor hasil filter ke CSV/PDF.

### Integrasi Email

- Inbound: tiket dapat dibuat dari email jika dikonfigurasi.
- Outbound: email notifikasi dikirim sesuai `config/mail.php`.

### Keamanan & Akses

- Gunakan HTTPS di produksi.
- Otorisasi dilakukan via middleware dan `AuthServiceProvider`.
- Audit trail disimpan di `TicketHistory`.

## Bagian Admin / Operasional

- Backup & restore database (mis. `mysqldump`).
- Jalankan worker queue untuk email/notifikasi:

```bash
php artisan queue:work
```

- Jalankan scheduler (cron):

```bash
* * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1
```

## Troubleshooting Umum

- Tidak menerima email: periksa konfigurasi `mail`, queue worker, dan log `storage/logs/laravel.log`.
- Upload gagal: cek `php.ini` dan permission `storage/`.
- Migrasi gagal: jalankan `php artisan migrate:status` dan cek error di terminal.
- Error 500/502: periksa `storage/logs/laravel.log` untuk stacktrace.

## FAQ Singkat

- Mengubah template email: edit di `resources/views/emails/` atau kelas di `Mail/`.
- Melihat log aktivitas tiket: buka detail tiket → tab `History`.
- Menambah peran baru: tambah melalui UI admin atau seed, lalu atur permissions di `AuthServiceProvider`.

## Lampiran Teknis

- Model penting: `app/Models/Ticket.php`, `TicketComment.php`, `TicketHistory.php`, `User.php`.
- Controller: `app/Http/Controllers/` (ticket, user, admin controllers).
- Notifikasi/Email: `app/Notifications/`, `app/Mail/`.
- Views: `resources/views/`.
- Routes: `routes/web.php`, `routes/api.php`, `routes/auth.php`.
- Seeder & Factory: `database/factories/`, `database/seeders/`.

---

## Langkah Berikutnya

- Jika Anda ingin, saya bisa menghasilkan versi PDF/MD yang diformat ulang, menambahkan screenshot untuk alur tertentu, atau menautkan bagian panduan langsung ke file kode di repo.

Terakhir diperbarui: 24 Februari 2026
