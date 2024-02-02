<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\ConnectorTests\A;
use App\DataSources\Connectors\RescueTimeConnector;
use App\Logging\QMLog;
use App\Models\TrackingReminder;
use App\Models\UserVariable;
use App\Slim\View\Request\Variable\GetUserVariableRequest;
use App\Units\HoursUnit;
use App\Variables\CommonVariables\ActivitiesCommonVariables\TimeSpentOnBusinessActivitiesCommonVariable;
use App\Variables\CommonVariables\ActivitiesCommonVariables\TimeSpentOnConsumingNewsActivitiesCommonVariable;
use App\Variables\CommonVariables\ActivitiesCommonVariables\TimeSpentOnDesignAndCompositionActivitiesCommonVariable;
use App\Variables\CommonVariables\ActivitiesCommonVariables\TimeSpentOnEntertainmentActivitiesCommonVariable;
use App\Variables\CommonVariables\ActivitiesCommonVariables\TimeSpentOnReferenceAndLearningActivitiesCommonVariable;
use App\Variables\CommonVariables\ActivitiesCommonVariables\TimeSpentOnShoppingActivitiesCommonVariable;
use App\Variables\CommonVariables\ActivitiesCommonVariables\TimeSpentOnSocialNetworkingActivitiesCommonVariable;
use App\Variables\CommonVariables\ActivitiesCommonVariables\TimeSpentOnSoftwareDevelopmentActivitiesCommonVariable;
use App\Variables\CommonVariables\ActivitiesCommonVariables\TimeSpentOnSoftwareUtilitiesActivitiesCommonVariable;
use App\Variables\CommonVariables\GoalsCommonVariables\EfficiencyScoreFromRescuetimeCommonVariable;
use App\Variables\CommonVariables\GoalsCommonVariables\ProductivityPulseFromRescuetimeCommonVariable;
use App\Variables\QMCommonVariable;
use Guzzle\Http\Exception\ServerErrorResponseException;
use Tests\ConnectorTests\ConnectorTestCase;
/**
 * Class RescueTimeTest
 * @package Tests\Api\Connectors3
 */
class RescueTimeTest extends ConnectorTestCase{
    public const DISABLED_UNTIL = "2023-04-01"; // Getting 400  Bad Request
    public static $variables = [
        //'All Distracting Hours',
        //'All Productive Hours',
        //'google.com usage',
        //'Neutral Hours',
        //'Total RescueTime Hours',
        //'Uncategorized Hours from RescueTime',
        //ModeratelyProductiveScoreCommonVariable::NAME,
        //ModeratelyUnproductiveScoreCommonVariable::NAME,
        //VeryProductiveScoreCommonVariable::NAME,
        //VeryUnproductiveScoreCommonVariable::NAME,
        EfficiencyScoreFromRescuetimeCommonVariable::NAME,
        ProductivityPulseFromRescuetimeCommonVariable::NAME,
        TimeSpentOnBusinessActivitiesCommonVariable::NAME,
        TimeSpentOnConsumingNewsActivitiesCommonVariable::NAME,
        TimeSpentOnDesignAndCompositionActivitiesCommonVariable::NAME,
        TimeSpentOnEntertainmentActivitiesCommonVariable::NAME,
        TimeSpentOnReferenceAndLearningActivitiesCommonVariable::NAME,
        TimeSpentOnShoppingActivitiesCommonVariable::NAME,
        TimeSpentOnSocialNetworkingActivitiesCommonVariable::NAME,
        TimeSpentOnSoftwareDevelopmentActivitiesCommonVariable::NAME,
        TimeSpentOnSoftwareUtilitiesActivitiesCommonVariable::NAME,
    ];
    public function testRescueTime(){
        if($this->weShouldSkip()){return;}
        if(RescueTimeConnector::GET_APPS_AND_WEBSITES){
            self::$variables[] = RescueTimeConnector::TEST_WEBSITE. " usage";
        }
        RescueTimeConnector::checkEfficiencyScoreVariable();
        RescueTimeConnector::checkEditingIDEVariable();
        $this->truncateTrackingReminders();
        $this->connectorName = 'rescuetime';
        try {
            $this->connectImportCheckDisconnect([
                'source' => 34,
                'variables' =>  self::$variables,
                'fromTime' => time() - 86400 * 7
                //it takes too much time so we should get only last month's data
            ]);
        } catch (ServerErrorResponseException $e){
            $m = "Skipping test because ".$e->getMessage();
            QMLog::error($m);
            $this->skipTest($m);
            return;
        }
        RescueTimeConnector::checkEditingIDEVariable();
        $this->assertNumberOfTrackingRemindersEquals(0);
        $req = new GetUserVariableRequest(['searchPhrase' => 'github'], 1);
        $userVariables = $req->getVariables();
        $weekAgo = time() - 14 * 86400;
        foreach ($userVariables as $uv){
            $this->assertEquals(HoursUnit::NAME, $uv->getUserUnit()->name);
            if(stripos($uv->getVariableName(), "github") !== false){
                $latestTaggedMeasurementAt = $uv->getLatestTaggedMeasurementAt();
                $this->logInfo("Most recent ".$uv->getVariableName()." measurement ". $uv->timeSinceLatestTaggedMeasurementHumanString());
                $this->assertDateGreaterThan($weekAgo, $latestTaggedMeasurementAt,
                    'week ago', '$latestTaggedMeasurementTime');
            }
        }
        $reminders = TrackingReminder::query()->get();
        if($reminders->count()){
            $v = QMCommonVariable::findByNameOrId($reminders[0]->variable_id);
            $v->logError("Why do we have a reminder?");
        }
        $this->assertCount(0, $reminders, "We should not have any reminders!");
        RescueTimeConnector::checkEfficiencyScoreVariable();
        $all = UserVariable::all();
        /** @var UserVariable $uv */
        foreach($all as $uv){
            $v = $uv->getVariable();
            $this->assertFalse($v->manual_tracking, $v->name." variable should not be manual tracking");
        }
    }

}
