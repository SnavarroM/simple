<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsOnAnuncioTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('anuncio', function (Blueprint $table) {
            $table->boolean('active_on_front')
                ->default(false);
            $table->boolean('active_on_backend')
                ->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('anuncio', function (Blueprint $table) {
            $table->dropColumn('active_on_front');
            $table->dropColumn('active_on_backend');
        });
    }
}
