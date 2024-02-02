<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits;
use App\Exceptions\QMFileNotFoundException;
use App\Files\FileHelper;
use App\Files\XmlFile;
use App\Types\QMStr;
trait HasXmlConfig {
	protected $xmlFile;
	abstract protected function getXmlConfigPath(): string;
	abstract protected function getXmlTemplatePath(): string;
	public function generateXmlConfig(): string{
		$file = $this->getXmlFile();
		$updatedStr = $file->getContents();
		foreach($this->getReplacements() as $key => $value){
			if($value !== null){
				$updatedStr = QMStr::replace_between($updatedStr, "<$key>", "</$key>", $value);
			}
		}
		return $updatedStr;
	}
	protected function loadXmlConfig(){
		$file = $this->getXmlFile();
		$arr = $file->toArray();
		foreach($arr as $key => $value){
			if($value !== null){
				if(!property_exists($this, $key)){
					// TODO $this->addProperty($key, ClassType::VISIBILITY_PUBLIC, null);
				}
				$this->$key = $value;
			}
		}
	}
	/**
	 * @return string
	 */
	public function getXmlConfigContents(): string{
		return $this->getXmlFile()->getContents();
	}
	private function getXmlConfigStubPath(): string{
		return FileHelper::getFilePathToClass(static::class) . ".xml.stub";
	}
	private function getXmlConfigStub(): string{
		try {
			return FileHelper::getContents($this->getXmlConfigStubPath());
		} catch (QMFileNotFoundException $e) {
			le($e);
		}
	}
	/**
	 * @return XmlFile
	 */
	public function getXmlFile(): XmlFile{
		return XmlFile::find($this->getXmlConfigPath());
	}
	abstract public function getReplacements();
}
