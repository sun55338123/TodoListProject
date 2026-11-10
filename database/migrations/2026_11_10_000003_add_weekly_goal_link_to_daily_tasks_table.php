<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_tasks', function (Blueprint $table) {
            // 掛勾的週目標。刪除週目標時子任務一併刪除。
            $table->foreignId('weekly_goal_id')->nullable()->after('user_id')->constrained()->cascadeOnDelete();
            // 完成的日期。週目標子任務會延到之後的日期，需要記錄在哪一天完成。
            $table->date('completed_on')->nullable()->after('is_completed');
        });

        // 既有的已完成任務都是單日任務，完成日期即為任務日期。
        DB::table('daily_tasks')->where('is_completed', true)->update(['completed_on' => DB::raw('task_date')]);
    }

    public function down(): void
    {
        Schema::table('daily_tasks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('weekly_goal_id');
            $table->dropColumn('completed_on');
        });
    }
};
