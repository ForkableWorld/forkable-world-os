<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpDocMissingThrowsInspection */
/** @noinspection PhpUnhandledExceptionInspection */
namespace App\PhpUnitJobs\Debug;
use App\Variables\QMUserVariable;
use App\PhpUnitJobs\JobTestCase;
class TagDebugTest extends JobTestCase {
    public function testUserVariableAnalysisDebug(){
        $main = QMUserVariable::getByNameOrId(230, 1261);
        $main->logInfo($main->numberOfMeasurements);
        $userTagged = $main->getUserTaggedVariables();
        $m = $c = [];
        $m[$main->getVariableIdAttribute()] = $main->getQMMeasurements();
        $c[$main->getVariableIdAttribute()] = $main->numberOfMeasurements;
        foreach ($userTagged as $v){
            $c[$v->getVariableIdAttribute()] = $v->numberOfMeasurements;
            $m[$v->getVariableIdAttribute()] = $v->getQMMeasurements();
        }
        $commonTagged = $main->getCommonTaggedVariables();
        foreach ($commonTagged as $v){
            $c[$v->getVariableIdAttribute()] = $v->numberOfMeasurements;
            $m[$v->getVariableIdAttribute()] = $v->getQMMeasurements();
        }
        $all = $main->getMeasurementsWithTags();
        $main->calculateNumberOfRawMeasurementsWithTagsFromNumberOfMeasurementsPropertyOnTaggedVariables();
        \App\Logging\QMLog::print_r($c);
    }
}
