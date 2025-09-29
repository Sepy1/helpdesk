@extends('layouts.app')

@section('content')
<div class="container">
  <h4 class="mb-3">Dashboard IT - Daftar Tiket</h4>

  @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
  @if(session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif

  <div class="table-responsive">
    <table class="table table-striped align-middle">
      <thead>
        <tr>
          <th>#</th>
          <th>Nomor Tiket</th>
          <th>Kategori</th>
          <th>Pembuat</th>
          <th>Status</th>
          <th>IT Handler</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($tickets as $i => $t)
        <tr>
          <td>{{ $i+1 }}</td>
          <td>{{ $t->nomor_tiket }}</td>
          <td>{{ $t->kategori }}</td>
          <td>{{ $t->user->name ?? '-' }}</td>
          <td>
            <span class="badge bg-{{ $t->status=='OPEN' ? 'secondary' : ($t->status=='ON_PROGRESS' ? 'warning' : 'success') }}">
              {{ $t->status }}
            </span>
          </td>
          <td>{{ $t->it->name ?? '-' }}</td>
          <td>
            @if($t->status=='OPEN' || ($t->status=='ON_PROGRESS' && optional($t->it)->id === auth()->id()))
              <form method="POST" action="{{ route('it.ticket.take',$t->id) }}">
                @csrf
                <button class="btn btn-sm btn-primary">Ambil Alih</button>
              </form>
            @else
              <button class="btn btn-sm btn-outline-secondary" disabled>Sudah Diambil</button>
            @endif
          </td>
        </tr>
        @empty
        <tr><td colspan="7" class="text-center text-muted">Belum ada tiket.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
