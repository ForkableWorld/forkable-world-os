<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCtTreatmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::create('ct_treatments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100)->unique('treName');
            $table->integer('variable_id')->index('ct_treatments_variables_id_fk');
            $table->timestamp('updated_at')->useCurrent();
            $table->timestamp('created_at')->useCurrent();
            $table->softDeletes();
            $table->integer('number_of_conditions')->nullable();
            $table->integer('number_of_side_effects');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ct_treatments');
    }
}
