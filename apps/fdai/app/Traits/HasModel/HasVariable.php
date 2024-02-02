<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\HasModel;
use App\Buttons\States\VariableStates\ReminderAddStateButton;
use App\Exceptions\InvalidVariableValueException;
use App\Models\BaseModel;
use App\Models\UserVariable;
use App\Models\Variable;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\Base\BaseCombinationOperationProperty;
use App\Properties\Base\BaseFillingTypeProperty;
use App\Properties\Variable\VariableNameProperty;
use App\Slim\Model\DBModel;
use App\Slim\Model\QMUnit;
use App\Traits\HasMany\HasManyMeasurements;
use App\UI\HtmlHelper;
use App\Units\YesNoUnit;
use App\Utils\IonicHelper;
use App\Variables\QMCommonVariable;
use App\Variables\QMUserVariable;
use App\Variables\QMVariable;
use App\Variables\VariableSearchResult;
trait HasVariable {
	use HasUnit, HasManyMeasurements;
	protected ?array $valueInvalidMessages = [];
	abstract public function getVariableIdAttribute(): ?int;
	public function hasVariableId(): bool{
		$nameOrId = $this->getAttribute('variable_id');
		return (bool)$nameOrId;
	}
	public function getVariableName(): ?string{
		if($fromMem = $this->getVariableFromMemory()){
			return $fromMem->name;
		}
		if($this instanceof VariableSearchResult){
			if($name = $this->variableName ?? $this->name ?? null){
				/** @var VariableSearchResult $this */
				return $this->setVariableName($name);
			}
			if($name === ""){
				le('$name === ""');
			}
		}
		if($this instanceof DBModel){
			if($name = $this->variableName ?? null){
				return $name;
			}
			if($name === ""){
				le('$name === ""');
			}
		}
		if($this instanceof BaseModel){
			if($v = $this->relations['variable'] ?? null){
				/** @var Variable $v */
				if($v){
					return $v->name;
				}
			}
		}
		if($this instanceof DBModel){
			if($cv = $this->commonVariable ?? null){
				/** @var QMCommonVariable $v */
				return $cv->name;
			}
		}
		if($this instanceof Variable){
			if($name = $this->name ?? null){
				return $name;
			}
			if($name === ""){
				le('$name === ""');
			}
		}
		if(property_exists($this, 'userVariable') && $this->userVariable instanceof QMUserVariable){
			return $this->userVariable->getVariableName();
		}
		return VariableNameProperty::fromId($this->getVariableIdAttribute());
	}
	/**
	 * @return Variable
	 */
	public function getVariable(): Variable{
		if($this instanceof Variable){
			/** @var Variable $this */
			/** @noinspection PhpIncompatibleReturnTypeInspection */
			return $this;
		}
		$v = null;
		if(!$v && property_exists($this, 'parentModel')){
			$parent = $this->parentModel;
			if($parent instanceof Variable){
				$v = $parent;
			}
			if($parent instanceof UserVariable){
				$v = $parent->getVariable();
			}
		}
		if(!$v && property_exists($this, 'relations')){
			$v = $this->relations["variable"] ?? null;
		}
		if(!$v && property_exists($this, 'commonVariable') && $this->commonVariable){
			$cv = $this->commonVariable;
			$v = $cv->l();
		}
		if(!$v && property_exists($this, 'laravelModel') && $this->laravelModel){
			/** @var BaseModel $l */
			$l = $this->l();
			$v = $l->getRelationIfLoaded('variable');
		}
		if(!$v){
			$id = $this->getVariableIdAttribute();
            if(!$id){
                le('no id', $this);
            }
			$v = Variable::findInMemoryOrDB($id);
		}
		if($v && property_exists($this, 'relations')){
			$this->relations['variable'] = $v; // Faster than searching memory all the time
		}
		return $v;
	}
	public function getQMCommonVariable(): QMCommonVariable{
		$l = $this->parentModel ?? null;
		if($l instanceof Variable){
			return $l->getDBModel();
		}
		return QMCommonVariable::find($this->getVariableIdAttribute());
	}
	public function getVariableNameLink(): string{
		return $this->getVariable()->getDataLabDisplayNameLink();
	}
	public function getVariableImageNameLink(): string{
		return $this->getVariable()->getDataLabImageNameLink([],
			"height: 25px; border-radius: 0; cursor: pointer; object-fit: scale-down; margin: auto;");
	}
	public function getVariableImage(): string{
		return $this->getVariable()->getImage();
	}
	abstract public function getImage(): string;
	public function getCommonUnit(): QMUnit{
		$id = $this->getCommonUnitId();
		return QMUnit::find($id);
	}
	public function getCommonUnitId(): int{
		$id = null;
		if(!$id && property_exists($this, 'commonUnitId')){
			$id = $this->commonUnitId ?? null;
		}
		if(!$id){
			$id = $this->getVariable()->getUnitIdAttribute();
		}
		return $id;
	}
	protected function validateBetweenMinMaxRecorded(?float $value){
		if($value === null){
			return;
		}
		$v = $this->getVariable();
		$min = $v->minimum_recorded_value;
		if($v->filling_type === BaseFillingTypeProperty::FILLING_TYPE_ZERO && $min > 0){
			$min = 0;
		}
		$max = $v->maximum_recorded_value;
		if($value < $min){
			le('$value < $min');
		}
		if($value > $max){
			le('$value > $max');
		}
	}
	abstract public function getVariableCategoryId(): int;
	/**
	 * @return Variable|null
	 */
	public function getVariableFromMemory(): ?Variable{
		if(method_exists($this, 'getRelation') && method_exists($this, 'relationLoaded') &&
			$this->relationLoaded('variable')){
			return $this->getRelation('variable');
		}
		$id = $this->getVariableIdAttribute();
        if(!$id){
            le('no getVariableIdAttribute from', $this);
        }
		$v = Variable::findInMemory($id);
		if($v){
			return $v;
		}
		return null;
	}
	/**
	 * @return string
	 */
	public function getFillingValueSentence(): string{
		$filling = $this->getVariable()->getFillingValueAttribute();
		/** @noinspection TypeUnsafeComparisonInspection */
		if($filling === null || $filling == -1){
			return "No missing data filling value was defined for " . $this->getDisplayNameAttribute() .
				" so any gaps in data were just not analyzed instead of assuming zero values for those times. ";
		}
		$filling = $this->getCommonUnit()->getValueAndUnitString($filling);
		return "It was assumed that any gaps in " . $this->getDisplayNameAttribute() . " data were unrecorded " .
			$filling . " measurement values. ";
	}
	/**
	 * @return string
	 */
	public function getMinimumAllowedValueSentence(): string{
		$min = $this->getVariable()->getMinimumAllowedValueAttribute();
		if($min === null){
			return "No minimum allowed measurement value was defined for " . $this->getDisplayNameAttribute() . ". ";
		}
		$min = $this->getCommonUnit()->getValueAndUnitString($min);
		return $this->getDisplayNameAttribute() . " measurement values below $min were assumed erroneous and removed. ";
	}
	/**
	 * @return string
	 */
	public function getMaximumAllowedValueSentence(): string{
		$max = $this->getVariable()->getMaximumAllowedValueAttribute();
		if($max === null){
			return "No maximum allowed measurement value was defined for " . $this->getDisplayNameAttribute() . ". ";
		}
		$max = $this->getCommonUnit()->getValueAndUnitString($max);
		return $this->getDisplayNameAttribute() . " measurement values above $max were assumed erroneous and removed. ";
	}
	protected function logVariableSettingsLink(){
		$this->logLink($this->getVariableSettingsUrl(), "$this->name Settings");
	}
	/**
	 * @param array $params
	 * @return string
	 */
	public function getVariableSettingsUrl(array $params = []): string{
		$clientId = BaseClientIdProperty::fromMemory();
		return IonicHelper::getIonicAppUrl($clientId, 'variable-settings/' . urlencode($this->getVariableName()), $params);
	}
	public function getVariableSettingsLink(): string{
		$name = $this->getTitleAttribute();
		return HtmlHelper::generateLink("$name Variable Settings", $this->getVariableSettingsUrl(), true,
			"Click to modify the variable analysis settings for $name.");
	}
	/**
	 * @return string
	 */
	public function getDisplayNameAttribute(): string{
		return VariableNameProperty::variableToDisplayName($this->getVariable());
	}
	public function getCreateReminderButton(array $params = []): ReminderAddStateButton{
		$b = new ReminderAddStateButton($this->getVariable(), $params);
		return $b;
	}
	/**
	 * @param float|string $valueInCommonUnit
	 * @param string|null $type
	 * @param int|null $durationInSeconds
	 * @param QMVariable|Variable $v
	 * @throws InvalidVariableValueException
	 */
	public function validateValueForCommonVariableAndUnit($valueInCommonUnit, string $type,
		int $durationInSeconds = null, $v = null){
		$message = $this->valueInvalidForCommonVariableOrUnit($valueInCommonUnit, $type, $durationInSeconds, $v);
		if($message){
			throw new InvalidVariableValueException($message . " for $type", $this->getVariable(), $durationInSeconds);
		}
	}
	/**
	 * @param float $valueInCommonUnit
	 * @param string $type
	 * @throws InvalidVariableValueException
	 */
	public function validateDailyValue(float $valueInCommonUnit, string $type): void {
		$this->validateValueForCommonVariableAndUnit($valueInCommonUnit, $type, 86400, $this);
	}
	/**
	 * @param float $valueInCommonUnit
	 * @param int|null $durationInSeconds
	 * @return string
	 */
	public function valueInvalidForCommonUnit(float $valueInCommonUnit, int $durationInSeconds = null): ?string{
		$unit = $this->getCommonUnit();
		return $unit->valueInvalid($valueInCommonUnit, $durationInSeconds);
	}
	/**
	 * @param float|string $valueInCommonUnit
	 * @param string|null $type
	 * @param int|null $durationInSeconds
	 * @param QMVariable|null|Variable $v
	 * @return string|null
	 */
	public function valueInvalidForCommonVariableOrUnit($valueInCommonUnit, string $type, int $durationInSeconds = null,
		$v = null): ?string{
		// DON'T REQUIRE $valueInCommonUnit TO BE FLOAT OR WE GET ERRORS WHEN PASSING STRING ROUNDED VALUES!
		if($type === UserVariable::FIELD_FILLING_VALUE && $valueInCommonUnit == -1){
			return false;
		}
		$index = (string)$valueInCommonUnit;
		$previous = $this->valueInvalidMessages[$index][$durationInSeconds ?? 0] ?? null;
		if($previous === false){
			return null;
		}
		if($previous !== null){
			return $previous;
		}
		if($valueInCommonUnit && is_string($valueInCommonUnit)){
			$valueInCommonUnit = (float)$valueInCommonUnit;
		} // Sometimes this gets turned into string in user_variable_relationships
        if($valueInCommonUnit === null){
            le("valueInCommonUnit is null for $type");
        }
		$message = $this->valueInvalidForVariable($valueInCommonUnit, $type, $v ?? $this, $durationInSeconds);
		if(!$message){
			$message = $this->valueInvalidForCommonUnit($valueInCommonUnit, $durationInSeconds);
		}
		if(!$message){
			$this->valueInvalidMessages[$index][$durationInSeconds ?? 0] = false;
			return null;
		}
		return $this->valueInvalidMessages[$index][$durationInSeconds ?? 0] = $message;
	}
	/**
	 * @return float|null
	 */
	public function getCommonMaximumAllowedDailyValue(): ?float{
		return $this->getVariable()->getMaximumAllowedDailyValue();
	}
	/**
	 * @param float $valueInCommonUnit
	 * @param string|null $type
	 * @param QMVariable|null $v
	 * @param int|null $durationInSeconds
	 * @return string|null
	 */
	protected function valueInvalidForVariable(float $valueInCommonUnit, string $type, $v = null,
		int $durationInSeconds = null): ?string{
		/** @var Variable $v */
		$v = $this->getVariable();
		$commonUnit = $this->getCommonUnit();
		if($durationInSeconds &&
			$this->getCombinationOperation() === BaseCombinationOperationProperty::COMBINATION_SUM){
			$valueInCommonUnit = $valueInCommonUnit / ($durationInSeconds / 86400);
			$maxInCommonUnit = $v->maximum_allowed_daily_value;
			if($maxInCommonUnit !== null){
				if($maxInCommonUnit < $valueInCommonUnit){
					return "$type value $valueInCommonUnit $commonUnit->abbreviatedName  exceeds maximum 
$maxInCommonUnit $commonUnit->abbreviatedName for variable $this->name";
				}
			}
		}
		$maxInCommonUnit = $v->maximum_allowed_value;
		if($maxInCommonUnit !== null && $maxInCommonUnit < $valueInCommonUnit){
			if($commonUnit->id === YesNoUnit::ID){
				return null;
			} // YesNo is countable/summable
			return "$type value $valueInCommonUnit $commonUnit->abbreviatedName
                exceeds maximum $maxInCommonUnit $commonUnit->abbreviatedName for variable $this->name
                maximum_allowed_value = {$v->maximum_allowed_value};
               {$v->getUrl()}";
		}
		$min = $v->minimum_allowed_value;
		if($min !== null && $min > $valueInCommonUnit){
			return "$type value $valueInCommonUnit $commonUnit->abbreviatedName
                is below minimum $min $commonUnit->abbreviatedName for variable $this->name
                View and Delete at:
                {$v->getUrl()} ";
		}
		return null;
	}
	/**
	 * @return string
	 */
	protected function getCombinationOperation(): string{
		$op = BaseCombinationOperationProperty::getCombinationOperationFromUnitOrCategory($this->getCommonUnit(),
			$this->getQMVariableCategory());
		return $op;
	}
	public function getMinimumAllowedDailyValue(): ?float{
		return $this->getVariable()->getMinimumAllowedDailyValue();
	}
	/**
	 * @return int
	 */
	public function getMinimumAllowedSecondsBetweenMeasurements(): int{
		$secs = $this->getVariable()->getMinimumAllowedSecondsBetweenMeasurementsAttribute();
		return $secs;
	}
	public function getCommonMinimumAllowedValue(): ?float{
		return $this->getVariable()->getMinimumAllowedValueAttribute();
	}
	public function getUnitIdAttribute(): ?int{
		if(property_exists($this, 'commonUnitId') && $this->commonUnitId){
			return $this->commonUnitId;
		}
		return $this->getVariable()->default_unit_id;
	}
	public function getVariableAvatarImageHtml($size = 6): string{
		return HtmlHelper::getAvatarImageHtml($this->getVariableImage(), $this->name, $size);
	}
}
