<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // Sửa độ dài cột title
            $table->string('title', 256)->nullable()->change();

            // Thêm cột title_home
            $table->string('title_home', 256)->nullable()->after('title');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // Quay lại cũ nếu rollback
            $table->string('title', 191)->nullable()->change();
            $table->dropColumn('title_home');
        });
    }
};
