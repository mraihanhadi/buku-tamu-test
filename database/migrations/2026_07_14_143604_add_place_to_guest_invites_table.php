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
            // Which place this QR is for. Staff pick it when minting the invite;
            // guests who scan it are recorded as visiting that place.
            $table->string('place')->nullable()->after('active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guest_invites', function (Blueprint $table) {
            $table->dropColumn('place');
        });
    }
};
