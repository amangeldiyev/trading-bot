<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePositionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->string('symbol', 10);
            $table->integer('amount');
            $table->integer('entry_price');
            $table->integer('mark_price');
            $table->integer('unrealized_pnl');
            $table->integer('liquidation_price');
            $table->tinyInteger('leverage');
            $table->tinyInteger('position_side');
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
        Schema::dropIfExists('positions');
    }
}
