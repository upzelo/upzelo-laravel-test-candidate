<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Added some indexes to the tasks table to improve query performance.
     */
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // filtering queries
            $table->index('status', 'tasks_status_index');

            // composite index for getting tasks by project and status
            $table->index(['project_id', 'status'], 'tasks_project_id_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex('tasks_status_index');
            $table->dropIndex('tasks_project_id_status_index');
        });
    }
};