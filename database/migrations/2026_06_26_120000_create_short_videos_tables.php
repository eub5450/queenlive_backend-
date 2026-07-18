<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * QueenLive "Moments" — TikTok-style short videos (max 60s, watermarked
 * "QueenLive" + user id). Vertical scroll feed with like / comment / view /
 * gift. Tables are created defensively so the migration is safe to re-run.
 */
return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('short_videos')) {
            Schema::create('short_videos', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('user_id')->index();
                $table->string('video_url', 1024);
                $table->string('thumb_url', 1024)->nullable();
                $table->string('caption', 1000)->nullable();
                $table->unsignedSmallInteger('duration')->default(0); // seconds
                $table->unsignedInteger('width')->default(0);
                $table->unsignedInteger('height')->default(0);
                $table->unsignedBigInteger('views_count')->default(0);
                $table->unsignedBigInteger('likes_count')->default(0);
                $table->unsignedBigInteger('comments_count')->default(0);
                $table->unsignedBigInteger('gifts_count')->default(0);
                $table->unsignedBigInteger('gift_value')->default(0); // total coins received
                // active | processing | blocked | deleted
                $table->string('status', 20)->default('active')->index();
                $table->timestamps();
                $table->index(['status', 'created_at']);
            });
        }

        if (!Schema::hasTable('short_video_likes')) {
            Schema::create('short_video_likes', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('video_id')->index();
                $table->unsignedBigInteger('user_id')->index();
                $table->timestamps();
                $table->unique(['video_id', 'user_id']);
            });
        }

        if (!Schema::hasTable('short_video_comments')) {
            Schema::create('short_video_comments', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('video_id')->index();
                $table->unsignedBigInteger('user_id')->index();
                $table->string('comment', 1000);
                $table->timestamps();
                $table->index(['video_id', 'created_at']);
            });
        }

        if (!Schema::hasTable('short_video_views')) {
            Schema::create('short_video_views', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('video_id')->index();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->timestamps();
                // one counted view per user per video
                $table->unique(['video_id', 'user_id']);
            });
        }

        if (!Schema::hasTable('short_video_gifts')) {
            Schema::create('short_video_gifts', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('video_id')->index();
                $table->unsignedBigInteger('sender_id')->index();
                $table->unsignedBigInteger('receiver_id')->index();
                $table->unsignedBigInteger('gift_id')->nullable();
                $table->unsignedBigInteger('coin')->default(0);
                $table->unsignedInteger('quantity')->default(1);
                $table->timestamps();
                $table->index(['video_id', 'created_at']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('short_video_gifts');
        Schema::dropIfExists('short_video_views');
        Schema::dropIfExists('short_video_comments');
        Schema::dropIfExists('short_video_likes');
        Schema::dropIfExists('short_videos');
    }
};
