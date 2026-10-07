<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NewsImage extends Model
{
    protected $fillable = ['news_id', 'path', 'position'];

    public function news(): BelongsTo
    {
        return $this->belongsTo(News::class);
    }

    public function url(): string
    {
        return asset('storage/'.$this->path);
    }
}