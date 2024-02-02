<?php
namespace Tests\UnitTests\UI;
use App\Models\Base\BaseConnection;
use App\Models\Base\BaseWpUser;
use App\UI\ImageUrls;
use Tests\UnitTestCase;

class ImageHelperTest extends UnitTestCase
{
    public function testImageSearch(){
        if(false){ImageUrls::outputForBaseModels();}
        if($table = false){ImageUrls::outputForTable($table);}

        $res = ImageUrls::findConstantNameLike(BaseConnection::FIELD_UPDATE_STATUS);
        $this->assertEquals("UPDATE", $res);

        $res = ImageUrls::findConstantNameLike("CARD");
        $this->assertEquals("CARD", $res);
        $res = ImageUrls::findConstantNameLike(BaseWpUser::FIELD_USER_LOGIN);
        $this->assertEquals("LOGIN", $res);
    }
}
