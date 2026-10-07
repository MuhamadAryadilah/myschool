<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Extracurricular extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'schedule', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class)->withTimestamps();
    }

    public function registrations(): BelongsToMany
    {
        return $this->belongsToMany(Registration::class)->withTimestamps();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}