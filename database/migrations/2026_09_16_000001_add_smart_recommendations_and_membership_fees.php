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
            $table->unsignedTinyInteger('academic_year')->nullable()->after('member_type'); // 1, 2, 3, 4 for Students
            $table->string('major', 150)->nullable()->after('academic_year'); // e.g. Computer Science, Business, etc.
            $table->dateTime('membership_expires_at')->nullable()->after('major'); // Subscription expiry for external/general patrons
        });

        Schema::table('books', function (Blueprint $table) {
            $table->unsignedTinyInteger('target_academic_year')->nullable()->after('category_id'); // 1, 2, 3, 4 or null (All)
            $table->string('recommended_major', 150)->nullable()->after('target_academic_year');
        });

        Schema::create('membership_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('plan_type', 50); // Monthly, Quarterly, Yearly, Custom
            $table->decimal('amount', 8, 2)->default(0.00);
            $table->string('payment_method', 50)->default('Cash'); // Cash, ABA KHQR, Bank Transfer
            $table->string('reference_no', 50)->unique();
            $table->date('payment_date');
            $table->date('start_date');
            $table->dateTime('expires_at');
            $table->string('status', 30)->default('Paid'); // Paid, Pending, Expired
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('membership_payments');

        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn(['target_academic_year', 'recommended_major']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['academic_year', 'major', 'membership_expires_at']);
        });
    }
};
