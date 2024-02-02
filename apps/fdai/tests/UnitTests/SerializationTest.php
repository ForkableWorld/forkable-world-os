<?php
namespace Tests\UnitTests;
use App\Slim\Model\DBModel;
use App\Slim\Model\User\QMUser;
use Tests\UnitTestCase;
class SerializationTest extends UnitTestCase
{
    public function testSerializeConnectors(){
        $u = QMUser::find(1);
        $connectors = $u->getQMConnectors();
        foreach($connectors as $connector){
            $this->assertSerializable($connector);
        }
    }
    /**
     * @param DBModel $obj
     */
    public function assertSerializable($obj): void{
        \App\Logging\ConsoleLog::info($obj);
        $serialized = serialize($obj);
        $this->assertNotNull($serialized);
        $unserialized = unserialize($serialized);
        $obj->makeSerializable();
        $this->assertEquals($obj, $unserialized);
    }
}
