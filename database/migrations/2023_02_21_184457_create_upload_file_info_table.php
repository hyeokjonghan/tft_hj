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
        Schema::create('upload_file_info', function (Blueprint $table) {
            $table->id();
            $table->string('upload_type')->comment("파일 업로드 구분");
            $table->integer('target_no')->nullable()->comment("파일 정보를 JOIN하여 가져 와야 할 경우 처리");
            $table->integer('file_sort')->nullable()->comment('다중 업로드시 업로드 된 순서');
            $table->double('file_size')->comment('파일 용량');
            $table->string('file_real_name')->comment('파일 실제 명칭');
            $table->string('file_extension')->comment('파일 확장자');
            $table->string('file_temp_name')->comment('임시 파일 명');
            $table->string('file_path')->comment('파일 위치');
            $table->string('file_s3_path')->comment('s3상 파일 경로');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('upload_file_info');
    }
};
