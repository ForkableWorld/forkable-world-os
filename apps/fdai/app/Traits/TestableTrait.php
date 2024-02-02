<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits;
use App\Buttons\Admin\PHPUnitButton;
use App\Buttons\QMButton;
use App\Logging\QMLog;
use App\Types\QMStr;
use Tests\TestGenerators\StagingJobTestFile;
trait TestableTrait {
	public function getPhpUnitButton(): QMButton{
		$url = $this->getPHPUnitTestUrl();
		$id = $this->getId();
		$id = urlencode($id);
		if(stripos($url, (string)$id) === false){
			$url = $this->getPHPUnitTestUrl();
			$id = $this->getId();
			le("$url does not contain $id");
		}
		$b = new PHPUnitButton("PHPUnit Test", $url); // Keep title short for dropdown menus
		$b->setTooltip($this->getTitleAttribute() . " PHPUnit Test");
		return $b;
	}
	/**
	 * @return string
	 */
	public function getPHPUnitTestUrl(): string{
		$shortName = (new \ReflectionClass(static::class))->getShortName();
		$id = $this->getId();
		if(is_string($id)){
			$id = '"' . $id . '"';
		}
		$functions = "\$l = $shortName::find($id);" . PHP_EOL;
		$functions .= "\t\t\$l->test();";
		$testName = QMStr::toClassName($this->__toString()) . $shortName;
		return StagingJobTestFile::getUrl($testName, $functions, static::class);
	}
	abstract public function test(): void;
	public function logPHPUnitTest(){
		$url = $this->getPHPUnitTestUrl();
		QMLog::linkButton("PHPUnit test", $url);
	}
}
