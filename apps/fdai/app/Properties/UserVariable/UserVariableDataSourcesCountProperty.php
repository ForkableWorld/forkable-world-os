<?php /** @noinspection SpellCheckingInspection *//*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariable;
use App\Models\UserVariable;
use App\Models\UserVariableClient;
use App\Traits\PropertyTraits\IsCalculated;
use App\Traits\PropertyTraits\UserVariableProperty;
use App\Properties\Base\BaseDataSourcesCountProperty;
use App\Variables\QMUserVariable;
class UserVariableDataSourcesCountProperty extends BaseDataSourcesCountProperty
{
    use UserVariableProperty;
    use IsCalculated;
    public $table = UserVariable::TABLE;
    public $parentClass = UserVariable::class;
	/**
	 * @return void
	 * @throws \App\Exceptions\InvalidAttributeException
	 * @throws \App\Exceptions\ModelValidationException
	 */
	public function validate(): void {
        if(!$this->shouldValidate()){return;}
        parent::validate();
        $val = $this->getAccessorValue();
        $uv = $this->getUserVariable();
        $num = $uv->number_of_measurements;
        if($num && empty($val)){
            $measurements = $uv->getQMMeasurementsWithTags();
            if($measurements){
                $this->throwException("should not be empty because there are $num raw measurements");
            }
        }
    }
    /**
     * @throws \App\Exceptions\ModelValidationException
     */
    public function beforeChange(bool $log = true): void {
        $uv = $this->getUserVariable();
        $cv = $uv->getVariable();
        if(!$cv->data_sources_count){
            $cv->data_sources_count = $this->getAccessorValue();
            $cv->save();
        }
    }
    public function cannotBeChangedToNull(): bool{
        $parent = $this->getParentModel();
        if(!$parent->id){return false;}
        $uv = $this->getUserVariable();
        $num = $uv->number_of_measurements;
        return (bool) $num;
    }
    /**
     * @param UserVariable $uv
     * @return array
     */
    public static function calculate($uv): array{
        if($uv instanceof QMUserVariable){$uv = $uv->l();}
	    $QMUserVariable = $uv->getQMUserVariable();
	    $measurements = $QMUserVariable->getMeasurementsWithTags();
		if($measurements){
			UserVariableClient::updateByUserVariable($uv);
		}
	    $uvcs = $uv->getUserVariableClients();
	    if(!$uvcs->count()){
			$measurementsWithoutTags = $uv->getMeasurements();
			if($measurementsWithoutTags){
				le("No user variable clients for $uv->name even though there are measurements");
			}
			return [];
		}
        $dsc = [];
        if($measurements){
            /** @var UserVariableClient $uvc */
            foreach($uvcs as $uvc){
                $name = ($ds = $uvc->getQMDataSource()) ? $ds->getTitleAttribute() : $uvc->client_id;
                $dsc[$name] = $uvc->number_of_measurements;
            }
        }
        $uv->setAttribute(static::NAME, $dsc);
        return $dsc;
    }
}
