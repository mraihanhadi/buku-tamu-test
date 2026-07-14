<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Isi Buku Tamu</title>
    @vite(['resources/css/app.css'])
</head>
<body class="h-full bg-gray-100 flex items-center justify-center p-4">
    <div class="w-full max-w-sm">
        <div class="bg-white shadow rounded-lg p-8">
            <h1 class="text-2xl font-semibold text-gray-900 text-center mb-6">Buku Tamu</h1>

            @if ($errors->any())
                <div class="mb-4 rounded-md bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('public.guests.store', $invite) }}" class="space-y-4">
                @csrf

                <div>
                    <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-1">NIP</label>
                    <input id="employee_id" name="employee_id" type="text" value="{{ old('employee_id') }}"
                           required autofocus
                           class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none">
                </div>

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}"
                           required
                           class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none">
                </div>

                <div>
                    <label for="reason" class="block text-sm font-medium text-gray-700 mb-1">Keperluan</label>
                    <textarea id="reason" name="reason" rows="3" required
                              class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none">{{ old('reason') }}</textarea>
                </div>

                <button type="submit"
                        class="w-full rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 cursor-pointer">
                    Kirim
                </button>
            </form>
        </div>
    </div>
</body>
</html>
