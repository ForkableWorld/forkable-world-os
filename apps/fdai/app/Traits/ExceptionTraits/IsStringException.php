<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\ExceptionTraits;
use App\Buttons\Admin\PHPStormExceptionButton;
use App\Storage\S3\S3Private;
use App\Traits\HasClassName;
use App\Types\QMStr;
use Facade\IgnitionContracts\BaseSolution;
use Facade\IgnitionContracts\Solution;
use Tests\QMBaseTestCase;
trait IsStringException {
	use HasClassName;
	public $attributeName;
	public $maxLength = 280;
	/**
	 * @var string
	 */
	protected $fullString;
	/**
	 * @var string
	 */
	public $urlToFullString;
	/**
	 * @return string
	 */
	public static $uploadedStrings = [];
	public function getUrlToFullString(): string{
		if($this->urlToFullString){
			return $this->urlToFullString;
		}
		$fullString = $this->fullString;
		foreach(static::$uploadedStrings as $url => $str){
			if($str === $fullString){
				return $this->urlToFullString = $url;
			}
		}
		$folderPath = QMStr::slugify($this->attributeName);
		$s3Path = (new \ReflectionClass(static::class))->getShortName() . '/' . $folderPath;
		if($test = \App\Utils\AppMode::getCurrentTestName()){
			$s3Path = $test . "/$folderPath";
		}
		$url = S3Private::uploadHTML($s3Path, $fullString);
		static::$uploadedStrings[$url] = $fullString;
		return $this->urlToFullString = $url;
	}
	public function getSolution(): Solution{
		$links = [
			"Jump to String Exception" => PHPStormExceptionButton::urlForException($this),
		];
		if($this->fullStringTooLong()){
			$links["See Full String"] = $this->getUrlToFullString();
		}
		return BaseSolution::create("Examine String")->setSolutionDescription("Prevent this string from being created")
			->setDocumentationLinks($links);
	}
	public function fullStringTooLong(): bool{
		return strlen($this->fullString) > $this->maxLength;
	}
	public function getInvalidStringSegment(): string{
		if($this->fullStringTooLong()){
			$url = $this->getUrlToFullString();
			return QMStr::truncate($this->fullString, 500, "[TRUNCATED. See full string at $url]");
		}
		return $this->fullString;
	}
}
