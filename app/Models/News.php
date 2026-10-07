<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class News extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'content',
        'image',
        'author',
        'status',
    ];

    protected static function booted(): void
    {
        // Hapus file gambar dari penyimpanan saat berita dihapus
        static::deleting(function (News $news) {
            Storage::disk('public')->delete($news->images()->pluck('path')->all());
            NewsImage::where('news_id', $news->id)->delete();
        });
    }

    public function images(): HasMany
    {
        return $this->hasMany(NewsImage::class)->orderBy('position')->orderBy('id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    /**
     * Slug unik dari judul: judul, judul-2, judul-3, dan seterusnya.
     */
    public static function makeSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'berita';
        $slug = $base;
        $i = 2;

        while (static::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    public function excerpt(int $limit = 140): string
    {
        return Str::limit(trim(strip_tags($this->content)), $limit);
    }
}