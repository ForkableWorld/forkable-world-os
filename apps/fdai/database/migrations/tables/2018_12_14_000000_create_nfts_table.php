<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNftsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('nfts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index()->comment('user_id');
            $table->morphs('tokenizable');
	        $table->string('chain');
	        $table->string('token_address')->unique();
	        $table->string('token_id')->unique();
	        $table->string('title');
	        $table->string('description');
	        $table->string('social_media_url');
	        $table->integer('quantity');
	        $table->string('minting_address');
	        $table->string('file_url');
	        $table->string('ipfs_cid');
	        $table->string('tx_hash');
	        $table->string('client_id');
	        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists(config('tokenize.nfts_table'));
    }
}
