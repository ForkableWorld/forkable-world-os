<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
	public function up(){
		Schema::table('sent_emails', function(Blueprint $table){
			$table->string('subject', 255)->change();
		});
	}
	public function down(){
		Schema::table('', function(Blueprint $table){
			//
		});
	}
};
