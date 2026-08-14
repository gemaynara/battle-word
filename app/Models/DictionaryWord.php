<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DictionaryWord extends Model
{
    protected $fillable = [
        'word',
        'length',
        'frequency',
        'is_valid',
        'is_inappropriate',
        'category',
    ];

    protected $casts = [
        'is_valid' => 'boolean',
        'is_inappropriate' => 'boolean',
    ];

    public function scopeValid($query)
    {
        return $query->where('is_valid', true)->where('is_inappropriate', false);
    }

    public function scopeByLength($query, int $length)
    {
        return $query->where('length', $length);
    }

    public function scopeMinLength($query, int $minLength)
    {
        return $query->where('length', '>=', $minLength);
    }
}
