<?php
namespace Tests\StagingUnitTests\D\Variables\UserVariables;
use App\Models\UserVariable;
use App\Models\Variable;
use App\Properties\Base\BaseValenceProperty;
use App\Storage\Memory;
use App\Variables\CommonVariables\SymptomsCommonVariables\VividDreamsCommonVariable;
use App\Variables\QMUserVariable;
use Tests\SlimStagingTestCase;
class NumericRatingVividDreamsTest extends SlimStagingTestCase
{
    public function testNumericRatingVividDreams(){
        //UserVariable::whereVariableId(VividDreamsCommonVariable::ID)->update([UserVariable::FIELD_VALENCE => null]);
        $v = Variable::findByName(VividDreamsCommonVariable::NAME);
        $qmV = $v->getDBModel();
        $this->assertEquals(BaseValenceProperty::VALENCE_NEUTRAL, $v->valence);
        $this->assertEquals(BaseValenceProperty::VALENCE_NEUTRAL, $qmV->valence);
        $userVariables = UserVariable::whereVariableId($v->id)->get();
        $this->assertGreaterThan(0, $userVariables->count());
        foreach($userVariables as $row){
            //if($row->user_id !== 1){continue;}
            $this->assertEquals(BaseValenceProperty::VALENCE_NEUTRAL, $row->valence,
                "User variable row valence is $row->valence for user $row->user_id ".
                print_r($row->toArray(), true));
            /** @var QMUserVariable $qmUV */
            $qmUV = $row->getDBModel();
            $this->assertEquals(BaseValenceProperty::VALENCE_NEUTRAL, $qmUV->valence);
            $notifications = $qmUV->getNotifications();
            foreach($notifications as $n){
                if($n->valence !== BaseValenceProperty::VALENCE_NEUTRAL){
                    Memory::flush();
                    $v = QMUserVariable::find($n->getUserVariableId());
                    $this->assertEquals(BaseValenceProperty::VALENCE_NEUTRAL, $v->valence);
                    Memory::flush();
                    $v = QMUserVariable::getByNameOrId($n->getUserId(), $n->getVariableIdAttribute());
                    $this->assertEquals(BaseValenceProperty::VALENCE_NEUTRAL, $v->valence);
                }
                $this->assertEquals(BaseValenceProperty::VALENCE_NEUTRAL, $n->valence);
                $buttons = $n->getNotificationActionButtons();
                foreach($buttons as $b){
                    if(stripos($b->image, "numeric") === false){
                        le("VividDreams buttons should contain numeric but is ".
                            print_r($b, true));
                    }
                    $this->assertContains("numeric", $b->image);
                }
            }
        }
    }
}
