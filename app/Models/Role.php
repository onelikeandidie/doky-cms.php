<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'permissions',
    ];

    protected $casts = [
        'permissions' => 'array'
    ];

    public const PERMISSIONS = [
        // Permissions for managing articles
        'article.create',
        'article.update',
        'article.delete',
        'article.delete.any',
        // Permissions for using the Sync feature
        'sync.download',
        'sync.upload',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * @param value-of<self::PERMISSIONS> $permission
     * @return bool
     */
    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions);
    }

    public function givePermissionTo(string $permission): void
    {
        $permissions = $this->permissions;
        // If the permission is already set, don't do anything
        if (in_array($permission, $permissions)) {
            return;
        }
        $permissions[] = $permission;
        $this->update(['permissions' => $permissions]);
    }

    public function revokePermissionTo(string $permission): void
    {
        $this->update([
            'permissions' => array_filter($this->permissions, fn ($p) => $p !== $permission)
        ]);
    }

    public function syncPermissions(array $permissions): void
    {
        $this->update(['permissions' => $permissions]);
    }
}
