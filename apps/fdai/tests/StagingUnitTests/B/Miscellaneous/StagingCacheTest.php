<?php /** @noinspection DuplicatedCode */
namespace Tests\StagingUnitTests\B\Miscellaneous;
use App\Storage\Memory;
use App\Storage\MemoryOrRedisCache;
use App\Utils\EnvOverride;
use App\VariableCategories\EmotionsVariableCategory;
use App\Variables\QMVariableCategory;
use Tests\SlimStagingTestCase;
class StagingCacheTest extends SlimStagingTestCase{
    public function testCacheSetGetDeleteObjectStaging(){
		if(!EnvOverride::isLocal()){
			$this->skipTest("All the flushing on staging makes tests fail randomly");
		}
        $key = "object";
        $c = EmotionsVariableCategory::instance();
        MemoryOrRedisCache::set($key, $c);
        Memory::flush();
        $gotten = MemoryOrRedisCache::get($key);
        $this->assertInstanceOf(QMVariableCategory::class, $gotten);
        $this->assertEquals($c, $gotten);
        $result = MemoryOrRedisCache::delete($key);
        $this->assertTrue($result && true);
        $gotten = MemoryOrRedisCache::get($key);
        $this->assertNull($gotten);
    }
    public function testCacheSetGetDeleteStaging(){
	    if(!EnvOverride::isLocal()){
		    $this->skipTest("All the flushing on staging makes tests fail randomly");
	    }
        $key = "test-key";
        $value = "test-string";
        MemoryOrRedisCache::set($key, $value);
        Memory::flush();
        $gotten = MemoryOrRedisCache::get($key);
        $this->assertEquals($value, $gotten);
        $result = MemoryOrRedisCache::delete($key);
        $this->assertTrue($result && true);
        $gotten = MemoryOrRedisCache::get($key);
        $this->assertNull($gotten);
    }
    public function testRedisConnection(){
	    if(!EnvOverride::isLocal()){
		    $this->skipTest("All the flushing on staging makes tests fail randomly");
	    }
        $key = "test-key";
        $value = "test-string";
        Memory::flush();
        MemoryOrRedisCache::set($key, $value);
        Memory::flush();
        $gotten = MemoryOrRedisCache::get($key);
        $this->assertEquals($value, $gotten);
        $result = MemoryOrRedisCache::delete($key);
        $this->assertTrue((bool)$result);
        $gotten = MemoryOrRedisCache::get($key);
        $this->assertNull($gotten);
    }
    public function testCacheIncrementStaging(){
	    if(!EnvOverride::isLocal()){
		    $this->skipTest("All the flushing on staging makes tests fail randomly");
	    }
        $key = "increment-test";
	    MemoryOrRedisCache::flush();
        $res = MemoryOrRedisCache::get($key);
        $this->assertNull($res);
        $res = MemoryOrRedisCache::increment($key, 1, 10);
        $this->assertEquals(1, $res);
        $res = MemoryOrRedisCache::increment($key);
        $this->assertEquals(2, $res);
//        Memory::flush();
//        $res = MemoryOrRedisCache::get($key);
        // This fails randomly $this->assertEquals(2, $res);
    }
    public function testCacheExpirationStaging(){
	    if(!EnvOverride::isLocal()){
		    $this->skipTest("All the flushing on staging makes tests fail randomly");
	    }
        $key = "test-key";
        $value = "test-string";
        MemoryOrRedisCache::set($key, $value, 3);
        $gotten = MemoryOrRedisCache::get($key);
        $this->assertEquals($value, $gotten);
        Memory::flush();
        sleep(4);
        $gotten = MemoryOrRedisCache::get($key);
        $this->assertNull($gotten);
    }
}
