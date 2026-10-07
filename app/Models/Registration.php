<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Registration extends Model
{
    use HasFactory;

    public const RELIGIONS = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'];

    public const MAX_EXTRACURRICULARS = 2;

    public const STATUSES = ['menunggu' => 'Menunggu', 'diterima' => 'Diterima', 'ditolak' => 'Ditolak'];

    protected $fillable = [
        'status', 'student_name', 'gender', 'birth_place', 'birth_date', 'nisn', 'religion', 'address',
        'previous_school', 'father_name', 'mother_name', 'guardian_name', 'guardian_relation',
        'guardian_job', 'phone', 'email', 'admin_note', 'processed_at', 'student_id', 'user_id',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'processed_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function extracurriculars(): BelongsToMany
    {
        return $this->belongsToMany(Extracurricular::class)->withTimestamps();
    }

    public function isPending(): bool
    {
        return $this->status === 'menunggu';
    }

    public function reference(): string
    {
        return sprintf('PPDB-%06d', $this->id);
    }

    public function genderLabel(): string
    {
        return $this->gender === 'L' ? 'Laki-laki' : 'Perempuan';
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when(filled($filters['q'] ?? null), function ($query) use ($filters) {
                $q = $filters['q'];
                $query->where(function ($sub) use ($q) {
                    $sub->where('student_name', 'like', "%{$q}%")
                        ->orWhere('guardian_name', 'like', "%{$q}%")
                        ->orWhere('father_name', 'like', "%{$q}%")
                        ->orWhere('mother_name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%");
                });
            })
            ->when(
                in_array($filters['status'] ?? null, array_keys(self::STATUSES), true),
                fn ($query) => $query->where('status', $filters['status'])
            );
    }
}