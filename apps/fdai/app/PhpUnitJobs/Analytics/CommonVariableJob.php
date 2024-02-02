<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\PhpUnitJobs\Analytics;
use App\Models\BaseModel;
use App\Models\Variable;
use App\PhpUnitJobs\JobTestCase;
use App\Properties\Variable\VariableIsPublicProperty;
use App\Properties\Variable\VariableManualTrackingProperty;
use App\Properties\Variable\VariableSlugProperty;
use App\Variables\QMCommonVariable;
class CommonVariableJob extends JobTestCase{
    public const TAG_COUNT_IMPLEMENTED = false; // TODO: Figure out a way to update tag count without destroying MySQL
    public function testCommonVariableAnalysisJob(){
        VariableManualTrackingProperty::fixInvalidRecords();
        VariableSlugProperty::fixNulls();
        //ModelFile::generateModelsForTablesStartingWith("ct_");
        $classes = BaseModel::getClassesLike("Ct");
        VariableIsPublicProperty::updateAll();
        QMCommonVariable::analyzeWaitingStaleStuck();
        if(self::TAG_COUNT_IMPLEMENTED){
            $whereTagsNull = Variable::query()
                ->whereNull(Variable::FIELD_NUMBER_OF_COMMON_TAGS)
                ->count();
            $this->assertEquals(0, $whereTagsNull,
                "Should have been updated by analyzeGloballyIfNecessary");
        }
        $this->assertTrue(true);
    }
}
