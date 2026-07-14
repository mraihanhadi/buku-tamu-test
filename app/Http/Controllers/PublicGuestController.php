<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\GuestInvite;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PublicGuestController extends Controller
{
    /**
     * Show the public create form for a valid invite token.
     */
    public function create(GuestInvite $invite): View
    {
        if (! $invite->isUsable()) {
            return view('public.expired');
        }

        return view('public.create', compact('invite'));
    }

    /**
     * Store an entry submitted through an invite token, then burn the token.
     */
    public function store(Request $request, GuestInvite $invite): View|RedirectResponse
    {
        if (! $invite->isUsable()) {
            return view('public.expired');
        }

        // Throttle submissions per token + IP to blunt abuse of a public link.
        $throttleKey = 'public-guest|'.$invite->token.'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, maxAttempts: 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'employee_id' => "Too many attempts. Please try again in {$seconds} seconds.",
            ]);
        }

        RateLimiter::hit($throttleKey, decaySeconds: 60);

        $data = $request->validate([
            'employee_id' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        // Tag the entry with the place this invite was minted for, so staff
        // can see which location each visitor came to.
        $data['place'] = $invite->place;

        // The invite is permanent and reusable, so it is not burned here — any
        // number of guests may submit while it stays active. The rate limiter
        // above is what blunts abuse of the public link.
        Guest::create($data);

        return view('public.success');
    }
}
