<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

#[Fillable(['token', 'created_by', 'expires_at', 'used_at', 'revoked_at'])]
class GuestInvite extends Model
{
    protected function casts(): array
    {
        return [
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
     * Mint a fresh, unused invite valid for the given number of hours.
     */
    public static function mint(?int $userId = null, float $hours = 0.1): self
    {
        return static::create([
            'token' => Str::random(48),
            'created_by' => $userId,
            'expires_at' => now()->addHours($hours),
        ]);
    }

    /**
     * An invite is usable while it is neither used, revoked, nor expired.
     */
    public function isUsable(): bool
    {
        if ($this->used_at !== null || $this->revoked_at !== null) {
            return false;
        }

        return $this->expires_at === null || $this->expires_at->isFuture();
    }

    /**
     * Limit a query to invites that can still be used.
     */
    public function scopeUsable(Builder $query): Builder
    {
        return $query
            ->whereNull('used_at')
            ->whereNull('revoked_at')
            ->where(function (Builder $q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }
}
