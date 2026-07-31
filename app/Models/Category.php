<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = [
        'type', 'name', 'slug', 'tagline', 'description', 'order',
    ];

    public function tools(): HasMany
    {
        return $this->hasMany(Tool::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}
