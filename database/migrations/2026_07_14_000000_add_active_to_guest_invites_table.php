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
        Schema::table('guest_invites', function (Blueprint $table) {
            // Reversible on/off switch. A permanent QR stays valid while active
            // and can be turned back on after being deactivated.
            $table->boolean('active')->default(true)->after('token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guest_invites', function (Blueprint $table) {
            $table->dropColumn('active');
        });
    }
};
