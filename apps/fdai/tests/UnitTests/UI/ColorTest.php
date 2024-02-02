<?php
namespace Tests\UnitTests\UI;
use App\UI\QMColor;
use Tests\UnitTestCase;

class ColorTest extends UnitTestCase
{
    public function testToColorString(){
        $hex = QMColor::getHexColors();
        foreach($hex as $name => $value){\App\Logging\ConsoleLog::info("self::$name => self::BOOTSTRAP_RED,");}
        foreach($hex as $name => $value){
            $str = QMColor::toString($value);
            $this->assertNotContains('hex_', $str);
        }
        foreach($hex as $name => $value){
            $str = QMColor::toBootstrap($value);
            $this->assertNotContains('hex_', $str);
        }
    }
}
