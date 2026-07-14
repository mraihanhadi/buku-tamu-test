<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Buku Tamu</title>
    @vite(['resources/css/app.css'])
</head>
<body class="h-full bg-gray-100">
    <nav class="bg-white shadow">
        <div class="max-w-4xl mx-auto px-6 py-3 flex items-center justify-between">
            <a href="{{ route('guests.index') }}" class="font-semibold text-gray-900">Buku Tamu</a>
            <div class="flex items-center gap-4 text-sm">
                <a href="{{ route('invites.index') }}" class="text-gray-600 hover:text-gray-900">Link QR</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-gray-600 hover:text-gray-900 cursor-pointer">Log out</button>
                </form>
            </div>
        </div>
    </nav>

    <main class="max-w-4xl mx-auto p-6">
        @if (session('status'))
            <div class="mb-4 rounded-md bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
                {{ session('status') }}
            </div>
        @endif

        {{ $slot }}
    </main>
</body>
</html>
