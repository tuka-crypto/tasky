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
    Schema::create('task_attachments', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('task_id');
        $table->unsignedBigInteger('uploaded_by'); // المستخدم الذي رفع الملف
        $table->string('file_path'); // مكان تخزين الملف
        $table->string('file_name');
        $table->string('file_type')->nullable(); // pdf, jpg, png, docx...
        $table->integer('file_size')->nullable(); // KB or bytes
        $table->timestamps();

        $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
        $table->foreign('uploaded_by')->references('id')->on('users')->onDelete('cascade');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_attachments');
    }
};
