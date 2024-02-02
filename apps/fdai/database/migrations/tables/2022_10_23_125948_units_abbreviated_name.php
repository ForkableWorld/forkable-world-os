<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
	public function up(){
		Schema::table('units', function(Blueprint $table){
			$table->string('abbreviated_name', 40)
				->nullable()->default(null)->after('id')->change();
		});
	}
	public function down(){
		Schema::table('units', function(Blueprint $table){
			//
		});
	}
};
