<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('checkin_rewards')) {
            Schema::create('checkin_rewards', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('day')->unique();
                $table->unsignedInteger('reward_amount')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('user_checkins')) {
            Schema::create('user_checkins', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->unique();
                $table->unsignedInteger('streak')->default(0);
                $table->date('last_checkin_date')->nullable();
                $table->unsignedBigInteger('total_claimed')->default(0);
                $table->timestamps();
                $table->index('user_id');
            });
        }

        // Seed a default 7-day ladder only if empty.
        if (DB::table('checkin_rewards')->count() === 0) {
            $ladder = [10, 20, 30, 50, 80, 120, 200];
            $rows = [];
            foreach ($ladder as $i => $amount) {
                $rows[] = [
                    'day' => $i + 1,
                    'reward_amount' => $amount,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            DB::table('checkin_rewards')->insert($rows);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_checkins');
        Schema::dropIfExists('checkin_rewards');
    }
};
