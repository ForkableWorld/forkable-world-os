<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\Model\Measurement;
use App\Buttons\QMButton;
use App\Traits\CompressibleTrait;
use App\Types\ObjectHelper;
use App\Types\QMStr;
/** Class AdditionalMetaData
 * @package App\Slim\Model
 */
class AdditionalMetaData {
	use CompressibleTrait;
	public $message;
	public $commenter;
	public $url;
	public $image;
	public $icon;
	public $description;
	public $name;
	public $accountName;
	public $originalDescription;
	public $measurementNoteMetaData;  // Can't just use metaData because Mongo won't support it
	/**
	 * MeasurementNote constructor.
	 * @param null $arrayOrObject
	 * @param string $message
	 * @param string $url
	 */
	public function __construct($arrayOrObject = null, string $message = null, string $url = null){
		if($arrayOrObject === "{}"){
			return;
		}
		if(is_string($arrayOrObject)){
			if(strpos($arrayOrObject, '{') === false){
				$message = $arrayOrObject;
				$arrayOrObject = null;
			} else{
				$arrayOrObject = json_decode($arrayOrObject);
			}
		}
		if($arrayOrObject){
			$this->populateFieldsByArrayOrObject($arrayOrObject);
		}
		if($url){
			$this->setUrl($url);
		}
		if($message){
			$this->setMessage($message);
		}
		$this->unsetNullPropertiesToSaveMemory();
	}
	/**
	 * @param $obj
	 */
	public function populateFieldsByArrayOrObject($obj){
		if(is_string($obj)){
			$obj = $this->decodeAndCamelize($obj);
		}
		foreach($obj as $key => $value){
			$camel = QMStr::camelize($key);
			$this->$camel = $value;
		}
		if(isset($obj->text)){
			$this->message = $obj->text;
		}
		if(isset($obj->picture)){
			$this->image = $obj->picture;
		}
		if(isset($obj->venue->categories[0]->icon->prefix)){
			$this->image = $obj->venue->categories[0]->icon->prefix . '88' . $obj->venue->categories[0]->icon->suffix;
		}
		if(isset($obj->from)){
			$this->commenter = $obj->from;
			$this->message = $obj->from->name . ": " . $this->message;
		}
		$this->createGithubNote($obj);
	}
	/**
	 * @param $arrayOrObject
	 */
	private function createGithubNote($arrayOrObject){
		if(isset($arrayOrObject->commit)){
			$this->message = $arrayOrObject->commit->message;
		}
		if(isset($arrayOrObject->html_url)){
			$this->url = $arrayOrObject->html_url;
		}
	}
	/**
	 * @param string $url
	 */
	public function setUrl(string $url){
		if(!$url){
			return;
		}
		$url = QMStr::validateUrlAndAddHttpsIfNecessary($url, true);
		$this->url = $url;
	}
	/**
	 * @return string|null
	 */
	public function getMessage(): ?string{
		if(!isset($this->message) || empty($this->message)){
			return null;
		}
		if(!is_string($this->message)){
			throw new \LogicException("Not a string " . \App\Logging\QMLog::print_r($this->message, true));
		}
		return $this->message;
	}
	/**
	 * @param string $message
	 */
	public function setMessage(string $message){
		$this->message = $message;
	}
	/**
	 * @param string $key
	 * @param mixed $metaData
	 */
	public function addMetaData(string $key, $metaData){
		$metaData = ObjectHelper::unsetNullAndEmptyStringFields($metaData);
		$this->$key = $metaData;
	}
	/**
	 * @param array $measurements
	 * @param QMMeasurement|\App\Slim\Model\Measurement\AnonymousMeasurement $parent
	 */
	public function addMergedMeasurements(array $measurements, QMMeasurement $parent){
		$clones = [];
		$deprecatedProperties = QMMeasurement::getDeprecatedProperties();
		foreach($measurements as $m){
			$clone = clone $m;
			foreach($clone as $key => $value){
				if(property_exists($parent, $key) && $parent->$key === $value){
					unset($clone->$key);
				} elseif(in_array($key, $deprecatedProperties)){
					unset($clone->$key);
				}
			}
			$clones[] = $clone;
		}
		$this->addMetaData('mergedMeasurements', $clones);
	}
	public function compress(): string{
		if(isset($this->mergedMeasurements)){
			$metaData = ObjectHelper::unsetNullAndEmptyStringFields($this->mergedMeasurements);
			foreach($metaData as $m){
				unset($m->submittedUnit);
			}
			$this->mergedMeasurements = $metaData;
		}
		$this->unsetNullAndEmptyStringFields();
		return json_encode($this);
	}
	/**
	 * @param string $image
	 */
	public function setImage(string $image){
		$image = QMStr::validateUrlAndAddHttpsIfNecessary($image, true);
		$this->image = $image;
	}
	/**
	 * @return mixed
	 */
	public function getIcon(): ?string{
		return $this->icon;
	}
	/**
	 * @return string
	 */
	public function __toString(){
		return json_encode($this);
	}
	private function unsetNullPropertiesToSaveMemory(): void{
		foreach($this as $key => $value){
			if($value === null){
				unset($this->$key);
			}
		}
	}
	/**
	 * @param $arrayOrObject
	 * @return object
	 */
	private function decodeAndCamelize(string $arrayOrObject){
		$obj = json_decode($arrayOrObject, false);
		if($obj){  // I guess too long for proper json_encode sometimes?
			foreach($obj as $key => $value){
				$key = QMStr::toCamelCaseIfSnakeCaseOrSpaces($key);
				$this->$key = $value;
			}
		}
		return $obj;
	}
	/**
	 * @return string
	 */
	public function toHumanString(): ?string{
		$str = '';
		if(isset($this->message)){
			if($this->message === "{}"){
				$this->message = null;
			} else{
				$decoded = QMStr::decodeIfJson($this->message);
				if(is_object($decoded)){
					$this->populateFieldsByArrayOrObject($decoded);
				}
			}
		}
		if(isset($this->name)){
			$str .= $this->name . ' ';
		}
		if($m = $this->getMessage()){
			$str .= $m . ' ';
		}
		if(isset($this->description)){
			$str .= $this->description . ' ';
		}
		if(isset($this->accountName)){
			$str .= $this->accountName . ' ';
		}
		if(isset($this->originalDescription)){
			$str .= $this->originalDescription . ' ';
		}
		$str = str_replace("{", "", $str);
		$str = str_replace("}", "", $str);
		$str = trim($str);
		if(isset($this->productUrl)){
			$this->setUrl($this->productUrl);
		}
		if($str === "{}" || empty($str)){
			return null;
		}
		return $str;
	}
	/**
	 * @return QMMeasurement[]
	 */
	public function getMergedMeasurements(): array{
		$arr = $this->getByKey('mergedMeasurements');
		if(!$arr){
			return [];
		}
		return $arr;
	}
	/**
	 * @param string $key
	 * @return mixed
	 */
	public function getByKey(string $key){
		if(isset($this->$key)){
			return $this->$key;
		}
		return null;
	}
	/**
	 * @param array $params
	 * @return QMButton
	 */
	public function getButton(array $params = []): QMButton{
		$b = new QMButton();
		$str = $this->toHumanString();
		if($str){
			$truncated = QMStr::truncate($str, 10);
			$b->setTooltip($str);
			$b->setTextAndTitle($truncated);
		}
		$url = $this->url ?? null;
		if($url){
			$b->setUrl($url);
		}
		$image = $this->image ?? null;
		if($image){
			$b->setImage($image);
		}
		return $b;
	}
	public function getUrl(array $params = []): string{
		return $this->url;
	}
	/**
	 * @return string
	 */
	public function getImage(): ?string{
		return $this->image;
	}
}
