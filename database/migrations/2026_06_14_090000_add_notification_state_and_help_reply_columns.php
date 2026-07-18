<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('notifications')) {
            Schema::table('notifications', function (Blueprint $table) {
                if (!Schema::hasColumn('notifications', 'title')) {
                    $table->string('title')->nullable()->after('user_id');
                }

                if (!Schema::hasColumn('notifications', 'notification_type')) {
                    $table->string('notification_type', 50)->nullable()->after('message');
                }

                if (!Schema::hasColumn('notifications', 'accent_color')) {
                    $table->string('accent_color', 20)->nullable()->after('notification_type');
                }

                if (!Schema::hasColumn('notifications', 'is_read')) {
                    $table->tinyInteger('is_read')->default(0)->after('accent_color');
                }

                if (!Schema::hasColumn('notifications', 'read_at')) {
                    $table->timestamp('read_at')->nullable()->after('is_read');
                }

                if (!Schema::hasColumn('notifications', 'help_id')) {
                    $table->unsignedBigInteger('help_id')->nullable()->after('read_at');
                }
            });
        }

        if (Schema::hasTable('helps')) {
            Schema::table('helps', function (Blueprint $table) {
                if (!Schema::hasColumn('helps', 'reply_notification_id')) {
                    $table->unsignedBigInteger('reply_notification_id')->nullable()->after('replay');
                }

                if (!Schema::hasColumn('helps', 'replied_at')) {
                    $table->timestamp('replied_at')->nullable()->after('reply_notification_id');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('notifications')) {
            Schema::table('notifications', function (Blueprint $table) {
                if (Schema::hasColumn('notifications', 'help_id')) {
                    $table->dropColumn('help_id');
                }

                if (Schema::hasColumn('notifications', 'read_at')) {
                    $table->dropColumn('read_at');
                }

                if (Schema::hasColumn('notifications', 'is_read')) {
                    $table->dropColumn('is_read');
                }

                if (Schema::hasColumn('notifications', 'accent_color')) {
                    $table->dropColumn('accent_color');
                }

                if (Schema::hasColumn('notifications', 'notification_type')) {
                    $table->dropColumn('notification_type');
                }

                if (Schema::hasColumn('notifications', 'title')) {
                    $table->dropColumn('title');
                }
            });
        }

        if (Schema::hasTable('helps')) {
            Schema::table('helps', function (Blueprint $table) {
                if (Schema::hasColumn('helps', 'replied_at')) {
                    $table->dropColumn('replied_at');
                }

                if (Schema::hasColumn('helps', 'reply_notification_id')) {
                    $table->dropColumn('reply_notification_id');
                }
            });
        }
    }
};
