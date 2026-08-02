<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageView extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'path', 'subject_type', 'subject_id', 'country_code', 'referrer_host', 'is_bot', 'viewed_at',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
        'is_bot' => 'boolean',
    ];

    public function scopeHumans($query)
    {
        return $query->where('is_bot', false);
    }

    public function scopeSince($query, $days)
    {
        return $query->where('viewed_at', '>=', now()->subDays($days));
    }
}
