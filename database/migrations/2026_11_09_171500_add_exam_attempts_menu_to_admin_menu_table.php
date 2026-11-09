<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $table = config('admin.database.menu_table');

        if (!DB::table($table)->where('uri', 'exam-attempts')->exists()) {
            DB::table($table)->insert([
                'parent_id' => 0,
                'order' => 0,
                'title' => 'Lịch sử làm bài',
                'icon' => 'fa-history',
                'uri' => 'exam-attempts',
                'permission' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        $table = config('admin.database.menu_table');

        DB::table($table)->where('uri', 'exam-attempts')->delete();
    }
};
