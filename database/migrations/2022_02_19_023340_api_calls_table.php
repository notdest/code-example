<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ApiCallsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(){
        DB::statement("
            CREATE TABLE `api_calls` (
              `time`        int(11) NOT NULL,
              `sourceId`    int(11) NOT NULL,
              `type`        enum('post','story') NOT NULL
            ) ENGINE=MEMORY MAX_ROWS=200;
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('api_calls');
    }
}
