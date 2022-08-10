<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRundownsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rundowns', function (Blueprint $table) {
            $table->increments('idRundowns');
            $table->string('namaAcara');
            $table->string('lokasi');
            $table->date('tanggal');
            $table->time('waktuMulai');
            $table->time('waktuSelesai');
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
        Schema::dropIfExists('rundown');
    }
}
