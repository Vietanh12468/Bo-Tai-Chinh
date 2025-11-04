<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Tên file lưu trữ
            $table->string('path');     // Đường dẫn lưu file
            $table->string('mime_type')->nullable(); // Kiểu MIME của file
            $table->unsignedBigInteger('size'); // Kích thước file tính bằng bytes
            $table->unsignedBigInteger('created_by')->nullable(); // ID người dùng tải lên
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('image_id')->nullable();

            $table->foreign('image_id')->references('id')->on('files')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['image_id']);
        });

        Schema::dropIfExists('files');
    }
};
