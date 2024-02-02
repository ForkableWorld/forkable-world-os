<?php
namespace Storage;
use App\Logging\QMIgnition;
use App\Models\User;
use App\Slim\Model\User\QMUser;
use App\Storage\DB\QMQB;
use App\Utils\Env;
use Tests\QMDebugBar;
use Tests\UnitTestCase;

class QueryLoggerTest extends UnitTestCase
{
	/**
	 * @return void
	 * @covers \App\Providers\DBQueryLogServiceProvider
	 */
	public function testQueryLoggers(){
        Env::set(Env::DEBUGBAR_WHILE_TESTING, true);
        QMDebugBar::enable();
        $this->assertQueryCount(0);
        User::all();
        $this->assertQueryCount(1);
        QMUser::get();
        $this->assertQueryCount(2);
        QMQB::flushQueryLog();
        $this->assertQueryCount(0);
        Env::set(Env::DEBUGBAR_WHILE_TESTING, false);
        QMDebugBar::disable();
    }
    private function assertQueryCount(int $number){
        $debugBar = QMQB::getQueriesWithBackTraces();
        $this->assertCount($number, $debugBar);
        $ignition = QMIgnition::queryRecorder()->getQueries();
        $this->assertCount($number, $ignition);
        //TODO: Switch to $debugBar count and uncomment this $this->assertEquals($number, $qm);
    }
}
