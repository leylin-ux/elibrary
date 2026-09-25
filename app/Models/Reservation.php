<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_code',
        'user_id',
        'book_id',
        'reservation_date',
        'pickup_deadline',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'reservation_date' => 'date',
            'pickup_deadline' => 'datetime',
        ];
    }

    protected static function booted()
    {
        static::creating(function ($reservation) {
            if (empty($reservation->reservation_code)) {
                $reservation->reservation_code = 'RES-' . strtoupper(substr(uniqid(), -6));
            }
        });
    }

    public function isExpired(): bool
    {
        return $this->pickup_deadline && now()->gt($this->pickup_deadline) && $this->status === 'Approved';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }
}
