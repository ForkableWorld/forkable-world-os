<?php
namespace Tests\UnitTests;
use App\CodeGenerators\TVarDumper;
use Tests\UnitTestCase;
class VarDumperTest extends UnitTestCase
{
    public function testDumpStringWithApostrophe(){
        $actual = TVarDumper::dump("I've an apostrophe");
        $this->assertEquals("'I\'ve an apostrophe'", $actual);
    }
}