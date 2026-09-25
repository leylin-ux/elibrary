<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('address', 255)->nullable()->after('phone');
            $table->text('bio')->nullable()->after('address');
            $table->string('preferred_locale', 10)->default('km')->after('bio');
            $table->boolean('notify_email')->default(true)->after('preferred_locale');
            $table->boolean('notify_sound')->default(true)->after('notify_email');
            $table->boolean('notify_borrow_reminders')->default(true)->after('notify_sound');
            $table->boolean('notify_hold_ready')->default(true)->after('notify_borrow_reminders');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'address',
                'bio',
                'preferred_locale',
                'notify_email',
                'notify_sound',
                'notify_borrow_reminders',
                'notify_hold_ready',
            ]);
        });
    }
};
