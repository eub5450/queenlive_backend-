<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScheduledFrameRulesTables extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('scheduled_frame_rules')) {
            Schema::create('scheduled_frame_rules', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->unsignedBigInteger('entry_frame_id')->nullable();
                $table->string('frame_name')->nullable();
                $table->string('frame_image')->nullable();
                $table->string('frame_effect');
                $table->string('top_type', 50);
                $table->string('metric_type', 50);
                $table->string('condition_type', 50)->default('top_rank');
                $table->decimal('target_value', 20, 2)->default(0);
                $table->unsignedInteger('top_limit')->default(1);
                $table->string('schedule_type', 30)->default('custom');
                $table->dateTime('campaign_starts_at');
                $table->dateTime('campaign_ends_at');
                $table->text('notes')->nullable();
                $table->unsignedTinyInteger('status')->default(1);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->dateTime('last_synced_at')->nullable();
                $table->string('last_window_key')->nullable();
                $table->timestamps();

                $table->index(array('status', 'campaign_starts_at', 'campaign_ends_at'), 'scheduled_frame_rules_active_idx');
                $table->index('entry_frame_id', 'scheduled_frame_rules_frame_idx');
            });
        }

        if (!Schema::hasTable('scheduled_frame_rule_winners')) {
            Schema::create('scheduled_frame_rule_winners', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('scheduled_frame_rule_id');
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('my_beg_id')->nullable();
                $table->unsignedBigInteger('previous_my_beg_id')->nullable();
                $table->string('previous_frame_effect')->nullable();
                $table->string('frame_effect');
                $table->string('frame_name')->nullable();
                $table->string('frame_image')->nullable();
                $table->decimal('metric_value', 20, 2)->default(0);
                $table->string('agency_code')->nullable();
                $table->string('period_key');
                $table->dateTime('window_starts_at');
                $table->dateTime('window_ends_at');
                $table->dateTime('applied_at')->nullable();
                $table->dateTime('removed_at')->nullable();
                $table->string('status', 30)->default('active');
                $table->timestamps();

                $table->unique(
                    array('scheduled_frame_rule_id', 'user_id', 'period_key'),
                    'scheduled_frame_rule_winners_unique'
                );
                $table->index(array('user_id', 'removed_at'), 'scheduled_frame_rule_winners_user_idx');
                $table->index(array('scheduled_frame_rule_id', 'removed_at'), 'scheduled_frame_rule_winners_rule_idx');
                $table->index(array('window_starts_at', 'window_ends_at'), 'scheduled_frame_rule_winners_window_idx');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('scheduled_frame_rule_winners');
        Schema::dropIfExists('scheduled_frame_rules');
    }
}
