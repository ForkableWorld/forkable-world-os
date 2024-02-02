<?php
namespace Tests\UnitTests\CodeGenerators;
use App\CodeGenerators\Generators\ModelGenerator;
use App\Files\FileHelper;
use App\Types\QMStr;
use Tests\UnitTestCase;
/**
 * @package Tests\UnitTests\Files
 * @coversDefaultClass \App\CodeGenerators\Generators\ModelGenerator;
 */
class ModelGeneratorTest extends UnitTestCase {
	public const DISABLED_UNTIL = "2023-04-01";
	/**
	 * @covers ModelGenerator::fromData
	 * @noinspection PhpUnitMissingTargetForTestInspection (from a trait)
	 */
	public function testFromJSONModelGenerator(){
		$this->skipTest("Only generate files locally");
		$data = [
			'login' => 'mikepsinn',
			'id' => 2808553,
			'plan' =>
				[
					'name' => 'pro',
				],
		];
		$files = ModelGenerator::fromData(QMStr::snakize($this->getName()), $data);
		$table = QMStr::classToTableName(static::class);
		$this->assertArrayEquals([], $files);
		$this->assertTableExists($table);
		$this->assertFilesExist($files);
		FileHelper::deleteFiles($files);
	}
}
