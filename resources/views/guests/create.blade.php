<x-layouts.app title="Add Entry">
    <h1 class="text-xl font-semibold text-gray-900 mb-6">Tambah data tamu</h1>

    <div class="bg-white shadow rounded-lg p-6 max-w-lg">
        <form method="POST" action="{{ route('guests.store') }}">
            @csrf
            @include('guests.form', ['submit' => 'Save'])
        </form>
    </div>
</x-layouts.app>
