<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCredentialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::create('credentials', function (Blueprint $table) {
            $table->bigInteger('user_id');
            $table->integer('connector_id')->comment('Connector id');
            $table->string('attr_key', 16)->comment('Attribute name such as token, userid, username, or password');
            $table->binary('attr_value')->comment('Encrypted value for the attribute specified');
            $table->string('status', 32)->nullable()->default('UPDATED');
            $table->text('message')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
            $table->softDeletes();
            $table->string('client_id')->nullable()->index('credentials_client_id_fk');

            $table->primary(['user_id', 'connector_id', 'attr_key']);
            $table->index(['connector_id', 'expires_at', 'status'], 'IDX_status_expires_connector');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('credentials');
    }
}
