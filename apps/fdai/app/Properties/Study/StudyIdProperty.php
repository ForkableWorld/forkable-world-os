<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Study;
use App\Exceptions\BadRequestException;
use App\Models\BaseModel;
use App\Models\Study;
use App\Properties\Base\BaseCauseVariableIdProperty;
use App\Properties\Base\BaseEffectVariableIdProperty;
use App\Properties\BaseProperty;
use App\Properties\User\UserIdProperty;
use App\Properties\Variable\VariableIdProperty;
use App\Slim\View\Request\QMRequest;
use App\Studies\QMCohortStudy;
use App\Studies\QMPopulationStudy;
use App\Studies\QMUserStudy;
use App\Traits\PropertyTraits\IsPrimaryKey;
use App\Traits\PropertyTraits\StudyProperty;
use App\Types\PhpTypes;
use App\Utils\AppMode;
use App\Variables\QMVariable;
use Illuminate\Support\Facades\Validator;
class StudyIdProperty extends BaseProperty{
	use IsPrimaryKey;
    use StudyProperty;
    public const INDIVIDUAL_STUDY_ID_SUFFIX = '-user-study';
    public const POPULATION_STUDY_ID_SUFFIX = '-population-study';
    public const COHORT_STUDY_ID_SUFFIX = "cohort-study";
    public const NAME = Study::FIELD_ID;
    public $table = Study::TABLE;
    public $parentClass = Study::class;
    public $minimum = null;
    public $maximum = null;
    public $minLength = 5;
    public $maxLength = 80;
    public $name = self::NAME;
    public $type = PhpTypes::STRING;
    public $dbType = PhpTypes::STRING;
	public $phpType = PhpTypes::STRING;
    public $canBeChangedToNull = false;
    public $required = true;
    public $autoIncrement = false;
    public $isPrimary = true;
    public $shouldNotContain = [
        '--'
    ];
    public const SYNONYMS = [
        'study_id',
        'study_client_id'
    ];
	/**
	 * @param $causeNameOrId
	 * @param $effectNameOrId
	 * @param int|null $userId
	 * @param string|null $type
	 * @return string
	 */
	public static function generateStudyId($causeNameOrId, $effectNameOrId, int $userId = null,
		string $type = null): string{
		$causeId = VariableIdProperty::pluck($causeNameOrId);
		$effectId = VariableIdProperty::pluck($effectNameOrId);
		if(!$type){
			$type = StudyTypeProperty::fromIdOrUrl();
		}
		if($type === StudyTypeProperty::TYPE_POPULATION){
			return QMPopulationStudy::generateStudyId($causeId, $effectId, $userId);
		}
		if($type === StudyTypeProperty::TYPE_INDIVIDUAL){
			return QMUserStudy::generateStudyId($causeId, $effectId, $userId);
		}
		if($type === StudyTypeProperty::TYPE_COHORT){
			return QMCohortStudy::generateStudyId($causeId, $effectId, $userId);
		}
		le("Please provide study type");
	}
	/**
	 * @param $causeNameOrId
	 * @param $effectNameOrId
	 * @param int|null $userId
	 * @param string|null $type
	 * @return mixed|null|string
	 */
	public static function generateStudyIdFromApiIfNecessary($causeNameOrId, $effectNameOrId, ?int $userId,
		?string $type){
		if(!$causeNameOrId){
			$causeNameOrId = BaseCauseVariableIdProperty::nameOrIdFromRequest();
		}
		if(!$effectNameOrId){
			$effectNameOrId = BaseEffectVariableIdProperty::nameOrIdFromRequest();
		}
		if(!$userId){
			$userId = UserIdProperty::fromRequestOrAuthenticated();
		}
		if(!$type){
			$type = StudyTypeProperty::fromIdOrUrl();
		}
		$id = (AppMode::isApiRequest()) ? StudyIdProperty::fromRequestDirectly() : null;
		if(!$id){
			if(!$causeNameOrId){
				$causeNameOrId = BaseCauseVariableIdProperty::nameOrIdFromRequest();
			}
			$id = self::generateStudyId($causeNameOrId, $effectNameOrId, $userId,
				$type);
		}
		return $id;
	}
	/**
	 * @param null $data
	 * @return string|null
	 */
	public static function getDefault($data = null): ?string{
        if(!$causeId = StudyCauseVariableIdProperty::pluckOrDefault($data)){
            return null;
        }
        if(!$effectId = StudyEffectVariableIdProperty::pluckOrDefault($data)){return null;}
        $userId = StudyUserIdProperty::pluck($data);
        $type = StudyTypeProperty::pluck($data);
        if(!$type){
            $type = StudyTypeProperty::getDefault($data);
        }
        if($type === StudyTypeProperty::TYPE_INDIVIDUAL && !$userId){
            $userId = StudyUserIdProperty::pluckOrDefault($data);
		if(!$userId){le( "No User ID provided for $type study for cause $causeId and effect $effectId");}
        }
        return self::generateStudyId($causeId, $effectId, $userId, $type);
    }
    /**
     * @param bool $throwException
     * @return string
     */
    public static function fromRequest(bool $throwException = false): ?string{
        $id = parent::fromRequest();
        if(!$id){
            $data = qm_request()->query();
            if(!$data){
                $data = qm_request()->input();
            }
            if(!$data){
                return null;
            }
            $id = static::generate($data);}
        if($id){
            $userId = UserIdProperty::fromRequest(false);
            /** @noinspection TypeUnsafeComparisonInspection */
            if($userId == $id){return null;} // TODO: Remove after we figure out why client is sending user id as study id
        }
        if($id === null && $throwException){static::throwMissingParameterException();}
        return $id;
    }
    /**
     * @param string|int $causeNameOrId
     * @param string|int $effectNameOrId
     * @return string
     */
    public static function generateStudyIdPrefix($causeNameOrId, $effectNameOrId): string{
        // Let's use id's instead of names because max client id length is 80
        $causeId = VariableIdProperty::pluckOrDefault($causeNameOrId);
        if(!$causeId){throw new BadRequestException("Please provided cause_variable_id parameter");}
        $effectId = VariableIdProperty::pluckOrDefault($effectNameOrId);
		if(!$effectId){throw new BadRequestException("Please provided effect_variable_id parameter");}
        return 'cause-'.$causeId.'-effect-'.$effectId;
    }
    public function cannotBeChangedToNull(): bool{
        return true;
    }
	/**
	 * @return void
	 * @throws \App\Exceptions\InvalidAttributeException
	 */
	public function validate(): void {
        if(!$this->shouldValidate()){return;}
        parent::validate();
        $study = $this->getStudy();
        $type = $study->type;
        $suffix = StudyTypeProperty::toIdSuffix($type);
        $this->assertContains([
            $study->cause_variable_id,
            $study->effect_variable_id,
            $suffix
        ]);
    }
    public static function getSynonyms(): array{
        $syn = parent::getSynonyms();
        return $syn;
    }
    public function __construct(BaseModel $parentModel = null){
        parent::__construct($parentModel);
    }
	/**
	 * @param $data
	 * @return string|null
	 */
	public static function generate($data): ?string{
        if(!$type = StudyTypeProperty::pluck($data)){return null;}
        if(!$causeId = StudyCauseVariableIdProperty::pluckOrDefault($data)){return null;}
        if(!$effectId = StudyEffectVariableIdProperty::pluckOrDefault($data)){return null;}
        $userId = StudyUserIdProperty::pluckOrDefault($data);
        $id = self::generateStudyId($causeId, $effectId, $userId, $type);
        return $id;
    }
    public function getExample(): string{
        return "1276-effect-1398-user-1-user-study";
    }
}
