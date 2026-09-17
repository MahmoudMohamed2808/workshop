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
        Schema::table('problems', function (Blueprint $table) {
            $table->json('suggested_accessories')->nullable()->after('description');
            $table->foreignId('suggested_technician_id')->nullable()->after('suggested_accessories')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('problems', function (Blueprint $table) {
            $table->dropConstrainedForeignId('suggested_technician_id');
            $table->dropColumn('suggested_accessories');
        });
    }
};
