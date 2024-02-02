<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
	public function up(){
		Schema::table('units', function(Blueprint $table){
			//			"Code": "%",
			$table->string('code')->nullable()->default(null)->after('id');
			//    "Descriptive_Name": "Percent [Most Common Healthcare Units]",
			$table->string('descriptive_name')->nullable()->default(null)->after('code');
			//    "Code_System": "PH_UnitsOfMeasure_UCUM_Expression",
			$table->string('code_system')->nullable()->default(null)->after('descriptive_name');
			//    "Definition": null,
			$table->string('definition')->nullable()->default(null)->after('code_system');
			//    "Synonym": "%",
			$table->string('synonym')->nullable()->default(null)->after('definition');
			//    "Status": "Active",
			$table->string('status')->nullable()->default(null)->after('synonym');
			//    "Kind_of_Quantity": "Most Common Healthcare Units",
			$table->string('kind_of_quantity')->nullable()->default(null)->after('status');
			//    "Date_Revised": "12/8/2005",
			//    "ConceptID": "Percent",
			$table->string('concept_id')->nullable()->default(null)->after('kind_of_quantity');
			//    "Dimension": "1",
			$table->string('dimension')->nullable()->default(null)->after('concept_id');

		});
	}
	public function down(){
		Schema::table('units', function(Blueprint $table){
			//
		});
	}
};
