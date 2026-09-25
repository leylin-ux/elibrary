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
        Schema::table('borrows', function (Blueprint $table) {
            $table->boolean('fine_waived')->default(false)->after('fine_paid');
            $table->string('fine_waived_reason')->nullable()->after('fine_waived');
            $table->foreignId('waived_by')->nullable()->after('fine_waived_reason')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('borrows', function (Blueprint $table) {
            $table->dropForeign(['waived_by']);
            $table->dropColumn(['fine_waived', 'fine_waived_reason', 'waived_by']);
        });
    }
};
