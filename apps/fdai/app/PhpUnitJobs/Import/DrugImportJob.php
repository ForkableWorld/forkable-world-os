<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\PhpUnitJobs\Import;
use App\Exceptions\QMFileNotFoundException;
use App\Logging\QMLog;
use App\Models\Variable;
use App\Repos\ReferenceDataRepo;
use App\Types\QMStr;
use App\Slim\Model\QMUnit;
use App\VariableCategories\TreatmentsVariableCategory;
use App\PhpUnitJobs\JobTestCase;
class DrugImportJob extends JobTestCase {
    public function testImport(){
        //QMUnit::updateDatabaseTableFromHardCodedConstants();
        try {
            $data = ReferenceDataRepo::readJsonFile('medications/drug-ndc.json');
        } catch (QMFileNotFoundException $e) {
            le($e);
        }
        //DOPublic::upload('qm-reference-databases/medications/drug-ndc.json', $data);
        foreach($data->results as $datum){
            $unit = QMUnit::getUnitFromString($datum->packaging[0]->description);
            $v = Variable::findOrCreate([
               Variable::FIELD_NAME => $datum->generic_name ." ".$datum->labeler_name,
               Variable::FIELD_DEFAULT_UNIT_ID => $unit->getId(),
               Variable::FIELD_VARIABLE_CATEGORY_ID => TreatmentsVariableCategory::ID,
               Variable::FIELD_SYNONYMS => [
                   $datum->generic_name,
                   $datum->labeler_name,
               ]
            ]);
            foreach($datum->active_ingredients as $ingredient){
                $numbers = QMStr::getNumbersFromString($ingredient->strength);
                if(count($numbers) !== 1){
                    le("ingredient: ".\App\Logging\QMLog::print_r($ingredient, true)."numbers: ".\App\Logging\QMLog::print_r($numbers, true));
                }
                $val = $numbers[0];
                $unitName = QMStr::between($ingredient->strength, " ", "/");
                if(!$unitName){
                    le(\App\Logging\QMLog::print_r($ingredient, true));
                }
                $unit = QMUnit::find($unitName);
                if(!$unit){
                    le($unitName, \App\Logging\QMLog::print_r($ingredient, true));
                }
                try {
                    $v->addIngredientTag($ingredient->name,
                        $val,
                        TreatmentsVariableCategory::NAME,
                        $unit);
                } catch (\Illuminate\Database\QueryException $e) {
                    QMLog::error(__METHOD__.": ".$e->getMessage());
                    continue;
                }
            }
        }
    }
}
