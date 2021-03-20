<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlayersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("team_id")->nullable();
            $table->string("first_name",80);
            $table->string("last_name",80);
            $table->string("position",80)->default("midfielder");
            $table->string("country",200);
            $table->unsignedTinyInteger("age");
            $table->double("market_value")->default(1000000);
            $table->timestamps();
            $table->foreign("team_id")
                ->on("teams")->references("id")
                ->onDelete("CASCADE");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('players');
    }
}
