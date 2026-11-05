<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('exam_results')) {
            Schema::table('exam_results', function ($table) {
                try {
                    $table->dropForeign(['exam_id']);
                } catch (\Throwable $e) {
                    // Ignore if the FK name differs or was already removed.
                }
            });

            DB::statement('ALTER TABLE exam_results MODIFY exam_id INT NULL');
            DB::statement('ALTER TABLE exam_results ADD CONSTRAINT exam_results_exam_id_foreign FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('exam_results')) {
            Schema::table('exam_results', function ($table) {
                try {
                    $table->dropForeign(['exam_id']);
                } catch (\Throwable $e) {
                    // Ignore if the FK name differs or was already removed.
                }
            });

            DB::statement('ALTER TABLE exam_results MODIFY exam_id INT NOT NULL');
            DB::statement('ALTER TABLE exam_results ADD CONSTRAINT exam_results_exam_id_foreign FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE');
        }
    }
};
