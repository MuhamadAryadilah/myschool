<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id', 'user_id', 'proof_path', 'note',
        'status', 'admin_note', 'verified_at', 'amount_paid',
    ];

    protected function casts(): array
    {
        return ['verified_at' => 'datetime', 'amount_paid' => 'integer'];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Selisih nominal yang dibayar terhadap tagihan (positif = lebih, negatif = kurang).
     * Null kalau nominal tidak diisi (data lama).
     */
    public function amountDifference(): ?int
    {
        if ($this->amount_paid === null) {
            return null;
        }

        return (int) $this->amount_paid - (int) $this->invoice->amount;
    }
}