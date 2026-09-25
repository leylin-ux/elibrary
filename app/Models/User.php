<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'google_id',
        'password',
        'card_id',
        'member_type',
        'academic_year',
        'major',
        'membership_expires_at',
        'status',
        'photo',
        'phone',
        'address',
        'bio',
        'preferred_locale',
        'notify_email',
        'notify_sound',
        'notify_borrow_reminders',
        'notify_hold_ready',
        'role',
        'notes',
        'last_login_at',
        'first_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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
            'academic_year' => 'integer',
            'membership_expires_at' => 'datetime',
            'last_login_at' => 'datetime',
            'first_login_at' => 'datetime',
            'notify_email' => 'boolean',
            'notify_sound' => 'boolean',
            'notify_borrow_reminders' => 'boolean',
            'notify_hold_ready' => 'boolean',
        ];
    }

    public function hasLoggedIn(): bool
    {
        return ! is_null($this->last_login_at);
    }

    public function membershipPayments()
    {
        return $this->hasMany(MembershipPayment::class);
    }

    public function borrows()
    {
        return $this->hasMany(Borrow::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function activeBorrows()
    {
        return $this->hasMany(Borrow::class)->whereIn('status', ['Borrowed', 'Overdue']);
    }

    public function completedBorrows()
    {
        return $this->hasMany(Borrow::class)->where('status', 'Returned');
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isManager(): bool
    {
        return in_array($this->role, ['admin', 'manager']);
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'manager']);
    }

    public function isMember(): bool
    {
        return ! $this->isManager();
    }

    public function canDeleteBooks(): bool
    {
        return $this->isManager();
    }

    public function canWaiveFines(): bool
    {
        return $this->isManager();
    }

    public function canForceCancelReservations(): bool
    {
        return $this->isManager();
    }

    public function canManagePolicies(): bool
    {
        return $this->isManager();
    }

    public function canModerateMembers(): bool
    {
        return $this->isManager();
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function hasOverdueBooks(): bool
    {
        return $this->borrows()->where('status', 'Overdue')->exists();
    }

    public function unpaidFinesTotal(): float
    {
        return (float) $this->borrows()->where('fine_paid', false)->where('fine_waived', false)->where('fine_amount', '>', 0)->sum('fine_amount');
    }

    public function isStudent(): bool
    {
        return $this->member_type === 'Student';
    }

    public function isTeacher(): bool
    {
        return $this->member_type === 'Teacher';
    }

    public function isExternalMember(): bool
    {
        return $this->member_type === 'General';
    }

    public function hasActiveMembership(): bool
    {
        // Students and Teachers/Staff are internal university members with free library privileges
        if ($this->member_type !== 'General') {
            return true;
        }

        // External patrons must have an active subscription
        return $this->membership_expires_at && \Carbon\Carbon::now()->lte($this->membership_expires_at);
    }

    public function membershipDaysRemaining(): int
    {
        if ($this->member_type !== 'General') {
            return 999;
        }

        if (!$this->membership_expires_at) {
            return 0;
        }

        $diff = \Carbon\Carbon::now()->diffInDays($this->membership_expires_at, false);
        return max(0, (int) $diff);
    }

    public function getAcademicYearLabelAttribute(): string
    {
        return match ((int) $this->academic_year) {
            1 => __('ឆ្នាំទី ១ (Year 1)'),
            2 => __('ឆ្នាំទី ២ (Year 2)'),
            3 => __('ឆ្នាំទី ៣ (Year 3)'),
            4 => __('ឆ្នាំទី ៤ (Year 4)'),
            default => __('គ្រប់ឆ្នាំសិក្សា / ទូទៅ'),
        };
    }

    public function maxAllowedLoans(): int
    {
        return match ($this->member_type) {
            'Teacher' => 10, // VIP (ខ្ចីបាន ១០ ក្បាល / ៣០ ថ្ងៃ)
            'Staff' => 10,
            'General' => 2,
            default => 3, // Student default
        };
    }

    public function canBorrowNewBook(): array
    {
        if ($this->status === 'Suspended') {
            return ['allowed' => false, 'reason' => __('Patron account is currently Suspended.')];
        }
        if ($this->status === 'Temporary') {
            return ['allowed' => false, 'reason' => __('Patron account is temporarily suspended.')];
        }
        if ($this->hasOverdueBooks()) {
            return ['allowed' => false, 'reason' => __('Patron currently has overdue unreturned books.')];
        }
        if ($this->unpaidFinesTotal() > 0) {
            return ['allowed' => false, 'reason' => __('សមាជិកមានប្រាក់ពិន័យមិនទាន់ទូទាត់ (ការសងយឺត ឬបាត់បង់សៀវភៅ): $:amount។ សូមទូទាត់ប្រាក់ពិន័យមុននឹងខ្ចីសៀវភៅថ្មី។', ['amount' => number_format($this->unpaidFinesTotal(), 2)])];
        }
        if ($this->activeBorrows()->count() >= $this->maxAllowedLoans()) {
            return ['allowed' => false, 'reason' => __('Patron has reached their maximum quota limit of :limit books.', ['limit' => $this->maxAllowedLoans()])];
        }
        return ['allowed' => true, 'reason' => ''];
    }
}
