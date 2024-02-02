<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Models\UserVariableRelationship;
use App\Astral\VariableBaseAstralResource;
use App\Properties\Study\StudyIdProperty;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Types\QMStr;
use Doctrine\DBAL\Types\Types;
use App\Fields\Field;
use App\Fields\HasOne;
use App\Variables\CommonVariables\EmotionsCommonVariables\OverallMoodCommonVariable;
use App\Models\GlobalVariableRelationship;
use App\Slim\View\Request\QMRequest;
use OpenApi\Generator;
class BaseEffectVariableIdProperty extends BaseVariableIdProperty{
    public $dbInput = 'integer,false,true';
	public $dbType = Types::INTEGER;
	public $default = Generator::UNDEFINED;
	public $description = 'ID of the outcome variable in the analysis. ';
	public $example = OverallMoodCommonVariable::ID;
	public $fieldType = self::TYPE_INTEGER;
	public $fontAwesome = FontAwesome::MANAGE_VARIABLES;
	public $htmlType = 'text';
	public $image = ImageUrls::PRIMARY_OUTCOME_VARIABLE;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $canBeChangedToNull = false;
	public $isOrderable = true;
	public $isSearchable = false;
	public const NAME = GlobalVariableRelationship::FIELD_EFFECT_VARIABLE_ID;
	public $name = self::NAME;
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $rules = 'required|integer|min:1|max:2147483647';
	public $title = 'Effect Variable';
	public $type = self::TYPE_INTEGER;
	public $validations = 'required';
	public const SYNONYMS = [
	    'effect_variable_id',
        'outcome_variable_id',
        'effect_id',
    ];
	public const NAME_SYNONYMS = [
        "effect_variable_name",
        "outcome_variable_name",
        "effect",
    ];
    public static function getDefault($data = null): ?int{
        if($studyId = StudyIdProperty::pluck($data)){return self::fromStudyId($studyId);}
        if($name = static::pluckName($data, false)){
            if(strpos($name, '*') !== false){return null;}
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
            if(!$studyId){return null;}
        }
        $effectId = QMStr::between($studyId, 'effect-', '-');
        $effectId = (int)$effectId;
        return $effectId;
    }
    public static function fromRequest(bool $throwException = false): ?int{
        $val = parent::fromRequest(false);
        if($val === null){
            $studyId = StudyIdProperty::fromRequest(false);
            if($studyId){return self::fromStudyId($studyId);}
        }
        if($val === null){$val = static::getDefault(qm_request()->input() + qm_request()->query());}
        if($val === null && $throwException){static::throwMissingParameterException();}
        return $val;
    }
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return Field
	 */
	public function getIndexField($resolveCallback = null, string $name = null): Field{
        return $this->getDetailLinkTextField($name, function($value, $resource, $attribute){
            /** @var UserVariableRelationship $resource */
            return $resource->getEffectVariableName();
        });
    }
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return Field
	 */
	public function getDetailsField($resolveCallback = null, string $name = null): Field{
        return VariableBaseAstralResource::belongsTo($name ?? $this->getTitleAttribute(), $this->relationshipMethod());
    }
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return Field
	 */
	public function getCreateField($resolveCallback = null, string $name = null): Field{
        return HasOne::make('Effect', 'effect_variable', VariableBaseAstralResource::class);
    }
    public function showOnUpdate(): bool {return false;}
    public function showOnCreate(): bool {return false;}
    public function showOnIndex(): bool {return false;}
    public function showOnDetail(): bool {return true;}
}
