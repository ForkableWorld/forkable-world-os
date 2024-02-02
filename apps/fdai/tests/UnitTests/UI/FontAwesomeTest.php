<?php
namespace Tests\UnitTests\UI;
use App\Models\User;
use App\UI\FontAwesome;
use Tests\UnitTestCase;

class FontAwesomeTest extends UnitTestCase
{
    public function testFontAwesomeSearch(){
        if(false){FontAwesome::outputForBaseModels();}
        if($table = false){FontAwesome::outputForTable($table);}
        $res = FontAwesome::findConstantNameLike(User::FIELD_USER_LOGIN);
        $this->assertEquals('LOGIN', $res);
    }
}
