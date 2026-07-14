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
        Schema::table('tasks', function (Blueprint $table) {
            Schema::table('tasks', function (Blueprint $table) {

            $table->foreignId('project_id')
                    ->nullable()
                    ->change();

            $table->foreignId('owner_id')
                    ->nullable()
                    ->after('project_id')
                    ->constrained('users')
                    ->cascadeOnDelete();

            $table->enum('task_type', [
                'project',
                'personal'
            ])->default('project');
        });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['owner_id']);
            $table->dropColumn('owner_id');
            $table->dropColumn('task_type');

            $table->foreignId('project_id')->nullable(false)->change();
        });
    }
};
