<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'author',
        'isbn',
        'category_id',
        'subcategory',
        'education_level',
        'subject',
        'grade',
        'target_academic_year',
        'recommended_major',
        'cover_image',
        'pdf_file',
        'allow_pdf_download',
        'total_copies',
        'available_copies',
        'location_shelf',
        'published_year',
        'description',
        'views_count',
        'downloads_count',
        'is_featured',
    ];

    protected $casts = [
        'target_academic_year' => 'integer',
        'views_count' => 'integer',
        'downloads_count' => 'integer',
        'is_featured' => 'boolean',
        'allow_pdf_download' => 'boolean',
    ];

    public function getTargetAcademicYearLabelAttribute(): string
    {
        return match ((int) $this->target_academic_year) {
            1 => __('ឆ្នាំទី ១ (Year 1)'),
            2 => __('ឆ្នាំទី ២ (Year 2)'),
            3 => __('ឆ្នាំទី ៣ (Year 3)'),
            4 => __('ឆ្នាំទី ៤ (Year 4)'),
            default => __('គ្រប់កម្រិត (All Years)'),
        };
    }

    public function scopeForAcademicYear($query, $year)
    {
        return $query->where(function ($q) use ($year) {
            $q->where('target_academic_year', $year)
              ->orWhereNull('target_academic_year')
              ->orWhere('target_academic_year', 0);
        });
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function borrows()
    {
        return $this->hasMany(Borrow::class);
    }

    public function activeBorrows()
    {
        return $this->hasMany(Borrow::class)->whereIn('status', ['Borrowed', 'Overdue']);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function activeReservations()
    {
        return $this->hasMany(Reservation::class)->whereIn('status', ['Pending', 'Approved']);
    }

    public function isAvailable(): bool
    {
        return $this->available_copies > 0;
    }

    public function isReserved(): bool
    {
        if ($this->relationLoaded('activeReservations')) {
            return $this->activeReservations->isNotEmpty();
        }
        return $this->activeReservations()->exists();
    }

    public function isBorrowed(): bool
    {
        return $this->available_copies <= 0;
    }

    public function hasPdf(): bool
    {
        return !empty($this->pdf_file);
    }
}
