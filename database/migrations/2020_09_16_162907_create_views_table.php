<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateViewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('views', function (Blueprint $table) {
            $table->integer('post_id')->unsigned();
            $table->integer('value')->unsigned()->default(0);
            $table->date('dt');

            $table->foreign('post_id')->references('id')->on('post')->onDelete('cascade');
            $table->unique(['post_id', 'dt']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('views', function (Blueprint $table) {
            Schema::dropIfExists('views');
        });
    }
}
