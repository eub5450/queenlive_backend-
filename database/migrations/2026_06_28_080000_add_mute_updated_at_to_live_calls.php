<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        if (!Schema::hasColumn('live_calls', 'mute_updated_at')) {
            Schema::table('live_calls', function (Blueprint $t) {
                $t->timestamp('mute_updated_at')->nullable()->after('mute_time');
                $t->index(['channelName', 'mute_updated_at'], 'live_calls_channel_mute_updated_idx');
            });
        }
    }
    public function down(): void {
        if (Schema::hasColumn('live_calls', 'mute_updated_at')) {
            Schema::table('live_calls', function (Blueprint $t) {
                $t->dropIndex('live_calls_channel_mute_updated_idx');
                $t->dropColumn('mute_updated_at');
            });
        }
    }
};
