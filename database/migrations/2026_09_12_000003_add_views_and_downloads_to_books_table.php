<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            if (!Schema::hasColumn('books', 'views_count')) {
                $table->unsignedBigInteger('views_count')->default(0)->after('description');
            }
            if (!Schema::hasColumn('books', 'downloads_count')) {
                $table->unsignedBigInteger('downloads_count')->default(0)->after('views_count');
            }
            if (!Schema::hasColumn('books', 'is_featured')) {
                $table->boolean('is_featured')->default(false)->after('downloads_count');
            }
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn(['views_count', 'downloads_count', 'is_featured']);
        });
    }
};
