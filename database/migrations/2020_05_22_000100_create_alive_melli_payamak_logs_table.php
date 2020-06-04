<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAliveMelliPayamakLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alive_melli_payamak_logs', function (Blueprint $table) {
            $table->id();

            // author id
            $table->unsignedBigInteger("author_id")
                ->index()
                ->nullable();
            $table->foreign("author_id")
                ->on("users")
                ->references("id")
                ->onDelete("cascade")
                ->onUpdate("cascade");

            $table->string("user_name");
            $table->string("password");
            $table->string("to")->comment("mobile number");
            $table->string("from")->comment("default phone number");
            $table->text("text");
            $table->boolean("is_flash")->default(false);
            $table->integer("response_status_code")->nullable();
            $table->text("response_body")->nullable();

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
        Schema::dropIfExists('alive_melli_payamak_logs');
    }
}
