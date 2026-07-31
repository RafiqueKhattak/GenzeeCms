<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ToolFaq extends Model
{
    protected $fillable = ['tool_id', 'question', 'answer', 'order'];

    public function tool(): BelongsTo
    {
        return $this->belongsTo(Tool::class);
    }
}
