<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            /*
             | Лабораторная работа №9: комментарий публикуется только после
             | проверки модератором. По умолчанию false — новый комментарий
             | сразу попадает в очередь модерации.
             */
            $table->boolean('is_approved')->default(false)->after('body');
            $table->timestamp('approved_at')->nullable()->after('is_approved');
            $table->index('is_approved');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->dropIndex(['is_approved']);
            $table->dropColumn(['is_approved', 'approved_at']);
        });
    }
};
