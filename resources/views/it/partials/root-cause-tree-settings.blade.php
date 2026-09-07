<article class="{{ $card }}">
  <div class="{{ $cardHead }}">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
      <div>
        <h2 class="{{ $cardTitle }}">Pengaturan Root Cause</h2>
        <p class="{{ $cardHint }}">Kelola root cause dan detail root cause dalam tampilan tree.</p>
      </div>
      <form method="POST" action="{{ route('it.parameters.rootcause.store') }}" class="flex w-full flex-col gap-2 sm:flex-row lg:max-w-md">
        @csrf
        <input name="name" required class="{{ $input }}" placeholder="Nama root cause baru">
        <button type="submit" class="{{ $btnPrimary }}">Tambah Root Cause</button>
      </form>
    </div>
  </div>

  <div class="max-h-[30rem] overflow-auto p-4 sm:p-5">
    <div class="min-w-[42rem] rounded-lg border border-slate-200 bg-slate-50/60 p-3 sm:p-4">
      @forelse($rootCauses as $rootCause)
        <details class="group/root mb-2 last:mb-0">
          <summary class="flex cursor-pointer list-none items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2.5 shadow-sm hover:border-blue-200 hover:bg-blue-50/40 [&::-webkit-details-marker]:hidden">
            <svg class="h-4 w-4 shrink-0 text-slate-400 transition-transform group-open/root:rotate-90" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L10.94 10 7.23 6.29a.75.75 0 1 1 1.06-1.06l4.24 4.24a.75.75 0 0 1 0 1.06l-4.24 4.24a.75.75 0 0 1-1.08 0Z" clip-rule="evenodd"/></svg>
            <svg class="h-5 w-5 shrink-0 text-rose-500" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a7 7 0 0 0-4 12.74V18a2 2 0 0 0 2 2h4a2 2 0 0 0 2-2v-3.26A7 7 0 0 0 12 2Zm-2 20h4v-1h-4v1Z"/></svg>
            <span class="min-w-0 flex-1 truncate text-sm font-semibold text-slate-900">{{ $rootCause->name }}</span>
            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-500">{{ $rootCause->details->count() }} detail</span>
            <form method="POST" action="{{ route('it.parameters.rootcause.delete', $rootCause->id) }}" onclick="event.stopPropagation()" onsubmit="return confirm('Hapus root cause beserta semua detailnya?');">
              @csrf
              <button type="submit" class="{{ $btnDanger }}">Hapus</button>
            </form>
          </summary>

          <div class="relative ml-5 border-l-2 border-slate-200 pb-1 pl-6 pt-2">
            @foreach($rootCause->details as $detail)
              <div class="relative mb-2 flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 last:mb-0 before:absolute before:-left-6 before:top-1/2 before:h-px before:w-6 before:bg-slate-200">
                <span class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded border border-blue-200 bg-blue-50 text-blue-600">
                  <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 5.29a1 1 0 0 1 .006 1.414l-8 8a1 1 0 0 1-1.414 0l-4-4A1 1 0 0 1 4.71 9.29L8 12.586l7.296-7.296a1 1 0 0 1 1.408 0Z" clip-rule="evenodd"/></svg>
                </span>
                <span class="min-w-0 flex-1 truncate text-sm text-slate-700">{{ $detail->label }}</span>
                @if($detail->is_other)
                  <span class="rounded-md border border-amber-200 bg-amber-50 px-1.5 py-0.5 text-[11px] font-semibold text-amber-700">Lainnya</span>
                @endif
                <form method="POST" action="{{ route('it.parameters.rootcause.detail.delete', $detail) }}" onsubmit="return confirm('Hapus detail ini?');">
                  @csrf
                  <button type="submit" class="{{ $btnDanger }}">Hapus</button>
                </form>
              </div>
            @endforeach

            <form method="POST" action="{{ route('it.parameters.rootcause.detail.store') }}" class="relative mt-2 grid grid-cols-[minmax(0,1fr)_auto_auto] gap-2 rounded-lg border border-dashed border-slate-300 bg-white/70 p-2 before:absolute before:-left-6 before:top-1/2 before:h-px before:w-6 before:bg-slate-200">
              @csrf
              <input type="hidden" name="root_cause_id" value="{{ $rootCause->id }}">
              <input name="label" required maxlength="191" class="{{ $input }}" placeholder="Nama detail root cause baru">
              <label class="inline-flex h-10 items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-700">
                <input type="checkbox" name="is_other" value="1" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                <span>Lainnya</span>
              </label>
              <button type="submit" class="{{ $btnPrimary }}">Tambah Detail</button>
            </form>
          </div>
        </details>
      @empty
        <div class="py-10 text-center">
          <p class="text-sm text-slate-500">Belum ada root cause. Tambahkan root cause pertama melalui form di atas.</p>
        </div>
      @endforelse
    </div>
  </div>
</article>
