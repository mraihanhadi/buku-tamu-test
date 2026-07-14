<x-layouts.app title="QR Undangan">
    <div class="mb-6">
        <a href="{{ route('invites.index') }}" class="text-sm text-gray-600 hover:text-gray-900">&larr; Kembali</a>
    </div>

    <div class="bg-white shadow rounded-lg p-8 max-w-md mx-auto text-center">
        <h1 class="text-xl font-semibold text-gray-900 mb-1">Scan untuk mengisi buku tamu</h1>
        <p class="text-sm mb-6">
            @if ($invite->active)
                <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">Aktif</span>
            @else
                <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600">Nonaktif — scan tidak diterima</span>
            @endif
        </p>

        <div class="inline-block rounded-lg border border-gray-200 p-4 bg-white">
            {!! \App\Support\QrCode::svg($url, 260) !!}
        </div>

        <div class="mt-4 flex items-center justify-center gap-3">
            <a href="{{ route('invites.download', $invite) }}"
               class="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3" />
                </svg>
                Unduh SVG
            </a>
            <a href="{{ route('invites.download', ['invite' => $invite, 'format' => 'png']) }}"
               class="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-imdigo-700">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3" />
                </svg>
                Unduh PNG
            </a>
        </div>

        <div class="mt-6">
            <label class="block text-sm font-medium text-gray-700 mb-1 text-left">Link</label>
            <input type="text" readonly value="{{ $url }}"
                   onclick="this.select()"
                   class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-700 bg-gray-50 focus:outline-none">
        </div>

        <div class="mt-6 flex items-center justify-center gap-4">
            <form method="POST" action="{{ route('invites.toggle', $invite) }}">
                @csrf
                @method('PATCH')
                <button type="submit"
                        class="rounded-md px-4 py-2 text-sm font-medium {{ $invite->active ? 'text-red-700 hover:text-red-900' : 'text-green-700 hover:text-green-900' }} shadow-sm cursor-pointer {{ $invite->active ? 'bg-amber-600 hover:bg-amber-700' : 'bg-green-600 hover:bg-green-700' }}">
                    {{ $invite->active ? 'Nonaktifkan QR' : 'Aktifkan QR' }}
                </button>
            </form>

            <form method="POST" action="{{ route('invites.destroy', $invite) }}"
                  onsubmit="return confirm('Cabut link ini permanen?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-sm text-red-700 font-medium hover:text-red-900 cursor-pointer">Cabut permanen</button>
            </form>
        </div>
    </div>
</x-layouts.app>
