<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up():void
{
    Schema::create('tasks', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->text('description')->nullable();
        $table->enum('status', ['todo', 'in_progress', 'done'])->default('todo');
        $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
        $table->date('start_date')->nullable();
        $table->date('end_date')->nullable();
        $table->boolean('is_approved')->default(false);
        $table->unsignedBigInteger('project_id');
        $table->unsignedBigInteger('created_by');

        $table->timestamps();

        $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
        $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        $table->foreignId('category_id')->nullable()->constrained('categories');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
