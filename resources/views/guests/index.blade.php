<x-layouts.app title="Buku Tamu">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold text-gray-900">List Tamu</h1>
        <a href="{{ route('guests.create') }}"
           class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700">
            + Tambah tamu
        </a>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="w-full table-fixed divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-center text-gray-500">
                <tr>
                    <th class="px-4 py-3 font-medium w-32">NIP</th>
                    <th class="px-4 py-3 font-medium w-1/4">Nama</th>
                    <th class="px-4 py-3 font-medium">Keperluan</th>
                    <th class="px-4 py-3 font-medium w-32">Tanggal</th>
                    <th class="px-4 py-3 font-medium w-24">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-center">
                @forelse ($guests as $guest)
                    <tr>
                        <td class="px-4 py-3 text-gray-700 break-words">{{ $guest->employee_id }}</td>
                        <td class="px-4 py-3 text-gray-900 font-medium break-words">{{ $guest->name }}</td>
                        <td class="px-4 py-3 text-gray-700 break-words">{{ $guest->reason }}</td>
                        <td class="px-4 py-3 text-gray-500 whitespace-nowrap">{{ $guest->created_at->format('d M Y H:i') }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-3">
                                <a href="{{ route('guests.edit', $guest) }}" class="text-indigo-600 hover:text-indigo-800">Edit</a>
                                <form method="POST" action="{{ route('guests.destroy', $guest) }}"
                                      onsubmit="return confirm('Delete this entry?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-700 font-medium hover:text-red-900 cursor-pointer">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">Belum ada tamu.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.app>
