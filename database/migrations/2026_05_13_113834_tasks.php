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
        Schema::create('tasks', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->text('description')->nullable();
        $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
        $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
        $table->date('deadline')->nullable();
        $table->unsignedBigInteger('assigned_to')->nullable(); // member
        $table->unsignedBigInteger('created_by'); // admin
        $table->timestamps();
        $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
        $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
    });
    }

    public function down(): void
    {

    }
};
