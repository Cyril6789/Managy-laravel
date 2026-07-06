<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Secret token that lets each technician subscribe to their own agenda
        // (read-only iCalendar feed) from Apple Calendar, Outlook or Google Calendar.
        Schema::table('users', function (Blueprint $table) {
            $table->string('calendar_token', 64)->nullable()->unique()->after('preferences');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('calendar_token');
        });
    }
};
