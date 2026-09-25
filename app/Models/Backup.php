<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Backup extends Model
{
    use HasFactory;

    protected $fillable = [
        'filename',
        'disk_path',
        'file_size',
        'backup_type',
        'status',
        'tables_count',
        'files_count',
        'error_message',
        'created_by',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'tables_count' => 'integer',
            'files_count' => 'integer',
            'completed_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function existsOnDisk(): bool
    {
        return file_exists($this->disk_path);
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' B';
    }

    public function scopeManual($query)
    {
        return $query->where('backup_type', 'manual');
    }

    public function scopeAuto($query)
    {
        return $query->where('backup_type', 'auto');
    }

    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }
}
