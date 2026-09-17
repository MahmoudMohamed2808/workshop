<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Maps orders stuck in the removed statuses onto the simplified flow:
     * inspection / waiting_approval / approved -> in_repair, rejected -> cancelled.
     */
    public function up(): void
    {
        DB::table('repair_orders')
            ->whereIn('status', ['inspection', 'waiting_approval', 'approved'])
            ->update(['status' => 'in_repair']);

        DB::table('repair_orders')
            ->where('status', 'rejected')
            ->update(['status' => 'cancelled']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // The original statuses cannot be recovered; only reopen cancelled
        // orders that were migrated from rejected back to in_repair.
    }
};
