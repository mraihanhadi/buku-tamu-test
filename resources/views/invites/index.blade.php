<x-layouts.app title="Link Undangan">
    <div class="flex items-center justify-between mb-6 gap-4">
        <h1 class="text-xl font-semibold text-gray-900">Link Undangan QR</h1>
        <form method="POST" action="{{ route('invites.store') }}" class="flex items-center gap-2">
            @csrf
            <select name="place" required
                    class="rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none">
                <option value="" disabled {{ old('place') ? '' : 'selected' }}>Pilih lokasi…</option>
                @foreach (\App\Models\GuestInvite::PLACES as $place)
                    <option value="{{ $place }}" {{ old('place') === $place ? 'selected' : '' }}>{{ $place }}</option>
                @endforeach
            </select>
            <button type="submit"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 cursor-pointer whitespace-nowrap">
                + Buat link baru
            </button>
        </form>
    </div>

    @error('place')
        <div class="mb-4 rounded-md bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">{{ $message }}</div>
    @enderror

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="w-full table-fixed divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-center text-gray-500">
                <tr>
                    <th class="px-4 py-3 font-medium">Lokasi</th>
                    <th class="px-4 py-3 font-medium w-32">Status</th>
                    <th class="px-4 py-3 font-medium w-56">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-center">
                @forelse ($invites as $invite)
                    <tr>
                        <td class="px-4 py-3 text-gray-700 font-medium truncate">{{ $invite->place ?? '—' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if ($invite->active)
                                <span class="inline-flex items-center rounded-full bg-green-300 px-2.5 py-0.5 text-xs font-medium text-green-800">Aktif</span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-3">
                                <a href="{{ route('invites.show', $invite) }}" class="text-indigo-600 hover:text-indigo-800">Lihat QR</a>
                                <form method="POST" action="{{ route('invites.toggle', $invite) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="font-medium {{ $invite->active ? 'text-red-700 hover:text-red-900' : 'text-green-700 hover:text-green-900' }} cursor-pointer">
                                        {{ $invite->active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('invites.destroy', $invite) }}"
                                      onsubmit="return confirm('Cabut link ini permanen?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-700 font-medium hover:text-red-900 cursor-pointer">Cabut</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-8 text-center text-gray-500">Belum ada link aktif.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.app>
