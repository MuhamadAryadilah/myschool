<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'name', 'nis', 'kelas'];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function extracurriculars(): BelongsToMany
    {
        return $this->belongsToMany(Extracurricular::class)->withTimestamps();
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}