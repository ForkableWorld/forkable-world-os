<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
	public function up(){
		Schema::table('variable_categories', function(Blueprint $table){
			$table->string('string_id', 64)->nullable();
			$table->string('description', 255)->nullable();
		});
		Schema::table('variables', function(Blueprint $table){
			$table->string('string_id', 125)->nullable();
		});
	}
	public function down(){
	}
};
