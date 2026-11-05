<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('exam_attempts') && !Schema::hasColumn('exam_attempts', 'question_ids')) {
            Schema::table('exam_attempts', function (Blueprint $table) {
                $table->longText('question_ids')->nullable()->after('submitted_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('exam_attempts') && Schema::hasColumn('exam_attempts', 'question_ids')) {
            Schema::table('exam_attempts', function (Blueprint $table) {
                $table->dropColumn('question_ids');
            });
        }
    }
};
