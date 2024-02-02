<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts;
use App\Files\FileHelper;
use App\Files\Spreadsheet\QMSpreadsheet;
use App\Models\Variable;
use App\VariableCategories\ActivitiesVariableCategory;
use App\VariableCategories\NutrientsVariableCategory;
use App\VariableCategories\SymptomsVariableCategory;
use App\VariableCategories\TreatmentsVariableCategory;
use App\Variables\QMVariable;
abstract class NodeQMChart extends QMChart {
	const TYPE_NETWORKGRAPH = 'networkgraph';
	public static function generateNetworkGraphCSV(){
		$outcomes =
			Variable::whereOutcome(1)->where(Variable::FIELD_VARIABLE_CATEGORY_ID, "<>", ActivitiesVariableCategory::ID)
				->where(Variable::FIELD_VARIABLE_CATEGORY_ID, "=", SymptomsVariableCategory::ID)
				->where(Variable::FIELD_NUMBER_OF_USER_VARIABLES, ">", 10)
				->orderBy(Variable::FIELD_NUMBER_OF_USER_VARIABLES, 'desc')->pluck(Variable::FIELD_NAME);
		$qb = Variable::whereOutcome(0)->where(Variable::FIELD_NUMBER_OF_USER_VARIABLES, ">", 10)
			->orderBy(Variable::FIELD_NUMBER_OF_USER_VARIABLES, 'desc');
		QMVariable::excludeAppsPaymentsWebsitesTestVariablesAndLocations($qb);
		$predictors = $qb->pluck(Variable::FIELD_NAME);
		$questions = [];
		$pairs = [];
		$limit = 50;
		$sankey = "";
		$sampleRate = 1000;
		foreach($outcomes as $outcome){
			foreach($predictors as $predictor){
				$n = rand(0, $sampleRate);
				if($n < $sampleRate - 1){
					continue;
				}
				$pairs[] = [$predictor, $outcome];
				$sankey .= "\n['" . $predictor . "', '" . $outcome . "',1],";
			}
			if(count($pairs) > $limit){
				break;
			}
		}
		QMSpreadsheet::convertToCsvFile($pairs, "tmp/treatment-outcome-network-graph.csv");
		FileHelper::writeByFilePath('tmp/sankey.txt', $sankey);
	}
	public static function generateFromToCSVForCategories(){
		$symptoms = SymptomsVariableCategory::getVariableNames();
		$treatments = TreatmentsVariableCategory::getVariableNames();
		$nutrients = NutrientsVariableCategory::getVariableNames();
		$foods = NutrientsVariableCategory::getVariableNames();
		$questions = [];
		$pairs = [];
		foreach($symptoms as $symptom){
			foreach($treatments as $treatment){
				$pairs[] = [$treatment, $symptom];
			}
			foreach($foods as $food){
				$pairs[] = [$food, $symptom];
			}
		}
		QMSpreadsheet::convertToCsvFile($pairs, "treatment-outcome-network-graph.csv");
	}
	protected function addClickEvent(string $js){
		$all = $this->getSeries();
		foreach($all as $series){
			$series->addClickEvent($js);
		}
	}
	protected function addUrlClickEvent(): void{
		$this->addClickEvent('function(e){
    var p = e.point;
    if(p.url){
        showLoader();
        window.location.href = p.url
    } else if (p.linksTo[0]) {
        showLoader();
        window.location.href = p.linksTo[0].url
    } else if (p.linksFrom[0]) {
        showLoader();
        window.location.href = p.linksFrom[0].url
    } else {
        debugger
        console.error("Could not find url on event:", event);
    }
}');
	}
}
