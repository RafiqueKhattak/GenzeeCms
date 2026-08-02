<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tool extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id', 'slug', 'title', 'icon', 'component',
        'short_description', 'guide_content', 'keywords',
        'meta_title', 'meta_description', 'og_image',
        'status', 'order', 'published_at',
    ];

    protected $casts = [
        'keywords' => 'array',
        'published_at' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function faqs(): HasMany
    {
        return $this->hasMany(ToolFaq::class)->orderBy('order');
    }

    public function related(): BelongsToMany
    {
        return $this->belongsToMany(Tool::class, 'tool_related', 'tool_id', 'related_tool_id')
            ->withPivot('order')
            ->orderBy('tool_related.order');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
}
