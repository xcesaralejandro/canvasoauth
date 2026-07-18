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
        Schema::create('canvas_tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('canvas_user_id')->index();
            $table->string('access_token');
            $table->string('token_type');
            $table->string('refresh_token');
            $table->integer('expires_in');
            $table->timestamps();
            $table->foreign('canvas_user_id')->references('id')->on('canvas_users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('canvas_tokens');
    }
};
