<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActionEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		if(Schema::hasTable('action_events')){
			error_log("action_events table already exists");
			return;
		}
         Schema::create('action_events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('batch_id', 36);
            $table->bigInteger('user_id')->index();
            $table->string('name');
            $table->string('actionable_type');
            $table->bigInteger('actionable_id');
            $table->string('target_type');
            $table->bigInteger('target_id');
            $table->string('model_type');
            $table->bigInteger('model_id')->nullable();
            $table->text('fields');
            $table->string('status', 25)->default('running');
            $table->text('exception');
            $table->timestamps();
            $table->text('original')->nullable();
            $table->text('changes')->nullable();

            $table->index(['actionable_type', 'actionable_id']);
            $table->index(['batch_id', 'model_type', 'model_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('action_events');
    }
}
