<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCanvasTokensTable extends Migration
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
            $table->string('user_id', 500)->index();
            $table->string('user_global_id', 500)->index();
            $table->string('access_token');
            $table->string('token_type');
            $table->string('refresh_token');
            $table->integer('expires_in');
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
        Schema::dropIfExists('canvas_tokens');
    }
}
