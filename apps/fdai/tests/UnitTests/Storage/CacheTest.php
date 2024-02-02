<?php /** @noinspection DuplicatedCode */
/** @noinspection PhpDocMissingThrowsInspection */
/** @noinspection PhpUnhandledExceptionInspection */
namespace Storage;
use App\Storage\Memory;
use App\Storage\MemoryOrRedisCache;
use App\Storage\RedisCache;
use App\VariableCategories\EmotionsVariableCategory;
use App\Variables\QMVariableCategory;
use Cache;
use Tests\UnitTestCase;
/**
 * Class StringHelperTest
 * @package Tests\Api\Model
 * @coversDefaultClass \App\Storage\MemoryOrRedisCache
 */
class CacheTest extends UnitTestCase {
	/**
	 * @param string $key
	 * @return \Illuminate\Testing\TestResponse|void
	 */
	private function deleteKey(string $key): void{
		$result = MemoryOrRedisCache::delete($key);
		//$this->assertTrue((bool)$result);
		$gotten = MemoryOrRedisCache::get($key);
		$this->assertNull($gotten);
		$gotten = RedisCache::get($key);
		$this->assertNull($gotten);
	}
	public function testMemcache(){
        $memcachedExpirationSeconds = 3600;
        $memcacheKey = 'latestSourceTime'.':'. 1 .':'. 1;
        $latestSourceTime = time();
        MemoryOrRedisCache::set($memcacheKey, $latestSourceTime, time() + $memcachedExpirationSeconds);
        $fromMemcache = MemoryOrRedisCache::get($memcacheKey);
        $this->assertEquals($latestSourceTime, $fromMemcache);
    }
    public function testCacheSetGetDeleteObject(){
        $key = "object";
		MemoryOrRedisCache::flush();
        $c = EmotionsVariableCategory::instance();
        MemoryOrRedisCache::set($key, $c);
//		$keys = MemoryOrRedisCache::keys();
//	    $this->assertArrayEquals([
//		    0 => 'quantimodo_test_cache:object',], $keys,
//		    "We just stored");
        $gotten = RedisCache::get($key);
        $this->assertInstanceOf(QMVariableCategory::class, $gotten);
        $this->assertEquals($c, $gotten);
	    $this->deleteKey($key);
    }
    public function testCacheSetGetDelete(){
        $key = "test-key";
        $value = "test-string";
        MemoryOrRedisCache::set($key, $value);
        Memory::flush();
        $gotten = MemoryOrRedisCache::get($key);
        $this->assertEquals($value, $gotten);
        $this->deleteKey($key);
    }
    public function testRedisConnection(){
	    $this->skipTest("Don't use redis in tests");
        $this->flush();
        Cache::set("hi", "hi");
        $this->assertEquals('hi', Cache::get("hi"));
        //$all = MemoryOrRedisCache::keys("*");
        //$this->assertEquals('quantimodo_test_cache::hi', $all[0]);
    }
    public function testCacheFlush(){
		$this->skipTest("Don't use redis in tests");
	    $this->flush();
        MemoryOrRedisCache::set("hi", "hi");
        //$keys = MemoryOrRedisCache::keys();
        //$this->assertCount(1, $keys);
        Memory::flush();
        $gotten = MemoryOrRedisCache::get("hi");
        $this->assertEquals("hi", $gotten);
	    $this->flush();
    }
    public function testCacheIncrement(){
        $key = "increment-test";
        Memory::flush();
        $res = MemoryOrRedisCache::increment($key, 1, 10);
        $this->assertEquals(1, $res);
        $res = MemoryOrRedisCache::increment($key);
        $this->assertEquals(2, $res);
        Memory::flush();
        $res = MemoryOrRedisCache::get($key);
        $this->assertEquals(2, $res);
    }
	private function flush(): void{
		MemoryOrRedisCache::flush();
		sleep(1);
		$keys = MemoryOrRedisCache::keys();
		$this->assertArrayEquals([], $keys, "We just flushed so it should be empty");
		$gotten = MemoryOrRedisCache::get("hi");
		$this->assertNull($gotten);
	}
}
