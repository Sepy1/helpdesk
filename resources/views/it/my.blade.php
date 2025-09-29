@extends('layouts.app')
@section('title','Tiket Saya (IT)')

@section('content')
<div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 p-6">
  <div class="mb-4">
    <h2 class="text-lg font-semibold text-gray-800">Tiket yang Saya Tangani</h2>
    <p class="text-sm text-gray-500">Hanya tiket dengan <span class="font-medium">it_id = {{ auth()->id() }}</span>.</p>
  </div>

  {{-- Desktop table --}}
  <div class="hidden md:block overflow-x-auto">
    <table class="min-w-full text-sm">
      <thead class="bg-gray-50 text-gray-600">
        <tr>
          <th class="py-3 px-4 text-left">#</th>
          <th class="py-3 px-4 text-left">Nomor</th>
          <th class="py-3 px-4 text-left">Kategori</th>
          <th class="py-3 px-4 text-left">Pembuat</th>
          <th class="py-3 px-4 text-left">Status</th>
          <th class="py-3 px-4 text-left">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        @foreach($tickets as $i => $t)
        <tr class="hover:bg-gray-50">
          <td class="py-3 px-4 text-gray-500">{{ $tickets->firstItem()+$i }}</td>
          <td class="py-3 px-4 font-medium"><a href="{{ route('ticket.show',$t->id) }}" class="text-indigo-600 hover:underline">{{ $t->nomor_tiket }}</a></td>
          <td class="py-3 px-4">{{ $t->kategori }}</td>
          <td class="py-3 px-4">{{ $t->user->name ?? '-' }}</td>
          <td class="py-3 px-4">
            @php $badge = $t->status==='OPEN'?'bg-gray-100 text-gray-700 ring-gray-200':($t->status==='ON_PROGRESS'?'bg-amber-100 text-amber-800 ring-amber-200':'bg-emerald-100 text-emerald-800 ring-emerald-200'); @endphp
            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ring-1 {{ $badge }}">{{ $t->status }}</span>
          </td>
          <td class="py-3 px-4 space-x-1">
            <a href="{{ route('ticket.show',$t->id) }}" class="inline-flex items-center rounded-lg bg-gray-100 px-3 py-1.5 text-gray-800 hover:bg-gray-200">Detail</a>
            @if($t->status==='ON_PROGRESS')
              <form method="POST" class="inline" action="{{ route('it.ticket.release',$t->id) }}">@csrf
                <button class="rounded-lg bg-gray-200 px-3 py-1.5 text-gray-800 hover:bg-gray-300">Lepas</button>
              </form>
              <form method="POST" class="inline" action="{{ route('it.ticket.close',$t->id) }}">@csrf
                <button class="rounded-lg bg-emerald-600 px-3 py-1.5 text-white hover:bg-emerald-700">Tutup</button>
              </form>
            @endif
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  {{-- Mobile cards --}}
  <div class="md:hidden space-y-3">
    @forelse($tickets as $t)
      <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
        <div class="flex items-start justify-between gap-3">
          <div>
            <a href="{{ route('ticket.show',$t->id) }}" class="font-semibold text-indigo-600 hover:underline">{{ $t->nomor_tiket }}</a>
            <div class="mt-1 text-xs text-gray-500">Dibuat: {{ $t->created_at->format('d M Y H:i') }}</div>
          </div>
          @php $badge = $t->status==='OPEN'?'bg-gray-100 text-gray-700 ring-gray-200':($t->status==='ON_PROGRESS'?'bg-amber-100 text-amber-800 ring-amber-200':'bg-emerald-100 text-emerald-800 ring-emerald-200'); @endphp
          <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ring-1 {{ $badge }}">{{ $t->status }}</span>
        </div>
        <div class="mt-3 grid grid-cols-2 gap-2 text-sm">
          <div class="text-gray-500">Kategori</div><div class="font-medium">{{ $t->kategori }}</div>
          <div class="text-gray-500">Pembuat</div><div class="font-medium">{{ $t->user->name ?? '-' }}</div>
        </div>
        <div class="mt-3 flex flex-wrap gap-2">
          <a href="{{ route('ticket.show',$t->id) }}" class="rounded-lg bg-gray-900 px-3 py-2 text-white hover:bg-gray-800">Detail</a>
          @if($t->status==='ON_PROGRESS')
            <form method="POST" action="{{ route('it.ticket.release',$t->id) }}">@csrf
              <button class="rounded-lg bg-gray-200 px-3 py-2 text-gray-800 hover:bg-gray-300">Lepas</button>
            </form>
            <form method="POST" action="{{ route('it.ticket.close',$t->id) }}">@csrf
              <button class="rounded-lg bg-emerald-600 px-3 py-2 text-white hover:bg-emerald-700">Tutup</button>
            </form>
          @endif
        </div>
      </div>
    @empty
      <div class="text-center text-gray-500 py-8">Tidak ada tiket.</div>
    @endforelse
  </div>

  <div class="mt-4">{{ $tickets->links() }}</div>
</div>
@endsection
