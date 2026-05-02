@component('mail::message')
# Tiket Ditutup

Halo {{ $ticket->user->name ?? 'Pengguna' }},

Tiket Anda dengan nomor **{{ $ticket->nomor_tiket ?? $ticket->id }}** telah ditutup oleh tim kami dengan ringkasan berikut :

- **Status:** Selesai
- **Kategori:** {{ $ticket->kategori ?? optional($ticket->category)->name ?? '-' }}
@if(optional($ticket->rootCauseDetail)->label)
- **Detail root cause:** {{ $ticket->rootCauseDetail->label }}
@endif
- **Closed note:**
{!! nl2br(e($ticket->closed_note ?? '-')) !!}



Salam,  
{{ config('app.name') }}
@endcomponent
