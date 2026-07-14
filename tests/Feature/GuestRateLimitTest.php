<?php

namespace Tests\Feature;

use App\Models\Guest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestRateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_creation_is_throttled_after_five_attempts(): void
    {
        $user = User::factory()->create();

        $payload = [
            'employee_id' => 'EMP-001',
            'name' => 'Jane Doe',
            'reason' => 'Meeting',
        ];

        // First 5 attempts should succeed.
        for ($i = 0; $i < 5; $i++) {
            $this->actingAs($user)
                ->post(route('guests.store'), $payload)
                ->assertSessionHasNoErrors()
                ->assertRedirect(route('guests.index'));
        }

        // The 6th attempt should be blocked by the rate limiter.
        $this->actingAs($user)
            ->post(route('guests.store'), $payload)
            ->assertSessionHasErrors('employee_id');

        // Only the first 5 entries were persisted.
        $this->assertSame(5, Guest::count());
    }
}
