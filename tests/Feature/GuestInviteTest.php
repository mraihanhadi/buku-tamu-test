<?php

namespace Tests\Feature;

use App\Models\Guest;
use App\Models\GuestInvite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestInviteTest extends TestCase
{
    use RefreshDatabase;

    private array $payload = [
        'employee_id' => 'EMP-001',
        'name' => 'Jane Doe',
        'reason' => 'Meeting',
    ];

    public function test_staff_can_mint_an_invite_and_view_its_qr(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('invites.store'));

        $invite = GuestInvite::sole();
        $response->assertRedirect(route('invites.show', $invite));
        $this->assertSame($user->id, $invite->created_by);

        // The show page embeds an inline SVG QR code.
        $this->actingAs($user)->get(route('invites.show', $invite))
            ->assertOk()
            ->assertSee('<svg', false)
            ->assertSee(route('public.guests.create', $invite));
    }

    public function test_guest_can_submit_through_a_valid_invite(): void
    {
        $invite = GuestInvite::mint();

        // A freshly minted invite is permanent (never expires) and active.
        $this->assertTrue($invite->active);
        $this->assertNull($invite->expires_at);

        // Public form is reachable without authentication.
        $this->get(route('public.guests.create', $invite))->assertOk();

        $this->post(route('public.guests.store', $invite), $this->payload)
            ->assertOk()
            ->assertSee('Terima kasih');

        $this->assertDatabaseHas('guests', ['employee_id' => 'EMP-001', 'name' => 'Jane Doe']);
    }

    public function test_staff_can_download_the_qr_as_svg(): void
    {
        $user = User::factory()->create();
        $invite = GuestInvite::mint();

        $this->actingAs($user)->get(route('invites.download', $invite))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/svg+xml')
            ->assertHeader('Content-Disposition', 'attachment; filename="qr-'.$invite->token.'.svg"')
            ->assertSee('<svg', false);
    }

    public function test_staff_can_download_the_qr_as_png(): void
    {
        $user = User::factory()->create();
        $invite = GuestInvite::mint();

        $response = $this->actingAs($user)
            ->get(route('invites.download', ['invite' => $invite, 'format' => 'png']))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png')
            ->assertHeader('Content-Disposition', 'attachment; filename="qr-'.$invite->token.'.png"');

        // Body is a real PNG (starts with the PNG magic bytes).
        $this->assertStringStartsWith("\x89PNG", $response->getContent());
    }

    public function test_invite_is_reusable_by_many_guests(): void
    {
        $invite = GuestInvite::mint();

        // The permanent QR is not burned — a second guest can submit too.
        $this->post(route('public.guests.store', $invite), $this->payload)->assertOk();
        $this->get(route('public.guests.create', $invite))->assertOk();
        $this->post(route('public.guests.store', $invite), [
            'employee_id' => 'EMP-002',
            'name' => 'John Roe',
            'reason' => 'Delivery',
        ])->assertOk();

        $this->assertSame(2, Guest::count());
    }

    public function test_staff_can_deactivate_and_reactivate_an_invite(): void
    {
        $user = User::factory()->create();
        $invite = GuestInvite::mint();

        // Deactivating keeps the same token/QR but rejects submissions.
        $this->actingAs($user)->patch(route('invites.toggle', $invite))->assertRedirect();
        $this->assertFalse($invite->fresh()->active);

        $this->get(route('public.guests.create', $invite))->assertSee('Link tidak berlaku');
        $this->post(route('public.guests.store', $invite), $this->payload)->assertSee('Link tidak berlaku');
        $this->assertSame(0, Guest::count());

        // Reactivating the very same invite makes it work again.
        $this->actingAs($user)->patch(route('invites.toggle', $invite))->assertRedirect();
        $this->assertTrue($invite->fresh()->active);

        $this->post(route('public.guests.store', $invite), $this->payload)->assertOk();
        $this->assertSame(1, Guest::count());
    }

    public function test_revoked_invite_is_rejected(): void
    {
        $invite = GuestInvite::create([
            'token' => 'revoked-token-value',
            'active' => true,
            'revoked_at' => now(),
        ]);

        $this->get(route('public.guests.create', $invite))->assertSee('Link tidak berlaku');
        $this->assertSame(0, Guest::count());
    }

    public function test_unknown_token_returns_404(): void
    {
        $this->get('/g/does-not-exist')->assertNotFound();
    }

    public function test_public_routes_do_not_require_authentication(): void
    {
        $invite = GuestInvite::mint();

        $this->get(route('public.guests.create', $invite))->assertOk();
    }
}
