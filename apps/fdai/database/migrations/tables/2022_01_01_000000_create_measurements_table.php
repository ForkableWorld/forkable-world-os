<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMeasurementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::create('measurements', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id');
            $table->string('client_id', 80)->nullable()->index('measurements_client_id_fk');
            $table->integer('connector_id')->nullable()->index('measurements_connectors_id_fk')->comment('The id for the connector data source from which the measurement was obtained');
            $table->integer('variable_id')->comment('ID of the variable for which we are creating the measurement records');
            $table->integer('start_time')->index()->comment('Start time for the measurement event in ISO 8601');
            $table->float('value', 0, 0)->comment('The value of the measurement after conversion to the default unit for that variable');
            $table->smallInteger('unit_id')->index('fk_measurementUnits')->comment('The default unit for the variable');
            $table->float('original_value', 0, 0)->comment('Value of measurement as originally posted (before conversion to default unit)');
            $table->smallInteger('original_unit_id')->index('measurements_original_unit_id_fk')->comment('Unit id of measurement as originally submitted');
            $table->integer('duration')->nullable()->comment('Duration of the event being measurement in seconds');
            $table->text('note')->nullable()->comment('An optional note the user may include with their measurement');
            $table->float('latitude', 0, 0)->nullable()->comment('Latitude at which the measurement was taken');
            $table->float('longitude', 0, 0)->nullable()->comment('Longitude at which the measurement was taken');
            $table->string('location')->nullable()->comment('location');
            $table->timestamp('created_at')->useCurrent()->comment('Time at which this measurement was originally stored');
            $table->timestamp('updated_at')->useCurrent()->comment('Time at which this measurement was last updated');
            $table->text('error')->nullable()->comment('An error message if there is a problem with the measurement');
            $table->smallInteger('variable_category_id')->index('measurements_variable_category_id_fk')->comment('Variable category ID');
            $table->softDeletes();
            $table->string('source_name', 80)->nullable()->comment('Name of the application or device');
            $table->integer('user_variable_id')->index('measurements_user_variables_user_variable_id_fk');
            $table->timestamp('start_at')->nullable();
            $table->integer('connection_id')->nullable()->index('measurements_connections_id_fk');
            $table->integer('connector_import_id')->nullable()->index('measurements_connector_imports_id_fk');
            $table->string('deletion_reason', 280)->nullable()->comment('The reason the variable was deleted.');
            $table->timestamp('original_start_at')->nullable();

            $table->index(['variable_id', 'user_id'], 'measurements_user_variables_variable_id_user_id_fk');
            $table->index(['variable_id', 'start_time']);
            $table->index(['user_id', 'variable_category_id', 'start_time']);
            $table->unique(['user_id', 'variable_id', 'start_time'], 'measurements_pk');
            $table->index(['variable_id', 'value', 'start_time']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('measurements');
    }
}
