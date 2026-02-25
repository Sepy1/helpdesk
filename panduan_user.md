# Panduan Pengguna — Helpdesk App

Dokumen ini menjelaskan secara terperinci cara mengoperasikan aplikasi Helpdesk App untuk peran:
- Pengguna (Pelapor)
- Agen / Petugas (Staff yang menindaklanjuti tiket)

Gunakan panduan ini saat membuat tiket, melihat status, memberi komentar, dan menutup tiket.

---

## Bagian A — Panduan Umum

1. Akses aplikasi via browser pada URL yang diberikan oleh administrator.
2. Login menggunakan akun yang telah dibuat. Jika belum memiliki akun, hubungi admin.

### Login

- Buka halaman login.
- Masukkan `Email` dan `Password`.
- Klik tombol `Login`.
- Jika lupa password, klik `Lupa Password` → masukkan email → ikuti instruksi pada email reset.

Catatan: Untuk masalah autentikasi, periksa email verifikasi atau hubungi tim IT.

---

## Bagian B — Untuk Pengguna (Pelapor)

Tujuan: Cara membuat tiket, melampirkan bukti, memantau status, dan merespons permintaan klarifikasi.

### 1. Membuat Tiket Baru

Lokasi: Menu `Buat Tiket` atau tombol `+` pada dashboard.

Langkah-langkah:

1. Klik `Buat Tiket`.
2. Isi kolom:
   - **Judul**: Ringkas dan jelas (contoh: "Printer rusak di Ruang A101").
   - **Kategori**: Pilih kategori yang paling relevan (contoh: Hardware, Software, Jaringan).
   - **Subkategori**: Pilih subkategori yang lebih spesifik jika tersedia.
   - **Prioritas**: Pilih antara Low / Medium / High / Critical. Pilih `High`/`Critical` hanya bila layanan terganggu secara signifikan.
   - **Deskripsi**: Jelaskan masalah secara rinci — langkah yang sudah dicoba, kapan mulai, dampak.
   - **Cabang/Departemen** (jika ada): Pilih lokasi yang terkena dampak.
   - **Lampiran**: Klik `Pilih file` untuk mengunggah foto, log, atau dokumen pendukung. Batasi ukuran sesuai kebijakan (lihat Troubleshooting jika gagal upload).
3. Klik `Kirim` atau `Submit`.

Hasil:
- Tiket dibuat dan Anda menerima notifikasi (email/in-app) berisi ID tiket dan ringkasan.

Tips penulisan deskripsi:
- Cantumkan langkah reproduksi jika masalah teknis.
- Sertakan screenshot atau foto bukti bila memungkinkan.

### 2. Memantau Tiket Saya

Lokasi: Menu `My Tickets` atau `Daftar Tiket`.

Langkah-langkah:

1. Buka `My Tickets` untuk melihat tiket yang Anda kirim.
2. Gunakan filter: `Status`, `Priority`, `Category`, dan `Date` untuk mempersempit daftar.
3. Klik nomor/judul tiket untuk membuka detail.

Detail tiket menampilkan:
- Status saat ini (Submitted, In Progress, Pending, Resolved, Closed).
- Riwayat aktivitas (yang diberi oleh `TicketHistory`): siapa yang mengubah status, kapan, dan catatannya.
- Komentar dan lampiran.

### 3. Menambahkan Komentar atau Merespons Permintaan Informasi

1. Buka detail tiket.
2. Di bagian komentar, tulis pesan Anda.
3. Pilih apakah komentar bersifat publik (pelapor & agent) atau internal (hanya staff) — bagi pengguna biasa pilih publik.
4. Lampirkan file bila perlu.
5. Klik `Kirim`.

Catatan: Saat agen meminta informasi tambahan (komentar atau checklist), Anda akan menerima notifikasi via email atau in-app.

### 4. Menutup Tiket (Feedback)

- Biasanya petugas yang menutup tiket setelah menyelesaikan pekerjaan.
- Pada beberapa workflow, pelapor diminta mengonfirmasi bahwa masalah telah teratasi; buka tiket dan pilih `Konfirmasi Selesai` atau beri komentar bahwa masalah sudah selesai.

---

