<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAliveFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alive_files', function (Blueprint $table) {
            $table->id();

            $table->uuid('uuid')->unique();

            // Author
            $table->unsignedBigInteger('author_id')
                ->nullable()
                ->index();
            $table->foreign('author_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->unsignedBigInteger('thumbnail_id')
                ->nullable()
                ->index();
            $table->foreign('thumbnail_id')
                ->on('alive_files')
                ->references('id')
                ->onDelete("set null")
                ->onUpdate('set null');

            $table->string('mime_type');
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->text('description')->nullable();

            $table->string('uri');
            $table->unsignedBigInteger('size');

            // Thumbnail
            $table->boolean('thumbnail')->default(false);

            // Active
            $table->boolean('active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('alive_files');
    }
}
