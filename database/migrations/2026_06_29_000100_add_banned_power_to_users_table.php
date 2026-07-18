<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Adds users.banned_power (tinyint, default 0) — the dedicated "can ban" flag
// used by the in-room Ban action, mirroring kick_power / comment_mute_power /
// brd_off_power. Requires DDL grant on bdlive_database (DBA action #53).
return new class extends Migration {
    public function up(): void {
        if (!Schema::hasColumn("users", "banned_power")) {
            Schema::table("users", function (Blueprint $table) {
                $table->tinyInteger("banned_power")->default(0)->after("kick_power");
            });
        }
    }
    public function down(): void {
        if (Schema::hasColumn("users", "banned_power")) {
            Schema::table("users", function (Blueprint $table) {
                $table->dropColumn("banned_power");
            });
        }
    }
};
