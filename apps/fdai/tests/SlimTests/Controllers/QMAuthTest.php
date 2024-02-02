<?php
namespace Tests\SlimTests\Controllers;
class QMAuthTest extends \Tests\SlimTests\SlimTestCase
{
    public function testGetQMUser(){
		$this->setAuthenticatedUser(2);
		$this->slimGetUser(2);
    }
}
