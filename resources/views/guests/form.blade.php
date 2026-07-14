@if ($errors->any())
    <div class="mb-4 rounded-md bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
        <ul class="list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="space-y-4">
    <div>
        <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-1">Employee ID</label>
        <input id="employee_id" name="employee_id" type="text"
               value="{{ old('employee_id', $guest->employee_id ?? '') }}"
               required autofocus
               class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none">
    </div>

    <div>
        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
        <input id="name" name="name" type="text"
               value="{{ old('name', $guest->name ?? '') }}"
               required
               class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none">
    </div>

    <div>
        <label for="reason" class="block text-sm font-medium text-gray-700 mb-1">Reason to Visit</label>
        <textarea id="reason" name="reason" rows="3" required
                  class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none">{{ old('reason', $guest->reason ?? '') }}</textarea>
    </div>

    <div class="flex items-center gap-3 pt-2">
        <button type="submit"
                class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 cursor-pointer">
            {{ $submit }}
        </button>
        <a href="{{ route('guests.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
    </div>
</div>
