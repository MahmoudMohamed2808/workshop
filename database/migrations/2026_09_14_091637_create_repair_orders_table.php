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
        Schema::create('repair_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->foreignId('device_type_id')->constrained()->restrictOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('device_model_id')->nullable()->constrained()->nullOnDelete();
            $table->string('serial_number')->nullable();
            $table->text('device_condition')->nullable();
            $table->json('accessories')->nullable();
            $table->text('customer_complaint')->nullable();
            $table->text('technician_diagnosis')->nullable();
            $table->foreignId('technician_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('received');
            $table->text('rejection_reason')->nullable();
            $table->date('estimated_completion_date')->nullable();
            $table->decimal('labor_cost', 10, 2)->default(0);
            $table->decimal('additional_cost', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total_cost', 10, 2)->default(0);
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repair_orders');
    }
};
