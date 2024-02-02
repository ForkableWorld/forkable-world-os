<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Study;
use App\Models\Study;
use App\Traits\PropertyTraits\StudyProperty;
use App\Properties\Base\BaseIsPublicProperty;
class StudyIsPublicProperty extends BaseIsPublicProperty
{
    use StudyProperty;
    public $table = Study::TABLE;
    public $parentClass = Study::class;
	/**
	 * @param Study $study
	 * @return bool
	 */
	public static function calculate(Study $study): bool {
		$public = false;
		if(!$study->typeIsIndividual()){
			$public = true;
		} elseif($study->getUser()->share_all_data){
			$public = true;
		}
		return $public;
	}
	public static function fixNulls(){
		Study::whereNull(self::NAME)
			->where(StudyTypeProperty::NAME, StudyTypeProperty::TYPE_POPULATION)
			->update([self::NAME => true]);
		Study::whereNull(self::NAME)
			->where(StudyTypeProperty::NAME, StudyTypeProperty::TYPE_COHORT)
			->update([self::NAME => true]);
		Study::whereNull(self::NAME)
			->where(StudyTypeProperty::NAME, StudyTypeProperty::TYPE_INDIVIDUAL)
			->update([self::NAME => false]);
	}
}
