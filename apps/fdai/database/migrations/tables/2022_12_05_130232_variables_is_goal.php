<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
	public function up(){
		try {
			Schema::table('variables', function(Blueprint $table){
				$table->enum('is_goal', ['ALWAYS', 'SOMETIMES', 'NEVER', NULL])
					->nullable()
					->comment('The effect of a food on the severity of a symptom is useful because you can control the predictor directly. However, the effect of a symptom on the foods you eat is not very useful.  The foods you eat are not generally an objective end in themselves. ')->change();
			});
		} catch (\Throwable $e){
		    error_log("Error in migration is_goal in variables table: " . $e->getMessage());
		}
	}
	public function down(){
		Schema::table('variables', function(Blueprint $table){
			//
		});
	}
};
