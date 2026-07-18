<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_progress', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('task_key', 120);
            $table->string('cycle_key', 50);
            $table->unsignedInteger('progress')->default(0);
            $table->json('meta')->nullable();
            $table->timestamp('last_tracked_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'task_key', 'cycle_key'], 'task_progress_unique_cycle');
            $table->index(['task_key', 'cycle_key'], 'task_progress_task_cycle_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_progress');
    }
};
