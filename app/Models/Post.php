<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'type', 'category_id', 'author_id', 'slug', 'title', 'excerpt', 'body',
        'featured_image', 'meta_title', 'meta_description', 'canonical_override',
        'source_name', 'source_url', 'og_image', 'status', 'published_at', 'views',
        'adsense_score', 'adsense_issues',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'adsense_issues' => 'array',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function scopeBlog($query)
    {
        return $query->where('type', 'blog');
    }

    public function scopeNews($query)
    {
        return $query->where('type', 'news');
    }

    public function scopePublished($query)
    {
        return $query->whereIn('status', ['published', 'scheduled'])->where('published_at', '<=', now());
    }
}
