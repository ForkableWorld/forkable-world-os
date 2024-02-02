<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Variable;
use App\Logging\QMLog;
use App\Models\Variable;
use App\Models\VariableCategory;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\Base\BaseIsPublicProperty;
use App\Slim\Model\QMUnit;
use App\Traits\PropertyTraits\IsCalculated;
use App\Traits\PropertyTraits\VariableProperty;
use App\Variables\QMVariableCategory;
use Illuminate\Database\Eloquent\Builder;
class VariableIsPublicProperty extends BaseIsPublicProperty
{
    use VariableProperty, IsCalculated;
    public $table = Variable::TABLE;
    public $parentClass = Variable::class;
    protected static function setVariablesWithMoreThan5UsersPublic(): void {
        $qb = Variable::query()
	        ->where(Variable::FIELD_NUMBER_OF_USER_VARIABLES, ">", 5);
        VariableNameProperty::whereNotTestVariable($qb);
        $updated = $qb->update([Variable::FIELD_IS_PUBLIC => true]);
        QMLog::info("Set $updated variables public because greater than 5 user variables");
    }
    protected static function setGovtDBVariablesPublic(): void {
        $updated = Variable::query()
            ->whereIn(Variable::FIELD_CLIENT_ID, BaseClientIdProperty::getClientIdsWithPublicVariables())
            ->update([Variable::FIELD_IS_PUBLIC => true]);
        QMLog::info("Set $updated variables public because they originated from a government database..");
    }
    protected static function setCureTogetherVariablesPublic(): void {
        $updated = Variable::query()
            ->where(Variable::FIELD_CLIENT_ID, BaseClientIdProperty::CLIENT_ID_CURE_TOGETHER)
            ->update([Variable::FIELD_IS_PUBLIC => true]);
        QMLog::info("Set $updated variables public because they originated from CURE_TOGETHER..");
    }
    protected static function setTestVariablesPrivate(): void {
        $updated = VariableNameProperty::whereTestVariable()
                ->withTrashed()
                ->where(Variable::FIELD_IS_PUBLIC, "<>", false)
                ->update([Variable::FIELD_IS_PUBLIC => false]);
        QMLog::info("Set $updated variables private because they were test variables");
    }
    protected static function setPrivateNamesPrivate(): void{
        foreach(VariableNameProperty::PRIVATE_NAMES_LIKE as $needle){
            $updated = VariableNameProperty::whereLike($needle)
                ->update([static::NAME => false]);
            QMLog::info("Set $updated variables private because they were Phone Call");
        }
    }
	/**
	 * @return void
	 * @throws \App\Exceptions\InvalidAttributeException
	 */
	public function validate(): void {
        $model  = $this->getVariable();
        $value = $this->getDBValue();
        if($value && $model->isTestVariable()){
            $this->throwException("Why is a test variable public?");
        }
        parent::validate();
    }
    public static function updateAll(){
        self::setCureTogetherVariablesPublic();
        self::setVariablesWithMoreThan5UsersPublic();
        self::setGovtDBVariablesPublic();
        self::setTestVariablesPrivate();
        self::setPrivateNamesPrivate();
        self::assertNoInvalidRecords();
    }
    /**
     * @param $providedParams
     * @param $data
     * @param QMUnit $unit
     * @param VariableCategory|QMVariableCategory $variableCategory
     * @param string $name
     * @return array
     */
    public static function setPublicNewVariableField($providedParams, $data, QMUnit $unit, $variableCategory,
                                                     string $name): array{
        if(VariableNameProperty::isTest($name)){
            $data[self::NAME] = 0;
            return $data;
        }
        if (isset($providedParams[self::NAME])) {
            $data[self::NAME] = $providedParams[self::NAME];
        } else {
            $data[self::NAME] = isset($data[Variable::FIELD_PRODUCT_URL]) ? 1 : 0;
            if ($unit->isCurrency()) {
                $data[self::NAME] = 1;
            }
            if ($variableCategory->getIsPublic()
               // && $variableCategory->getDefaultUnitId() === $unit->id
            ) {
                $data[self::NAME] = 1;
            }
        }
        return $data;
    }
    /**
     * @param Variable $model
     * @return bool|null
     */
    public static function calculate($model): ?bool{
        $val = $model->getAttribute(static::NAME);
        if($model->isTestVariable()){
            $val = false;
        } elseif($model->getNumberOfUserVariables() > 5){
            $val = true;
        } else {
            $userVariables = $model->getPublicUserVariables();
            if($userVariables->count()){
                $val = true;
            }
        }
        if($model->hasPrivateName()){
            $val = false;
        }
        $model->setAttribute(static::NAME, $val);
        return $val;
    }
	/**
	 * @return void
	 */
	public static function fixInvalidRecords(){
        self::whereInvalid()
            ->update([self::NAME => false]);
    }
    public static function whereInvalid(): Builder{
        return VariableNameProperty::whereTestVariable()
            ->where(Variable::FIELD_IS_PUBLIC, "<>", false);
    }
}
