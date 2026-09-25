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
        Schema::table('books', function (Blueprint $table) {
            $table->string('pdf_file')->nullable()->after('cover_image');
            $table->boolean('allow_pdf_download')->default(false)->after('pdf_file');
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->string('reservation_code', 30)->nullable()->unique()->after('id');
            $table->dateTime('pickup_deadline')->nullable()->after('reservation_date');
        });

        Schema::table('borrows', function (Blueprint $table) {
            $table->boolean('fine_paid')->default(true)->after('fine_amount');
            $table->string('book_condition', 50)->default('Good')->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn(['pdf_file', 'allow_pdf_download']);
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(['reservation_code', 'pickup_deadline']);
        });

        Schema::table('borrows', function (Blueprint $table) {
            $table->dropColumn(['fine_paid', 'book_condition']);
        });
    }
};
