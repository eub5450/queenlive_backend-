<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_claims', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('task_definition_id');
            $table->string('task_key', 120);
            $table->string('cycle_key', 50);
            $table->unsignedInteger('reward_amount')->default(0);
            $table->unsignedBigInteger('balance_before')->default(0);
            $table->unsignedBigInteger('balance_after')->default(0);
            $table->string('claim_ip', 64)->nullable();
            $table->string('claim_user_agent', 500)->nullable();
            $table->timestamp('claimed_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['user_id', 'task_definition_id', 'cycle_key'],
                'task_claims_unique_cycle'
            );
            $table->index(['user_id', 'task_key'], 'task_claims_user_task_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_claims');
    }
};
