<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Borrow extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'book_id',
        'borrow_date',
        'due_date',
        'return_date',
        'fine_amount',
        'fine_paid',
        'fine_waived',
        'fine_waived_reason',
        'waived_by',
        'status',
        'book_condition',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'borrow_date' => 'date',
            'due_date' => 'date',
            'return_date' => 'date',
            'fine_amount' => 'decimal:2',
            'fine_paid' => 'boolean',
            'fine_waived' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function waivedBy()
    {
        return $this->belongsTo(User::class, 'waived_by');
    }

    public function isLost(): bool
    {
        return $this->status === 'Lost' || $this->book_condition === 'Lost';
    }

    public function isDamaged(): bool
    {
        return $this->book_condition === 'Damaged';
    }

    public function hasUnpaidFine(): bool
    {
        return ! $this->fine_paid && ! $this->fine_waived && (float)$this->fine_amount > 0;
    }

    public function getFineTypeLabelAttribute(): string
    {
        if ($this->isLost()) {
            return __('បាត់បង់សៀវភៅ (Lost Book)');
        }
        if ($this->isDamaged()) {
            return __('ខូចខាតសៀវភៅ (Damaged)');
        }
        return __('សងយឺត (Late Return)');
    }
}
