<x-layouts.app title="Edit Entry">
    <h1 class="text-xl font-semibold text-gray-900 mb-6">Edit data tamu</h1>

    <div class="bg-white shadow rounded-lg p-6 max-w-lg">
        <form method="POST" action="{{ route('guests.update', $guest) }}">
            @csrf
            @method('PUT')
            @include('guests.form', ['submit' => 'Save'])
        </form>
    </div>
</x-layouts.app>
