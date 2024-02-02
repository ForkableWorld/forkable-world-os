<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Files\Json;
use App\Files\FileHelper;
use App\Folders\DynamicFolder;
use App\Types\QMStr;
use JSONSchemaGenerator\Generator;
use JSONSchemaGenerator\Parsers\Exceptions\UnmappableException;
use PHPModelGenerator\Exception\FileSystemException;
use PHPModelGenerator\Exception\RenderException;
use PHPModelGenerator\Exception\SchemaException;
use PHPModelGenerator\Model\GeneratorConfiguration;
use PHPModelGenerator\ModelGenerator;
use PHPModelGenerator\SchemaProvider\RecursiveDirectoryProvider;
class JsonSchemaToPhpFile extends JsonFile {
	public const RESOURCES_JSON_SCHEMA = "resources/json-schema";
	private $rawInput;
	/**
	 * JsonSchemaFile constructor.
	 * @param string $schemaPath
	 */
	public function __construct(string $schemaPath){
		parent::__construct($schemaPath);
	}
	public static function getDefaultFolderRelative(): string{
		return DynamicFolder::RESOURCES_JSON_SCHEMA;
	}
	/**
	 * @param $data
	 * @throws UnmappableException
	 */
	public function dataToSchema($data): void{
		$this->rawInput = $data;
		$json = !is_string($data) ? json_encode($data) : $data;
		$schema = Generator::fromJson($json, [
			'schema_id' => null,
			'properties_required_by_default' => true,
			'schema_uri' => 'http://json-schema.org/draft-04/schema',
			'schema_title' => $this->getTitleCasedFileName(),
			'schema_description' => $this->getTitleCasedPath(),
			'schema_type' => null,
			"items_schema_collect_mode" => 0,
			'schema_required_field_names' => [],
		]);
		$schema = $this->stripDisallowedFormatForPHPGenerator($schema);
		$schema = json_decode($schema);
		FileHelper::writeJsonFile($this->getPath(), QMStr::prettyJsonEncode($schema, null, false));
	}
	/**
	 * @param string $schema
	 * @return array|string|string[]
	 * TODO: Fix PHPModelGenerator\Exception\SchemaException: Unsupported format hostname for property _class
	 */
	private function stripDisallowedFormatForPHPGenerator(string $schema){
		$schema = str_replace([',"format":"uri"', ',"format":"hostname"'], '', $schema);
		return $schema;
	}
	public function deletePhpOutputFolder(): void{
		FileHelper::deleteDir($this->getPhpOutputFolder(), __METHOD__);
	}
	/**
	 * @return string
	 */
	public function getPhpOutputFolder(): string{
		$outputFolder = str_replace([self::RESOURCES_JSON_SCHEMA . "/", ".json"], "", $this->absPath);
		return $outputFolder;
	}
	/**
	 * @throws FileSystemException
	 * @throws RenderException
	 * @throws SchemaException
	 */
	public function generatePHP(): void{
		$generator = new ModelGenerator((new GeneratorConfiguration())->setNamespacePrefix($this->getNamespace())
			->setImmutable(false));
		$source = $this->getFolderPath();
		$destination = $this->getPhpOutputFolder();
		FileHelper::mkDir($destination);
		$this->assertExists();
		$generator->generateModelDirectory($destination)
			->generateModels(new RecursiveDirectoryProvider($source), $destination);
	}
	/**
	 * @return string
	 */
	private function getNamespace(): string{
		return FileHelper::classToNamespace($this->getPhpOutputFolder());
	}
}
