<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_definitions', function (Blueprint $table): void {
            $table->id();
            $table->string('task_key', 120)->unique();
            $table->string('title', 190);
            $table->text('description')->nullable();
            $table->unsignedInteger('reward_amount')->default(0);
            $table->unsignedInteger('goal')->default(1);
            $table->string('unit', 50)->default('step');
            $table->string('category', 50)->default('daily');
            $table->string('recurrence', 30)->default('once');
            $table->string('progress_resolver', 80)->default('task_progress');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_definitions');
    }
};
