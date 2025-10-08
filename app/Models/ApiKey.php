<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ApiKey extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'key',
        'permissions',
        'is_active',
        'last_used_at',
        'expires_at'
    ];

    protected $casts = [
        'permissions' => 'array',
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime'
    ];

    protected $hidden = [
        'key'
    ];

    /**
     * Generate a new API key
     */
    public static function generate(string $name, array $permissions = [], $expiresAt = null): self
    {
        return self::create([
            'name' => $name,
            'key' => 'wapi_' . Str::random(56), // wapi_ prefix + 56 random chars = 61 total
            'permissions' => $permissions,
            'expires_at' => $expiresAt
        ]);
    }

    /**
     * Check if the API key is valid
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Check if the API key has a specific permission
     */
    public function hasPermission(string $permission): bool
    {
        if (empty($this->permissions)) {
            return true; // If no specific permissions set, key has all permissions
        }

        return in_array($permission, $this->permissions) || in_array('*', $this->permissions);
    }

    /**
     * Update last used timestamp
     */
    public function markAsUsed(): void
    {
        $this->update(['last_used_at' => now()]);
    }

    /**
     * Get masked key for display
     */
    public function getMaskedKeyAttribute(): string
    {
        return substr($this->key, 0, 8) . '...' . substr($this->key, -4);
    }
}
