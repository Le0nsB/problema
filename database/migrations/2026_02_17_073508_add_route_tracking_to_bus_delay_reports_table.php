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
        Schema::table('bus_delay_reports', function (Blueprint $table) {
            $table->renameColumn('bus_stop_id', 'origin_bus_stop_id');     
            $table->foreignId('destination_bus_stop_id')->nullable()->after('origin_bus_stop_id')->constrained('bus_stops')->onDelete('cascade');
            $table->time('scheduled_arrival_time')->nullable()->after('delay_minutes');
            $table->boolean('arrived_on_time')->nullable()->after('scheduled_arrival_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bus_delay_reports', function (Blueprint $table) {
            $table->dropColumn(['destination_bus_stop_id', 'scheduled_arrival_time', 'arrived_on_time']);
            $table->renameColumn('origin_bus_stop_id', 'bus_stop_id');
        });
    }
};
