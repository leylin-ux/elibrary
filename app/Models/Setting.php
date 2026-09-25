<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'group',
    ];

    /**
     * Default settings dictionary by domain.
     */
    public static function defaults(): array
    {
        return [
            // 1. Library Information (ព័ត៌មានបណ្ណាល័យ)
            'general' => [
                'library_name' => 'E-Library Smart Management System',
                'library_name_km' => 'ប្រព័ន្ធគ្រប់គ្រងបណ្ណាល័យឆ្លាតវៃ',
                'library_tagline' => 'Designed for Academic & Research Excellence',
                'library_tagline_km' => 'ពង្រីកចំណេះដឹង និងឧត្តមភាពនៃការស្រាវជ្រាវ',
                'library_email' => 'contact@elibrary.edu.kh',
                'library_phone' => '012 889 900 / 023 888 999',
                'library_address' => 'Building A, University Campus, Russian Federation Blvd, Phnom Penh, Cambodia',
                'library_address_km' => 'អគារ A, បរិវេណសាកលវិទ្យាល័យ, មហាវិថីសហព័ន្ធរុស្ស៊ី, រាជធានីភ្នំពេញ',
                'library_hours' => 'Mon - Fri: 7:30 AM - 6:00 PM | Sat: 8:00 AM - 12:00 PM',
                'library_hours_km' => 'ចន្ទ - សុក្រ: ៧:៣០ ព្រឹក - ៦:០០ ល្ងាច | សៅរ៍: ៨:០០ ព្រឹក - ១២:០០ ថ្ងៃត្រង់',
                'library_website' => 'https://elibrary.edu.kh',
                'library_logo' => '/images/logo.png',
            ],

            // 3. Notification Settings (ការកំណត់ការជូនដំណឹង)
            'notifications' => [
                'enable_email_notifications' => '1',
                'notify_new_reservation' => '1',
                'notify_overdue_loans' => '1',
                'reminder_days_before_due' => '2',
                'notify_daily_fine' => '1',
                'enable_sound_alerts' => '1',
                'broadcast_announcement' => 'Welcome to the Smart Library Portal! Please remember to return borrowed books prior to the due date.',
                'broadcast_announcement_km' => 'សូមស្វាគមន៍មកកាន់ប្រព័ន្ធបណ្ណាល័យឆ្លាតវៃ! សូមសងសៀវភៅមុនកាលកំណត់ ដើម្បីទទួលបានសិទ្ធិខ្ចីបន្ត។',
                'enable_broadcast_banner' => '1',
            ],

            // 4. System Settings (ការកំណត់ប្រព័ន្ធ)
            'system' => [
                'standard_loan_days' => '14',
                'max_books_student' => '3',
                'max_books_teacher' => '5',
                'max_books_general' => '3',
                'daily_fine_rate' => '0.50',
                'grace_period_days' => '1',
                'hold_pickup_window_hours' => '48',
                'allow_pdf_downloads' => '1',
                'default_language' => 'km',
                'maintenance_mode' => '0',
            ],

            // 5. Data Backup Settings (ការកំណត់ការបម្រុងទុកទិន្នន័យ)
            'backup' => [
                'backup_disk_path' => 'D:\E-Library-Backups',
                'backup_auto_enabled' => '1',
                'backup_auto_time' => '00:00',
                'backup_include_files' => '1',
                'backup_retention_days' => '30',
                'backup_last_run_at' => '',
                'backup_last_status' => '',
                'backup_last_type' => '',
                'backup_last_filename' => '',
            ],
        ];
    }

    /**
     * Get a setting by key with fallback default.
     */
    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        if ($setting !== null && $setting->value !== null) {
            return $setting->value;
        }

        if ($default !== null) {
            return $default;
        }

        // Search in defaults dictionary
        foreach (static::defaults() as $group => $items) {
            if (array_key_exists($key, $items)) {
                return $items[$key];
            }
        }

        return null;
    }

    /**
     * Set a setting value.
     */
    public static function set(string $key, $value, string $group = 'general')
    {
        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group]
        );
    }

    /**
     * Get all settings merged with default fallbacks, grouped by category.
     */
    public static function getAllGrouped(): array
    {
        $defaults = static::defaults();
        $stored = static::all()->keyBy('key');

        $grouped = [];
        foreach ($defaults as $groupName => $items) {
            $grouped[$groupName] = [];
            foreach ($items as $k => $defVal) {
                $grouped[$groupName][$k] = isset($stored[$k]) ? $stored[$k]->value : $defVal;
            }
        }

        // Add any additional dynamic settings that exist in database but not in defaults
        foreach ($stored as $key => $model) {
            $grp = $model->group ?: 'general';
            if (!isset($grouped[$grp][$key])) {
                $grouped[$grp][$key] = $model->value;
            }
        }

        return $grouped;
    }
}
