<x-layouts.app title="Link Undangan">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold text-gray-900">Link Undangan QR</h1>
        <form method="POST" action="{{ route('invites.store') }}">
            @csrf
            <button type="submit"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 cursor-pointer">
                + Buat link baru
            </button>
        </form>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="w-full table-fixed divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-center text-gray-500">
                <tr>
                    <th class="px-4 py-3 font-medium">Token</th>
                    <th class="px-4 py-3 font-medium w-32">Status</th>
                    <th class="px-4 py-3 font-medium w-56">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-center">
                @forelse ($invites as $invite)
                    <tr>
                        <td class="px-4 py-3 text-gray-700 font-mono truncate">{{ Str::limit($invite->token, 16) }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if ($invite->active)
                                <span class="inline-flex items-center rounded-full bg-green-300 px-2.5 py-0.5 text-xs font-medium text-green-800">Aktif</span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-10 py-5 text-xs font-medium text-gray-600">Nonaktif</span>
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
