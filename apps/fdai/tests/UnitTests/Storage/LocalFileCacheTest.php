<?php
namespace Tests\UnitTests\Storage;
use App\Storage\LocalFileCache;
use Tests\UnitTestCase;
/**
 * @package Tests\UnitTests\Files
 * @coversDefaultClass \App\Storage\LocalFileCache
 */
class LocalFileCacheTest extends UnitTestCase {
	protected function setUp(): void{
		$this->skipIfNotLocal();
		parent::setUp();
	}
	/**
	 * @covers \App\Storage\LocalFileCache::set
	 */
	public function testLocalFileCacheSet(){
		$t = time();
		LocalFileCache::set(__FUNCTION__, $t);
		$gotten = LocalFileCache::get(__FUNCTION__);
		$this->assertEquals($t, $gotten);
	}
}
