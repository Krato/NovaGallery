<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAlbumsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('el_albums', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('el_photos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('album_id')->unsigned()->index();
            $table->string('path');
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->boolean('enabled')->default(1);
            $table->integer('position');
            $table->foreign('album_id')->references('id')->on('el_albums')->onDelete('cascade');
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
        Schema::dropIfExists('el_photos');
        Schema::dropIfExists('el_albums');
    }
}
