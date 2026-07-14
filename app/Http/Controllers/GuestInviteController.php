<?php

namespace App\Http\Controllers;

use App\Models\GuestInvite;
use App\Support\QrCode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class GuestInviteController extends Controller
{
    /**
     * List the invite links that can still be used.
     */
    public function index(): View
    {
        // Show all invites (active and inactive) so a deactivated QR can be
        // turned back on. Revoked ones are hidden as they are gone for good.
        $invites = GuestInvite::whereNull('revoked_at')->latest()->get();

        return view('invites.index', compact('invites'));
    }

    /**
     * Mint a new invite link and show its QR code.
     */
    public function store(Request $request): RedirectResponse
    {
        $invite = GuestInvite::mint($request->user()->id);

        return redirect()->route('invites.show', $invite);
    }

    /**
     * Show a single invite's shareable link and QR code.
     */
    public function show(GuestInvite $invite): View
    {
        $url = route('public.guests.create', $invite);

        return view('invites.show', compact('invite', 'url'));
    }

    /**
     * Download the invite's QR code as a standalone file. Defaults to SVG
     * (vector, scales cleanly for print); pass ?format=png for a raster that
     * pastes into documents and chat apps.
     */
    public function download(Request $request, GuestInvite $invite): Response
    {
        $url = route('public.guests.create', $invite);
        $png = $request->query('format') === 'png';

        [$body, $type, $ext] = $png
            ? [QrCode::png($url, 512), 'image/png', 'png']
            : [(string) QrCode::svg($url, 512), 'image/svg+xml', 'svg'];

        return response($body, 200, [
            'Content-Type' => $type,
            'Content-Disposition' => 'attachment; filename="qr-'.$invite->token.'.'.$ext.'"',
        ]);
    }

    /**
     * Flip the QR on or off without invalidating it. The token — and therefore
     * the printed QR — is unchanged; only whether it accepts submissions.
     */
    public function toggle(GuestInvite $invite): RedirectResponse
    {
        $invite->active ? $invite->deactivate() : $invite->activate();

        $state = $invite->active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->back()->with('status', "QR {$state}.");
    }

    /**
     * Revoke an invite so its link stops working immediately.
     */
    public function destroy(GuestInvite $invite): RedirectResponse
    {
        $invite->update(['revoked_at' => now()]);

        return redirect()->route('invites.index')->with('status', 'Invite link revoked.');
    }
}
