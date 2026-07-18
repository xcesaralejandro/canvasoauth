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
        Schema::create('canvas_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('canvas_client_id');
            $table->string('canvas_id', 500)->index();
            $table->string('canvas_global_id')->index();
            $table->string('name')->nullable();
            $table->string('effective_locale')->nullable();
            $table->boolean('fake_student')->nullable();
            $table->timestamps();
            $table->foreign('canvas_client_id')->references('id')->on('canvas_clients')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('canvas_users');
    }
};
