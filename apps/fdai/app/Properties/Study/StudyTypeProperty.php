<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn 
 */

namespace App\Properties\Study;
use App\Exceptions\InvalidAttributeException;
use App\Exceptions\UnauthorizedException;
use App\Logging\QMLog;
use App\Models\Study;
use App\Properties\Base\BaseCauseVariableIdProperty;
use App\Properties\Base\BaseEffectVariableIdProperty;
use App\Properties\Base\BaseTypeProperty;
use App\Slim\Middleware\QMAuth;
use App\Slim\View\Request\QMRequest;
use App\Studies\QMCohortStudy;
use App\Studies\QMPopulationStudy;
use App\Studies\QMUserStudy;
use App\Traits\PropertyTraits\StudyProperty;
class StudyTypeProperty extends BaseTypeProperty
{
    use StudyProperty;
    public const TYPE_INDIVIDUAL = 'individual';
    public const TYPE_POPULATION = 'population';
    public const TYPE_COHORT = 'cohort';
    public $table = Study::TABLE;
    public $parentClass = Study::class;
	/**
	 * @param $data
	 * @return mixed|null
	 */
	public static function pluckOrDefault($data){
        return parent::pluckOrDefault($data);
    }
	/**
	 * @param null $data
	 * @return string|null
	 */
	public static function getDefault($data = null): ?string{
        if($id = StudyIdProperty::pluck($data)){
            if($type = self::fromId($id)){return $type;}
        }
        if($userId = StudyUserIdProperty::pluck($data)){
            QMUserStudy::authorizeStudy(BaseCauseVariableIdProperty::pluckOrDefault($data),
	            BaseEffectVariableIdProperty::pluckOrDefault($data), $userId);
            return self::TYPE_INDIVIDUAL;
        }
        if(self::loggedInUserHasCauseEffectData()){
            return self::TYPE_INDIVIDUAL;
        }
        return self::TYPE_POPULATION;
    }
    /**
     * @param string $studyId
     * @return string
     * @noinspection PhpParameterNameChangedDuringInheritanceInspection
     */
    public static function fromId($studyId): ?string{
        if(stripos($studyId, self::TYPE_INDIVIDUAL) !== false){
            return self::TYPE_INDIVIDUAL;
        }
        if(stripos($studyId, self::TYPE_POPULATION) !== false){
            return self::TYPE_POPULATION;
        }
        if(stripos($studyId, self::TYPE_COHORT) !== false){
            return self::TYPE_COHORT;
        }
        if(stripos($studyId, 'user-study') !== false){
            return self::TYPE_INDIVIDUAL;
        }
        QMLog::error("Could not determine study type for study id: $studyId");
        return null;
    }
	public static function fromClass(string $class): string{
		$map = [
			QMCohortStudy::class => self::TYPE_COHORT,
			QMUserStudy::class => self::TYPE_INDIVIDUAL,
			QMPopulationStudy::class => self::TYPE_POPULATION,
		];
		return $map[$class];
	}
	/**
     * @param string|null $studyId
     * @return string
     */
    public static function fromIdOrUrl(string $studyId = null): ?string{
        if(!$studyId){
            $studyId = StudyIdProperty::fromRequest();
        }
        if($studyId){
            return StudyTypeProperty::fromId($studyId);
        }
        $path = QMRequest::getRequestPathWithoutQuery();
        if(!$path){
            return null;
        }
        if(stripos($path, '/population') !== false){
            return self::TYPE_POPULATION;
        }
        if(stripos($path, '/user') !== false){
            return self::TYPE_INDIVIDUAL;
        }
        //if(QMRequest::getUserIdParamFromAnywhere(false)){return self::TYPE_INDIVIDUAL;}
        return QMRequest::getParam('type');
    }
    /**
     * @return bool
     */
    public static function weShouldGetPopulationStudy(): bool {
        $type = self::fromRequest(false);
        return $type === self::TYPE_POPULATION;
    }
    /**
     * @return bool
     * @throws UnauthorizedException
     */
    public static function loggedInUserHasCauseEffectData(): bool{
        if(!QMAuth::id()){
            return false;
        }
        $cause = BaseCauseVariableIdProperty::getCauseUserVariable();
        if(!$cause){
            return false;
        }
        if(!$cause->getNumberOfRawMeasurementsWithTagsJoinsChildren()){
            return false;
        }
        $effect = QMRequest::getEffectUserVariable();
        if(!$effect){
            return false;
        }
        if(!$effect->getNumberOfRawMeasurementsWithTagsJoinsChildren()){
            return false;
        }
        return true;
    }
	/**
	 * @return void
	 * @throws InvalidAttributeException
	 */
	public function validate(): void {
        parent::validate();
        $type = $this->getDBValue();
        $id = $this->getParentModel()->getId();
        $suffix = StudyTypeProperty::toIdSuffix($type);
        if($id && stripos($id, $suffix) === false){
            $this->throwException("id should contain study type $type but is $id");
        }
    }
    public static function toIdSuffix(string $type): string {
        $arr = [
            self::TYPE_INDIVIDUAL => StudyIdProperty::INDIVIDUAL_STUDY_ID_SUFFIX,
            self::TYPE_POPULATION => StudyIdProperty::POPULATION_STUDY_ID_SUFFIX,
            self::TYPE_COHORT => StudyIdProperty::COHORT_STUDY_ID_SUFFIX,
        ];
        return $arr[$type];
    }
}
