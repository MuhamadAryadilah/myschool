<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'phone'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Link WhatsApp (08... dan +62... diubah ke format 62...). Null kalau nomor kosong atau tidak masuk akal.
     */
    public function whatsappUrl(): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $this->phone);

        if ($digits === '' || strlen($digits) < 8) {
            return null;
        }

        if (str_starts_with($digits, '0')) {
            $digits = '62'.substr($digits, 1);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '62'.$digits;
        }

        return 'https://wa.me/'.$digits;
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }
}
