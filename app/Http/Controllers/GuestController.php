<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class GuestController extends Controller
{
    /**
     * List all guest book entries.
     */
    public function index(): View
    {
        $guests = Guest::latest()->get();

        return view('guests.index', compact('guests'));
    }

    /**
     * Show the form for creating a new entry.
     */
    public function create(): View
    {
        return view('guests.create');
    }

    /**
     * Store a new entry.
     */
    public function store(Request $request): RedirectResponse
    {
        // Throttle how many entries a single user can add: 5 per minute.
        $throttleKey = 'add-guest|'.$request->user()->id;

        if (RateLimiter::tooManyAttempts($throttleKey, maxAttempts: 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'employee_id' => "Too many entries added. Please try again in {$seconds} seconds.",
            ]);
        }

        RateLimiter::hit($throttleKey, decaySeconds: 60);

        Guest::create($this->validated($request));

        return redirect()->route('guests.index')->with('status', 'Data tamu berhasil ditambahkan.');
    }

    /**
     * Show the form for editing an entry.
     */
    public function edit(Guest $guest): View
    {
        return view('guests.edit', compact('guest'));
    }

    /**
     * Update an entry.
     */
    public function update(Request $request, Guest $guest): RedirectResponse
    {
        $guest->update($this->validated($request));

        return redirect()->route('guests.index')->with('status', 'Data tamu berhasil diperbarui.');
    }

    /**
     * Delete an entry.
     */
    public function destroy(Guest $guest): RedirectResponse
    {
        $guest->delete();

        return redirect()->route('guests.index')->with('status', 'Data tamu berhasil dihapus.');
    }

    /**
     * Validate the request payload.
     *
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'employee_id' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);
    }
}
