@component('mail::message')
# Tiket Ditutup

Halo {{ $ticket->user->name ?? 'Pengguna' }},

Tiket Anda dengan nomor **{{ $ticket->nomor_tiket ?? $ticket->id }}** telah ditindak lanjuti dan ditutup oleh tim kami dengan keterangan sebagai berikut :

- **Status:** Selesai
- **Kategori:** {{ $ticket->kategori ?? optional($ticket->category)->name ?? '-' }}
- **Tindak Lanjut:**
{!! nl2br(e($ticket->closed_note ?? '-')) !!}



Salam,  
{{ config('app.name') }}
@endcomponent
