<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnhandledExceptionInspection */
namespace App\PhpUnitJobs\Studies;
use App\Charts\CorrelationCharts\CorrelationChartGroup;
use App\Models\User;
use App\Properties\Study\StudyTypeProperty;
use App\Studies\QMUserStudy;
use App\PhpUnitJobs\JobTestCase;
/** @package App\PhpUnitJobs\Studies
 */
class RepublishStudiesJobTest extends JobTestCase {
    public function testRepublishStudies(): void{
        $debug = false;
        if($debug){
            $pairs = [
                //['c' => "Cardio Heart Rate Zone Minutes", 'e' => "Overall Mood"],
                //['c' => "Cardio Heart Rate Zone Minutes", 'e' => "Psoriasis Severity"],
                ['c' => "Calories Burned", 'e' => "Psoriasis Severity"],
                ['c' => "Psoriasis Severity", 'e' => "Overall Mood"],
                ['c' => "Acne Severity", 'e' => "Energy"],
                ['c' => "5 HTP", 'e' => "Overall Mood"],
                ['c' => "Gluten Free Pasta With Olive Oil", 'e' => "Overall Mood"],
                ['c' => "Sleep Duration", 'e' => "Overall Mood"],
            ];
            CorrelationChartGroup::$REGENERATE_DELAY_DURATION_CHARTS = true;
            foreach($pairs as $pair){
                $userStudy = QMUserStudy::findOrCreateQMStudy($pair['c'], $pair['e'], 230, StudyTypeProperty::TYPE_INDIVIDUAL);
                //$userStudy->sendStudyReportEmail();
                $userStudy->publishToJekyll();
            }
        }
        $mike = User::mike();
        $mike->republishUpVotedStudies();
    }
}
