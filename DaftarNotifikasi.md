# NOTIFICATIONS

Ringkasan pemicu notifikasi (aplikasi = database, email = Mailable) dalam proyek helpdesk-app.

## Ikhtisar singkat
- Notifikasi aplikasi menggunakan kelas `App\Notifications\TicketActivity` dan dikirim melalui channel `database`.
- Email dikirim melalui `App\Mail\TicketSubmitted` dan `App\Mail\TicketClosed`.

## Pemicu dan lokasi

- Komentar baru pada tiket — notifikasi aplikasi (database) ke peserta tiket (pemilik, IT, vendor)
  - Lokasi: `app/Http/Controllers/TicketCommentController.php` (method `store`)
  - Notification: `app/Notifications/TicketActivity.php`

- Perubahan history tiket (di-trigger dari berbagai aksi IT/vendor seperti `taken`, `released`, `closed`, `reopened`, `progress`, `vendor_followup`, `assigned_vendor`)
  - Lokasi: `app/Http/Controllers/TicketController.php` (method `notifyHistory` dan pemanggilannya seperti `take`, `release`, `close`, `reopen`, `saveProgress`, `vendorFollowup`, `assignVendor`)
  - Notification: `app/Notifications/TicketActivity.php`

- Tiket dibuat — email `TicketSubmitted` dikirim ke pemilik tiket (jika ada alamat email)
  - Lokasi: `app/Http/Controllers/TicketController.php` (method `store`)
  - Mail: `app/Mail/TicketSubmitted.php` (template: `resources/views/emails/tickets/submitted.blade.php`)

- Tiket ditutup — email `TicketClosed` dikirim ke pemilik tiket (jika ada alamat email)
  - Lokasi: `app/Http/Controllers/TicketController.php` (method `close`)
  - Mail: `app/Mail/TicketClosed.php` (template: `resources/views/emails/tickets/closed.blade.php`)

- Assign vendor & vendor follow-up — notifikasi aplikasi via `notifyHistory` (lihat di atas). Tidak ada email otomatis selain history notifications.
  - Lokasi: `app/Http/Controllers/TicketController.php` (`assignVendor`, `vendorFollowup`)

## Catatan teknis
- Semua notifikasi aplikasi memakai channel `database` (lihat `via()` di `TicketActivity`). Jika diperlukan pengiriman email via Notification, `via()` harus ditambahkan/diubah.
- Pengiriman email menggunakan `Mail::to(...)->send()` atau `->queue()` tergantung `config('queue.default')`.
- Penerima notifikasi aplikasi ditentukan oleh logic pada `notifyHistory()` dan pemilihan penerima di `TicketCommentController@store` (pemilik tiket, IT handler, vendor jika ada), dengan pengecualian actor (tidak menerima notifikasi sendiri).

## Cara menguji cepat
1. Jalankan server lokal:

```bash
php artisan serve
```

2. Login sebagai IT / Cabang dan lakukan aksi yang relevan (buat tiket, komentar, close, assign vendor). Periksa:
   - Notifikasi aplikasi di tabel `notifications` (database)
   - Email: bila queue `sync`, periksa log atau inbox penerima; bila queue non-sync, jalankan worker (`php artisan queue:work`).

## File referensi utama
- `app/Notifications/TicketActivity.php`  
- `app/Http/Controllers/TicketController.php`  
- `app/Http/Controllers/TicketCommentController.php`  
- `app/Mail/TicketSubmitted.php`  
- `app/Mail/TicketClosed.php`

Jika ingin, saya bisa pindahkan ringkasan ini ke `README.md` utama atau menambahkan nomor baris/link langsung ke bagian kode.
