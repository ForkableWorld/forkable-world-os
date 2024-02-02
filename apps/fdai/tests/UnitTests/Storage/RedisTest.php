<?php
namespace Tests\UnitTests\Storage;
use App\Utils\Env;
use Tests\UnitTestCase;
/**
 * @coversDefaultClass \App\Storage\MemoryOrRedisCache
 */
class RedisTest extends UnitTestCase
{
	public const DISABLED_UNTIL = "2030-01-01";
	public const REASON_FOR_SKIPPING = "This messes up the following tests because it never gets changed back for some reason";
	/**
	 * @covers \App\Storage\MemoryOrRedisCache
	 */
	public function testInvalidRedisCredentials(){
        $prev = Env::get(Env::REDIS_PORT);
		/** @noinspection SpellCheckingInspection */
		//Env::set(Env::REDIS_PORT, "asdf");
        $res = $this->getResponseContains('privacy', "Privacy Policy");
        //Env::set(Env::REDIS_PORT, $prev);
    }
}
