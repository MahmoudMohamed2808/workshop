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
        Schema::create('problem_spare_part', function (Blueprint $table) {
            $table->id();
            $table->foreignId('problem_id')->constrained()->cascadeOnDelete();
            $table->foreignId('spare_part_id')->constrained()->restrictOnDelete();
            $table->integer('default_quantity')->default(1);
            $table->timestamps();

            $table->unique(['problem_id', 'spare_part_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('problem_spare_part');
    }
};
