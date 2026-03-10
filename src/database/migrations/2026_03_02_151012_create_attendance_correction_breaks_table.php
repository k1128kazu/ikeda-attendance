<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('attendance_correction_breaks', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('attendance_correction_request_id');

            $table->time('break_start');
            $table->time('break_end');

            $table->timestamps();

            // 外部キー制約名を短く指定（重要）
            $table->foreign(
                'attendance_correction_request_id',
                'acb_acr_id_fk'
            )
                ->references('id')
                ->on('attendance_correction_requests')
                ->cascadeOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('attendance_correction_breaks');
    }
};