## Bagian C — Untuk Agen / Petugas

Tujuan: Menangani tiket, menugaskan, mengubah status, menulis catatan teknis, dan menutup tiket.

### 1. Menerima dan Memproses Tiket

1. Buka `Inbox` atau `All Tickets` sesuai peran.
2. Pilih tiket berdasarkan prioritas atau aturan penugasan otomatis.
3. Baca deskripsi dan periksa lampiran.

Langkah tindakan awal:

- Jika informasi tidak cukup: tambahkan komentar dan minta klarifikasi (pilih `Internal` untuk catatan internal atau `Public` untuk meminta info dari pelapor).
- Jika perlu penugasan: gunakan kontrol `Assign` untuk menetapkan ke diri sendiri atau reassign ke teknisi lain.
- Ubah status menjadi `In Progress` setelah mulai menangani.

### 2. Menambahkan Catatan/Log Teknis

1. Klik `Tambah Komentar` atau `Tambah Log`.
2. Isi deskripsi tindakan yang dilakukan, langkah diagnostik, dan hasil.
3. Lampirkan screenshot, log, atau patch jika ada.
4. Simpan.

Catatan: Semua perubahan akan tercatat di `TicketHistory`.

### 3. Menangani Eskalasi dan SLA

- Periksa SLA dan waktu yang tersisa di header tiket. Jika SLA terancam, tambahkan catatan eskalasi dan beri notifikasi ke manajer.
- Gunakan tag/label `escalated` bila perlu.

### 4. Menutup Tiket

1. Pastikan solusi diterapkan dan diverifikasi (oleh pelapor jika diperlukan).
2. Ubah status menjadi `Resolved` atau `Closed`.
3. Tulis ringkasan solusi dan penyebab akar (`Root Cause`) bila ada.

---

## Bagian D — Lampiran Teknis & Pengaturan Upload

- Format lampiran yang umum diterima: `.pdf`, `.jpg`, `.png`, `.docx`, `.xlsx`.
- Jika upload gagal, periksa pesan error. Umumnya disebabkan oleh batas `upload_max_filesize` atau `post_max_size` pada `php.ini` atau pembatasan di aplikasi.

Rekomendasi jika upload gagal:
1. Kompres gambar sebelum upload.
2. Gunakan file-sharing link (Google Drive/OneDrive) di deskripsi jika file terlalu besar.

---

## Bagian E — Notifikasi & Email

- Anda akan menerima notifikasi untuk event penting: tiket dibuat, permintaan info, tiket ditutup.
- Email template diatur oleh administrator; jika tidak menerima email, cek folder spam atau hubungi admin.

---

## Bagian F — Troubleshooting Umum untuk Pengguna

Masalah: Tidak bisa login
- Solusi: Pastikan email terdaftar, reset password, hubungi admin jika akun belum aktif.

Masalah: Tidak bisa upload lampiran
- Solusi: Kurangi ukuran file, gunakan format didukung, atau kirim sebagai link.

Masalah: Tidak menerima notifikasi email
- Solusi: Periksa spam, konfigurasi email di profil, atau hubungi admin.

Masalah: Tiket tidak muncul di `My Tickets`
- Solusi: Pastikan Anda login dengan akun yang benar; periksa filter tanggal/status.

---

## Bagian G — Tip & Etika Pelaporan

- Berikan judul singkat dan jelas.
- Sertakan langkah dan bukti (screenshot) untuk mempercepat penanganan.
- Jangan memberi komentar sampah atau melecehkan petugas — jaga komunikasi profesional.

---

## Bagian H — FAQ Cepat

1. Bagaimana cara mengubah email notifikasi? — Hubungi admin untuk pengaturan global; untuk preferensi per-user, cek profil.
2. Siapa yang bisa melihat komentar internal? — Hanya agen/petugas dan admin.
3. Bisa buat tiket via email? — Jika fitur inbound email diaktifkan, ya. Hubungi admin untuk konfigurasi.

---

## Lampiran & Sumber

- Panduan teknis lain: [panduan_manual.md](panduan_manual.md)
- Jika mau, minta saya menambahkan tangkapan layar langkah per langkah.

---

Terakhir diperbarui: 24 Februari 2026
