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
            $table->string('subcategory', 100)->nullable()->after('category_id'); // ប្រភេទរង
            $table->string('education_level', 100)->nullable()->after('subcategory'); // កម្រិតអប់រំ
            $table->string('subject', 100)->nullable()->after('education_level'); // មុខវិជ្ជា
            $table->string('grade', 100)->nullable()->after('subject'); // ថ្នាក់
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn(['subcategory', 'education_level', 'subject', 'grade']);
        });
    }
};
