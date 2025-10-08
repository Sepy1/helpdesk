@component('mail::message')
# Tiket Diterima

Halo {{ $ticket->user->name ?? 'Pengguna' }},

Tiket Anda telah berhasil diterima dengan rincian berikut:


- **No. Tiket:** {{ $ticket->nomor_tiket ?? $ticket->id }}
- **Judul:** {{ $ticket->kategori ?? optional($ticket->category)->name ?? '-' }}
- **Deskripsi:**
{!! nl2br(e($ticket->deskripsi ?? '-')) !!}


Kami akan memproses tiket ini sesegera mungkin.

Terima kasih,  
{{ config('app.name') }}
@endcomponent
