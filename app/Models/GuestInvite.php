<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

#[Fillable(['token', 'active', 'place', 'created_by', 'expires_at', 'used_at', 'revoked_at'])]
class GuestInvite extends Model
{
    /**
     * The fixed set of places a QR invite can be minted for. Staff must pick
     * one of these when generating a link.
     */
    public const PLACES = ['Place A', 'Place B', 'Place C'];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    /**
     * Route-model binding resolves invites by their token, not their id.
     */
    public function getRouteKeyName(): string
    {
        return 'token';
    }

    /**
     * Mint a fresh, permanent invite. The token never changes, so the QR
     * printed from it stays valid indefinitely — it is gated by `active`,
     * not by expiry, and is reusable by any number of guests.
     */
    public static function mint(?string $place = null, ?int $userId = null): self
    {
        return static::create([
            'token' => Str::random(48),
            'active' => true,
            'place' => $place,
            'created_by' => $userId,
        ]);
    }

    /**
     * An invite is usable while it is active and has not been permanently
     * revoked. Reusable — it is never "burned" by a submission.
     */
    public function isUsable(): bool
    {
        return $this->active && $this->revoked_at === null;
    }

    /**
     * Turn the QR on (accept submissions again).
     */
    public function activate(): void
    {
        $this->update(['active' => true]);
    }

    /**
     * Turn the QR off without invalidating it — can be re-activated later.
     */
    public function deactivate(): void
    {
        $this->update(['active' => false]);
    }

    /**
     * Limit a query to invites that can currently be used.
     */
    public function scopeUsable(Builder $query): Builder
    {
        return $query
            ->where('active', true)
            ->whereNull('revoked_at');
    }
}
