<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Files;
use App\Folders\DynamicFolder;
use Noodlehaus\Config;
class XmlFile extends TypedProjectFile {
	private $config;
	/**
	 * @param string $file
	 */
	public function __construct($file = null){
		parent::__construct($file);
		$this->config = Config::load($file);
	}
	public static function getDefaultFolderRelative(): string{
		return DynamicFolder::STORAGE . "/" . static::getDefaultExtension();
	}
	public static function getDefaultExtension(): string{
		return "xml";
	}
	/**
	 * @return Config
	 */
	public function getConfig(): Config{
		return $this->config;
	}
	public function toArray(): array{
		return $this->getConfig()->all();
	}
	/**
	 * @param string $path
	 * @return array
	 * @noinspection PhpUnused
	 */
	public static function xmlFileToArray(string $path): array{
		$config = new static($path);
		return $config->toArray();
	}
}
