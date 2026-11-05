<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('exam_attempts')) {
            Schema::table('exam_attempts', function ($table) {
                try {
                    $table->dropForeign(['exam_id']);
                } catch (\Throwable $e) {
                    // Ignore if the FK name differs or was already removed.
                }
            });

            DB::statement('ALTER TABLE exam_attempts MODIFY exam_id INT NULL');
            DB::statement('ALTER TABLE exam_attempts ADD CONSTRAINT exam_attempts_exam_id_foreign FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('exam_attempts')) {
            Schema::table('exam_attempts', function ($table) {
                try {
                    $table->dropForeign(['exam_id']);
                } catch (\Throwable $e) {
                    // Ignore if the FK name differs or was already removed.
                }
            });

            DB::statement('ALTER TABLE exam_attempts MODIFY exam_id INT NOT NULL');
            DB::statement('ALTER TABLE exam_attempts ADD CONSTRAINT exam_attempts_exam_id_foreign FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE');
        }
    }
};
