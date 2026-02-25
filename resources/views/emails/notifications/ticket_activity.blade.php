@component('mail::message')

# {{ $data['title'] ?? 'Notifikasi Tiket' }}

@if(!empty($data['actor_name']))
Dari: **{{ $data['actor_name'] }}**

@endif

@if(isset($data['kind']) && $data['kind'] === 'comment')
{{ $data['body'] ?? '' }}

@else
{{ $data['body'] ?? '' }}

@endif

@if(!empty($data['ticket_no']))
Nomor tiket: **{{ $data['ticket_no'] }}**
@endif

@component('mail::button', ['url' => $data['url'] ?? url('/')])
Lihat Tiket
@endcomponent

Terima kasih,<br>
{{ config('app.name') }}

@endcomponent
