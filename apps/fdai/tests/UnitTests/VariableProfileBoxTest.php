<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\UnitTests;
use App\Models\UserVariable;
use App\Models\Variable;
use App\Storage\DB\TestDB;
use App\Storage\DB\Writable;
use App\Units\OneToFiveRatingUnit;
use App\Variables\CommonVariables\EmotionsCommonVariables\OverallMoodCommonVariable;
use Tests\UnitTestCase;
class VariableProfileBoxTest extends UnitTestCase
{
    public function testVariableProfileBox(){
        TestDB::resetTestDB();
		$this->assertEquals("storage/qm_test.sqlite", Writable::getDbName());
	    $this->assertEquals("sqlite", Writable::getConnectionName());
		$this->assertEquals(9, UserVariable::count(), "We should have 7 user variables in test DB");
	    $this->assertEquals(7, UserVariable::whereVariableId(1398)->count(),
	                        "We should have 7 mood user variables in test DB");
        $v = Variable::find(OverallMoodCommonVariable::ID);
		$this->assertEquals(7, $v->number_of_user_variables, 
		                    "We should have 7 number_of_user_variables for mood in test DB");
        $html = $v->getAvatarBadgesRelationshipListBoxHtml();
        $this->compareHtmlFragment('AvatarBadgesRelationshipListBox', $html);
        $this->assertContains(OneToFiveRatingUnit::NAME, $html);
    }
}
