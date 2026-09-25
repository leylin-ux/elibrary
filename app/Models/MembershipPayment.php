<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MembershipPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plan_type',
        'amount',
        'payment_method',
        'reference_no',
        'payment_date',
        'start_date',
        'expires_at',
        'status',
        'notes',
        'recorded_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
        'start_date' => 'date',
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function isExpired(): bool
    {
        if ($this->status === 'Expired') {
            return true;
        }
        return $this->expires_at && Carbon::now()->gt($this->expires_at);
    }

    public function daysRemaining(): int
    {
        if (!$this->expires_at) {
            return 0;
        }
        $diff = Carbon::now()->diffInDays($this->expires_at, false);
        return max(0, (int) $diff);
    }

    public function getPlanLabelAttribute(): string
    {
        return match ($this->plan_type) {
            'Monthly' => __('Monthly Plan (ប្រចាំខែ)'),
            'Quarterly' => __('Quarterly Plan (ប្រចាំត្រីមាស)'),
            'Yearly' => __('Yearly Plan (ប្រចាំឆ្នាំ)'),
            default => __($this->plan_type),
        };
    }
}
