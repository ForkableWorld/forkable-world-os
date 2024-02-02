<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Exceptions\UnauthorizedException;
use App\Models\UserVariableRelationship;
use App\Astral\VariableBaseAstralResource;
use App\Properties\Study\StudyIdProperty;
use App\Traits\ForeignKeyIdTrait;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Types\QMStr;
use App\Fields\Field;
use App\Fields\HasOne;
use App\Slim\Middleware\QMAuth;
use App\Variables\CommonVariables\TreatmentsCommonVariables\BupropionSrCommonVariable;
use App\Variables\QMUserVariable;
use App\Slim\View\Request\QMRequest;
class BaseCauseVariableIdProperty extends BaseVariableIdProperty {
	use ForeignKeyIdTrait;
    public $description = 'The cause or predictor variable is assumed to be the stimulus and is combined with outcome data following a defined onset delay over a duration of action period. ';
    public $example = BupropionSrCommonVariable::ID;
    public $fontAwesome = FontAwesome::MANAGE_VARIABLES;
    public $image = ImageUrls::PRIMARY_OUTCOME_VARIABLE;
    public $canBeChangedToNull = false;
    public $title = 'Cause Variable';
    public $name = self::NAME;
	public const NAME = 'cause_variable_id';
    public const SYNONYMS = [
        'cause_variable_id',
        'predictor_variable_id',
        'cause_id',
    ];
	public const NAME_SYNONYMS = [
        'predictor_variable_name',
        'cause_variable_name',
        'cause',
    ];
    /**
     * @param null $data
     * @return int|null
     */
    public static function getDefault($data = null): ?int{
        if($studyId = StudyIdProperty::pluck($data)){
            return self::fromStudyId($studyId);
        }
        if($name = static::pluckName($data)){
            if(strpos($name, '*') !== false){
                return null;
            }
            return BaseVariableIdProperty::fromNameOrNew($name);
        }
        return null;
    }
    /**
     * @param string|null $studyId
     * @return int
     */
    public static function fromStudyId(string $studyId = null): ?int{
        if(!$studyId){
            $studyId = StudyIdProperty::fromRequest();
            if(!$studyId){
                return null;
            }
        }
        $effectId = QMStr::between($studyId, 'cause-', '-effect');
        $effectId = (int)$effectId;
        return $effectId;
    }
    public static function fromRequest(bool $throwException = false): ?int{
        $val = parent::fromRequest(false);
        if($val === null){
            $studyId = StudyIdProperty::fromRequest(false);
            if($studyId){
                return self::fromStudyId($studyId);
            }
        }
        if($val === null){
            $val = static::getDefault(qm_request()->input() + qm_request()->query());
        }
        if($val === null && $throwException){
            self::throwMissingParameterException();
        }
        return $val;
    }
    /**
     * @return QMUserVariable|null
     * @throws UnauthorizedException
     */
    public static function getCauseUserVariable(): ?QMUserVariable{
        $nameOrId = self::nameOrIdFromRequest();
        if(!$nameOrId){
            return null;
        }
        return QMUserVariable::findByNameIdSynonymOrSpending(QMAuth::id(), $nameOrId);
    }
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return Field
	 */
	public function getCreateField($resolveCallback = null, string $name = null): Field{
        return HasOne::make('Cause', 'cause_variable', VariableBaseAstralResource::class);
    }
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return Field
	 */
	public function getIndexField($resolveCallback = null, string $name = null): Field{
        return $this->getDetailLinkTextField($name, function($value, $resource, $attribute){
            /** @var UserVariableRelationship $resource */
            return $resource->getCauseVariableName();
        });
    }
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return Field
	 */
	public function getDetailsField($resolveCallback = null, string $name = null): Field{
		//return $this->getBelongsToField($name);
        return VariableBaseAstralResource::belongsTo($name ?? $this->getTitleAttribute(), $this->relationshipMethod());
    }
    public function showOnUpdate(): bool {return false;}
    public function showOnCreate(): bool {return false;}
    public function showOnIndex(): bool {return false;}
    public function showOnDetail(): bool {return true;}
    public static function nameOrIdFromRequest(bool $throwException = false){
        return parent::nameOrIdFromRequest($throwException);
    }
}
