<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FileText extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('file_text', function (Blueprint $table) {
            $table->unsignedBigInteger('speech_text_id')
                ->index();;
            $table->unsignedBigInteger('file_id')
                ->index();;

            $table->foreign('speech_text_id')
                ->references('id')
                ->on('speech_texts')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('file_id')
                ->references('id')
                ->on('alive_files')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('file_text');
    }
}
