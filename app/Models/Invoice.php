<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    public const SEMESTERS = ['ganjil' => 'Ganjil', 'genap' => 'Genap'];

    protected $fillable = ['student_id', 'title', 'amount', 'due_date', 'status', 'paid_at', 'reminded_at', 'semester', 'academic_year'];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'paid_at' => 'datetime',
            'reminded_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function semesterLabel(): ?string
    {
        if (blank($this->semester) && blank($this->academic_year)) {
            return null;
        }

        return trim('Semester '.(self::SEMESTERS[$this->semester] ?? '').' '.$this->academic_year);
    }

    public function isPaid(): bool
    {
        return $this->status === 'lunas';
    }

    /**
     * Filter yang dipakai bersama oleh daftar, rekap, dan ekspor tagihan.
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when(filled($filters['q'] ?? null), function ($query) use ($filters) {
                $q = $filters['q'];
                $query->where(function ($sub) use ($q) {
                    $sub->where('title', 'like', "%{$q}%")
                        ->orWhereHas('student', fn ($s) => $s->where('name', 'like', "%{$q}%"));
                });
            })
            ->when(in_array($filters['semester'] ?? null, ['ganjil', 'genap'], true), fn ($query) => $query->where('semester', $filters['semester']))
            ->when(filled($filters['academic_year'] ?? null), fn ($query) => $query->where('academic_year', $filters['academic_year']))
            ->when(filled($filters['kelas'] ?? null), function ($query) use ($filters) {
                $query->whereHas('student', fn ($s) => $s->where('kelas', $filters['kelas']));
            })
            ->when(($filters['status'] ?? null) === 'lunas', function ($query) {
                $query->where('status', 'lunas');
            })
            ->when(($filters['status'] ?? null) === 'belum_lunas', function ($query) {
                $query->where('status', '!=', 'lunas');
            })
            ->when(($filters['status'] ?? null) === 'terlambat', function ($query) {
                $query->where('status', '!=', 'lunas')->whereDate('due_date', '<', today());
            });
    }
}