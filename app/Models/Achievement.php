<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Achievement extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'description',
        'icon',
        'category',
        'target_value',
        'xp_reward',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_achievements')
            ->withPivot(['current_value', 'unlocked', 'unlocked_at'])
            ->withTimestamps();
    }
}
