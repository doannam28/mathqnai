<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_attempts', function (Blueprint $table) {
            if (!Schema::hasColumn('exam_attempts', 'type')) {
                $table->tinyInteger('type')->default(0)->after('user_id');
            }

            if (!Schema::hasColumn('exam_attempts', 'topic_title')) {
                $table->string('topic_title')->nullable()->after('type');
            }

            if (!Schema::hasColumn('exam_attempts', 'spent_seconds')) {
                $table->unsignedInteger('spent_seconds')->default(0)->after('time_limit');
            }

            if (!Schema::hasColumn('exam_attempts', 'wrong_count')) {
                $table->unsignedInteger('wrong_count')->default(0)->after('correct_count');
            }

            if (!Schema::hasColumn('exam_attempts', 'status')) {
                $table->tinyInteger('status')->default(1)->after('topic_title');
            }
        });
    }

    public function down(): void
    {
        Schema::table('exam_attempts', function (Blueprint $table) {
            if (Schema::hasColumn('exam_attempts', 'status')) {
                $table->dropColumn('status');
            }

            if (Schema::hasColumn('exam_attempts', 'wrong_count')) {
                $table->dropColumn('wrong_count');
            }

            if (Schema::hasColumn('exam_attempts', 'spent_seconds')) {
                $table->dropColumn('spent_seconds');
            }

            if (Schema::hasColumn('exam_attempts', 'topic_title')) {
                $table->dropColumn('topic_title');
            }

            if (Schema::hasColumn('exam_attempts', 'type')) {
                $table->dropColumn('type');
            }
        });
    }
};
