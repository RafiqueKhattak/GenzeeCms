<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KeywordSuggestion extends Model
{
    protected $fillable = [
        'keyword', 'source', 'source_url', 'context',
        'suggested_type', 'relevance', 'status', 'used_post_id', 'fetched_at',
    ];

    protected $casts = [
        'fetched_at' => 'datetime',
    ];

    public function usedPost(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'used_post_id');
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'new');
    }
}
