<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocsAPITable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('docs_api', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('model')->nullable();
            $table->string('description', 1024)->nullable();
            $table->string('attribute')->nullable();

            $table->unique(['model', 'attribute'], 'docs_api_table_attribute_uindex');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('docs_api');
    }
}
