<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariable;
use App\Exceptions\IncompatibleUnitException;
use App\Exceptions\InvalidAttributeException;
use App\Exceptions\ModelValidationException;
use App\Exceptions\UserVariableNotFoundException;
use App\Logging\QMLog;
use App\Models\BaseModel;
use App\Models\TrackingReminder;
use App\Models\TrackingReminderNotification;
use App\Models\UserVariable;
use App\Models\Variable;
use App\Properties\Base\BaseDefaultUnitIdProperty;
use App\Properties\Measurement\MeasurementOriginalUnitIdProperty;
use App\Slim\Model\QMUnit;
use App\Storage\DB\QMDB;
use App\Storage\DB\QMQB;
use App\Storage\DB\Writable;
use App\Traits\PropertyTraits\UserHyperParameterTrait;
use App\Traits\PropertyTraits\UserVariableProperty;
use App\Units\HectopascalUnit;
use App\Units\KilogramsUnit;
use App\Units\MilesUnit;
use App\Units\MinutesUnit;
use App\Variables\QMUserVariable;
class UserVariableDefaultUnitIdProperty extends BaseDefaultUnitIdProperty {
	use UserVariableProperty, UserHyperParameterTrait;
	public const SYNONYMS = [
		'original_unit_id',
		'default_unit_id',
		'unit_id',
	];
	public $parentClass = UserVariable::class;
	public $table = UserVariable::TABLE;
	/**
	 * @param QMUserVariable $variable
	 * @return QMUserVariable
	 */
	public static function changeYesNoToCount(QMUserVariable $variable): QMUserVariable{
		if($variable->isYesNo()){
			$variable->getCommonVariable()->updateDefaultUnitIdOnlyInDBRow(QMUnit::getCount()->id);
			$variable->updateDbRow([UserVariable::FIELD_DEFAULT_UNIT_ID => null]);
			$variable = QMUserVariable::getOrCreateById($variable->getUserId(), $variable->getVariableIdAttribute());
			$unit = $variable->getCommonUnit();
			if($unit->isYesNo()){
				le("Still yes no after changing to count!");
			}
		}
		return $variable;
	}
	public static function fixIncompatibleUnits(){
		$res = Writable::selectStatic("
            select count(*) as num from user_variables uv
                join variables v on v.id = uv.variable_id
                where uv.default_unit_id = v.default_unit_id
                  and uv.default_unit_id is not null;
        ");
		$count = $res[0]->num;
		\App\Logging\ConsoleLog::info("$count user variables with redundant unit id set");
		if($count){
			QMLog::error("$count user variables with redundant unit id set");
			Writable::statementStatic("
                update user_variables uv
                    join variables v on v.id = uv.variable_id
                set uv.default_unit_id = null
                where uv.default_unit_id = v.default_unit_id
                    and uv.default_unit_id is not null;
            ");
		}
		$minute = MinutesUnit::ID;
		Writable::statementStatic("
                update user_variables uv
                    join variables v on v.id = uv.variable_id
                set uv.default_unit_id = null
                where uv.default_unit_id = $minute
                    and uv.default_unit_id is not null;
            ");
		$variables = UserVariable::whereNotNull(UserVariable::FIELD_DEFAULT_UNIT_ID)
			->where(UserVariable::FIELD_DEFAULT_UNIT_ID, "<>", MilesUnit::ID) // Too many
			->where(UserVariable::FIELD_DEFAULT_UNIT_ID, "<>", KilogramsUnit::ID) // Too many
			->get();
		foreach($variables as $v){
			$qmV = $v->getDBModel();
			$qmV->logInfo("user " . $qmV->getUserUnitAbbreviatedName() . " | common: " .
				$qmV->getCommonUnit()->abbreviatedName);
			$qmV->validate();
		}
	}
	/**
	 * @return null
	 */
	public function getExample(){
		return null;
	}
	/**
	 * @param $value
	 * @return int|null
	 */
	public function toDBValue($value): ?int{
		$variable = $this->getVariable();
		if($value === $variable->default_unit_id){
			return null;
		}
		return $value;
	}
	/**
	 * @param $data
	 * @param UserVariable $uv
	 * @return UserVariable
	 * @noinspection PhpDocSignatureInspection
	 */
	public static function updateFromData($data, BaseModel $uv){
		$fromData = MeasurementOriginalUnitIdProperty::pluckOrDefault($data);
		if($fromData){
			$v = $uv->getVariable();
			if(!$v){
				$v = UserVariableVariableIdProperty::findRelated($data);
			}
			if(!$v){
				le('!$v');
			}
			$commonUnitId = $v->default_unit_id;
			$currentValue = $uv->default_unit_id;
			if($currentValue === $fromData){
				return $uv;
			}
			if(QMUnit::unitIsIncompatible($commonUnitId, $fromData)){
				try {
					$userId = UserVariableUserIdProperty::pluckOrDefault($data);
					$withNewUnit = QMUserVariable::getByNameOrId($userId, $v->id, [], $data);
					try {
						QMUnit::validateUnitCompatibility($fromData, $withNewUnit->getCommonUnitId());
					} catch (IncompatibleUnitException $e) {
						le($e);
					}
					TrackingReminderNotification::whereUserVariableId($uv->id)->forceDelete();
					TrackingReminder::whereUserVariableId($uv->id)->forceDelete();
					return $withNewUnit->l();
				} catch (UserVariableNotFoundException $e) {
					le($e);
				}
			} else{
				try {
					parent::updateFromData($data, $uv);
				} catch (ModelValidationException $e) {
					le($e);
				}
				$dbm = $uv->getDBModel();
				$dbm->setUserUnit($fromData);
				if($fromData !== $dbm->userUnitId){
					le('$fromData !== $dbm->userUnitId');
				}
			}
		}
		return $uv;
	}
	/**
	 * @return \App\Storage\DB\QMQB|\Illuminate\Database\Query\Builder
	 */
	public static function whereInvalidQMQB(): QMQB{
		$qb = QMUserVariable::writable()->select(UserVariable::TABLE . "." . UserVariable::FIELD_VARIABLE_ID,
				UserVariable::TABLE . "." . UserVariable::FIELD_USER_ID,
				UserVariable::TABLE . "." . UserVariable::FIELD_DEFAULT_UNIT_ID)
			->join(Variable::TABLE, Variable::TABLE . "." . Variable::FIELD_ID, "=",
				UserVariable::TABLE . "." . UserVariable::FIELD_VARIABLE_ID)->whereNotNull(UserVariable::TABLE . "." .
				UserVariable::FIELD_DEFAULT_UNIT_ID)->whereRaw(UserVariable::TABLE . "." .
				UserVariable::FIELD_DEFAULT_UNIT_ID . " <> " . Variable::TABLE . "." . Variable::FIELD_DEFAULT_UNIT_ID);
		return $qb;
	}
	public function getUserUnit(): QMUnit{
		return QMUnit::find($this->getAccessorValue());
	}
	/**
	 * @throws InvalidAttributeException
	 * @throws \App\Exceptions\IncompatibleUnitException
	 */
	public function validate(): void {
		if(!$this->shouldValidate()){
			return;
		}
		$id = $this->getDBValue();
		if($id === null){
			return;
		}
		if(!QMUnit::idExists($id)){
			$this->throwException("Unit with id $id does not exist");
		}
		$v = $this->getVariable();
		$commonUnit = $this->getCommonUnit();
		$newUnit = QMUnit::getByNameOrId($id);
		QMUnit::validateUnitCompatibility($commonUnit, $newUnit, $this->getUserVariable());
		// Trying to figure out what is changing default unit for barometric pressure?
		if(in_array($id, [HectopascalUnit::ID])){
			le("Why are we changing unit for $this to $newUnit?");
		}
		$ratingOrSymptom = $v->isEmotion() || $v->isSymptom();
		$countWithNumberOf = $newUnit->isCount() && stripos($v->name, "Number of ");
		if(!$newUnit->isYesNo() && $ratingOrSymptom && !$countWithNumberOf && !$newUnit->isRating()){
			throw new IncompatibleUnitException($commonUnit, $newUnit, $this,
				"Symptoms or emotions must have rating unit");
		}
		if($newUnit->getId() === $commonUnit->id){
			$this->throwException("New unit $newUnit is the same as the common unit!");
		}
	}
}
