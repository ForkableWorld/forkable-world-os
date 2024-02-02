<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWpTermmetaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::create('wp_termmeta', function (Blueprint $table) {
            $table->bigIncrements('meta_id');
            $table->bigInteger('term_id')->default(0)->index('term_id');
            $table->string('meta_key')->nullable()->index('wp_termmeta_meta_key');
            $table->text('meta_value')->nullable();
            $table->timestamp('updated_at')->useCurrent();
            $table->timestamp('created_at')->useCurrent();
            $table->softDeletes();
            $table->string('client_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wp_termmeta');
    }
}
