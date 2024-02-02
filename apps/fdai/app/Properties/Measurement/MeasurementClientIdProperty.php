<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Measurement;
use App\Logging\QMLog;
use App\Models\Measurement;
use App\Traits\PropertyTraits\MeasurementProperty;
use App\Properties\Base\BaseClientIdProperty;
use App\Utils\AppMode;
class MeasurementClientIdProperty extends BaseClientIdProperty
{
    use MeasurementProperty;
    public $table = Measurement::TABLE;
    public $parentClass = Measurement::class;
	/**
	 * @param null $data
	 * @return string|null
	 */
	public static function getDefault($data = null): ?string {
		if(!AppMode::isApiRequest()){return null;}
        return BaseClientIdProperty::fromRequest(false);
    }
	/**
	 * @return void
	 * @throws \App\Exceptions\NoChangesException
	 */
	public static function fixInvalidRecords(){
        $black = (new static())->getShouldNotContain();
        foreach($black as $str){
            $measurements = Measurement::query()
                ->where(Measurement::FIELD_CLIENT_ID, "LIKE", '%'.$str.'%')
                ->get();
            QMLog::error($measurements->count()." with client id $str");
            foreach($measurements as $m){
                $m->client_id = BaseClientIdProperty::CLIENT_ID_SYSTEM;
                $m->save();
            }
        }
    }
}
