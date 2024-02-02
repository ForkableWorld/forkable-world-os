<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\Model\User;
use App\Models\UserVariable;
use App\Models\Variable;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\User\UserIdProperty;
use App\Storage\DB\Writable;
use App\VariableCategories\ITMetricsVariableCategory;
class SystemUser extends QMUser {
	public function createITMetricMeasurements(){
		$tables = Writable::getTableNames();
		foreach($tables as $table){
			$total = Writable::getBuilderByTable($table)->count();
			$v = Variable::firstOrCreate([
				Variable::FIELD_NAME => "Total $table",
			], [
				Variable::FIELD_VARIABLE_CATEGORY_ID => ITMetricsVariableCategory::ID,
				Variable::FIELD_DEFAULT_UNIT_ID => ITMetricsVariableCategory::ID,
				Variable::FIELD_CLIENT_ID => BaseClientIdProperty::CLIENT_ID_SYSTEM,
				Variable::FIELD_CREATOR_USER_ID => UserIdProperty::USER_ID_SYSTEM,
			]);
			$uv = UserVariable::firstOrCreate([
				UserVariable::FIELD_USER_ID => $this->id,
				UserVariable::VARIAB => $this->id,
			]);
		}
	}
}
