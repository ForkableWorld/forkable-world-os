<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Exceptions\InvalidAttributeException;
use App\Exceptions\ModelValidationException;
use App\Fields\Select;
use App\Logging\QMLog;
use App\Models\Variable;
use App\Properties\BaseProperty;
use App\Properties\Variable\VariableNameProperty;
use App\Traits\PropertyTraits\IsArray;
use App\Types\PhpTypes;
use App\Types\QMStr;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use App\Utils\APIHelper;
use OpenApi\Generator;

class BaseSynonymsProperty extends BaseProperty {
	use IsArray;
	const MAX_LENGTH = 600;
	public $dbInput = 'string,' . self::MAX_LENGTH;
	public $dbType = PhpTypes::STRING;
	public $default = Generator::UNDEFINED;
	public $description = 'The primary name and any synonyms for it. This field should be used for non-specific variable searches.';
	public $fieldType = Select::class;
	public $fontAwesome = FontAwesome::QUESTION_CIRCLE;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::QUESTION_MARK;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $maxLength = self::MAX_LENGTH;
	public $minLength = VariableNameProperty::MIN_LENGTH + 2;
	public $name = self::NAME;
	public const NAME = 'synonyms';
	public $phpType = PhpTypes::ARRAY;
	public $rules = 'max:' . self::MAX_LENGTH;
	public $title = 'Synonyms';
	public $type = PhpTypes::ARRAY;
	public $canBeChangedToNull = false;
	public $validations = 'nullable|max:' . self::MAX_LENGTH;
	public $required = true;
	protected $shouldNotContain = [
		"\\\\\\",
		"Array",
	];
	/**
	 * @return array|string|string[]
	 * @throws \App\Exceptions\ModelValidationException
	 */
	public static function fixInvalidRecords(){
		parent::fixInvalidRecords();
		$fixed = [];
		$blackList = (new static())->getShouldNotContain();
		foreach($blackList as $item){
			$qb = Variable::withTrashed()->whereLike(self::NAME, '%' . $item . '%');
			$count = $qb->count();
			if($count){
				QMLog::error("$count variables with $item ");
			} else{
				continue;
			}
			$variables = $qb->limit(1000)->get();
			foreach($variables as $v){
				$broke = $v->attributes[self::NAME];
				QMLog::infoWithoutContext("broke: $broke");
				$fixed = str_replace('\\\\', '', $broke);
				$fixed = str_replace('\\"', "'", $fixed);
				$fixed = str_replace("'''", "", $fixed);
				$fixed = str_replace("['", '["', $fixed);
				$fixed = str_replace("']", '"]', $fixed);
				$fixed = str_replace('""', '"', $fixed);
				$fixed = str_replace("]''", ']', $fixed);
				$fixed = str_replace("''[", '[', $fixed);
				$fixed = str_replace('"[', '[', $fixed);
				$fixed = str_replace(']"', ']', $fixed);
				$fixed = str_replace('""', '"', $fixed);
				$fixed = str_replace("]'", ']', $fixed);
				$fixed = str_replace("'[", '[', $fixed);
				$fixed = str_replace('"[', '[', $fixed);
				$fixed = str_replace(']"', ']', $fixed);
				$fixed = str_replace("','", '","', $fixed);
				QMLog::infoWithoutContext("fixed: $fixed");
				$arr = json_decode($fixed);
				if(!$arr){
					QMLog::error("could not fix $fixed");
					continue;
				}
				$v->synonyms = $arr;
				$v->save();
				$fixed[] = $v;
				$updated = Variable::find($v->id);
				$after = $updated->attributes[self::NAME];
				$afterDecode = json_decode($after);
				if(!$afterDecode){
					le("Could not decode after save $after");
				}
				QMLog::infoWithoutContext("after save: $after");
			}
		}
		return $fixed;
	}
	/**
	 * @return array
	 */
	public static function fixNulls(): array{
		$fixed = [];
		$qb = Variable::withTrashed()->whereNull(self::NAME);
		$count = $qb->count();
		QMLog::error("$count variables with null synonyms");
		$variables = $qb->limit(500)->get();
		foreach($variables as $v){
			$name = $v->name;
			QMLog::infoWithoutContext($name);
			$v->synonyms = [$name];
			try {
				$v->save();
			} catch (ModelValidationException $e) {
				le($e);
				throw new \LogicException();
			}
			$fixed[] = $v;
		}
		return $fixed;
	}
	public static function fixEmptySynonyms(){
		$qb = Variable::withTrashed()->where(self::NAME, "");
		$count = $qb->count();
		QMLog::error("$count variables with empty synonyms");
		$variables = $qb->limit(500)->get();
		foreach($variables as $v){
			$name = $v->name;
			QMLog::infoWithoutContext($name);
			$v->synonyms = [$name];
			try {
				$v->save();
			} catch (ModelValidationException $e) {
				le($e);
				throw new \LogicException();
			}
		}
	}
	/**
	 * @return array
	 */
	public static function fixTooLong(): array{
		// https://stackoverflow.com/questions/1898453/does-index-on-varchar-make-performance-difference
		$max =
			600; // Also know that MySQL limits the amount of space set aside for indexes - they can be up to 1000 bytes long for MyISAM (767 bytes for InnoDB) tables.
		$qb = Variable::withTrashed()->whereRaw("length(synonyms) > $max");
		$count = $qb->count();
		QMLog::error("$count variables with synonym length > $max");
		$variables = $qb->limit(500)->get();
		foreach($variables as $v){
			QMLog::infoWithoutContext("Before: " . $v->attributes[self::NAME]);
			$name = $v->name;
			$newLength = strlen($name);
			if($newLength > $max - 10){
				le("new string too long $newLength chars");
			}
			QMLog::infoWithoutContext("$newLength After: " . $name);
			$v->synonyms = [$name];
			try {
				$v->save();
			} catch (ModelValidationException $e) {
				le($e);
				throw new \LogicException();
			}
		}
		return $variables;
	}
	/**
	 * @return void
	 * @throws \App\Exceptions\InvalidAttributeException
	 * @throws \App\Exceptions\InvalidStringAttributeException
	 * @throws \App\Exceptions\ModelValidationException
	 */
	public function validate(): void {
		if(!$this->shouldValidate()){
			return;
		}
		$this->validateMinLength();
		$this->validateMaxLength();
		$str = $this->getDBValue();
		if(!is_string($str)){
			$this->throwException("DBValue should be a string. ");
		}
		$this->assertAccessorValIsArray();
		self::makeSureSynonymsDoNotHaveDoubleSlashes($this->getAccessorValue());
		$this->assertIsJsonDecodable();
	}
	/**
	 * @param $syn
	 */
	public static function makeSureSynonymsDoNotHaveDoubleSlashes($syn): void{
		if(!is_array($syn)){
			$arr = json_decode($syn);
		} else{
			$arr = $syn;
		}
		foreach($syn as $item){
			if(stripos($item, '\\\\') !== false){
				le("Synonym should not contain double slash but is $item");
			}
		}
		$encoded = json_encode($arr);
		if(stripos($encoded, '\\\\') !== false){
			le("Encoded synonym should not contain slash but is $encoded");
		}
	}
	/**
	 * @throws \App\Exceptions\InvalidStringException
	 * @throws \App\Exceptions\ModelValidationException
	 */
	public function validateBlackListedStrings(){
		$synonyms = $this->getAccessorValue();
		$partialBlack = $this->getShouldNotContain();
		foreach($synonyms as $syn){
			QMStr::assertStringDoesNotContain($syn, $partialBlack, $this->name);
		}
		BaseSynonymsProperty::makeSureSynonymsDoNotHaveDoubleSlashes($synonyms);
	}
	/**
	 * @throws \App\Exceptions\ModelValidationException
	 */
	public function getHardCodedValue(): string{
		$str = implode("', '", $this->getAccessorValue());
		return "['$str']";
	}
	/**
	 * @return array|mixed|null
	 * @throws \App\Exceptions\InvalidAttributeException
	 * @throws \App\Exceptions\ModelValidationException
	 */
	protected function assertAccessorValIsArray(): void{
		$arr = $this->getAccessorValue();
		if(!is_array($arr)){
			$this->throwException("AccessorValue should be an array. ");
		}
	}
	/**
	 * @throws InvalidAttributeException
	 */
	protected function assertIsJsonDecodable(): void{
		$str = $this->getDBValue();
		$decoded = json_decode($str, true);
		if(!$decoded){
			$this->throwException("Could not decode $str");
		}
	}
	/**
	 * @param string $name
	 * @return array|bool
	 * @throws \App\Exceptions\RateLimitConnectorException
	 * @noinspection PhpUnused
	 */
	public static function fetchSynonyms(string $name): array{
		$response =
			APIHelper::getRequest('http://words.bighugelabs.com/api/2/bc71d9ade09209c84c0e1192c0681e35/' . $name .
				'/json');
		if(isset($response->error)){
			le($response->error, $response);
		}
		return $response;
	}
}
