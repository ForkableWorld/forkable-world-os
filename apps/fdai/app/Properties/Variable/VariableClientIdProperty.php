<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Variable;
use App\Models\CtCause;
use App\Models\CtCondition;
use App\Models\CtSideEffect;
use App\Models\CtSymptom;
use App\Models\CtTreatment;
use App\Models\OAClient;
use App\Models\Variable;
use App\Properties\User\UserIdProperty;
use App\Traits\PropertyTraits\VariableProperty;
use App\Properties\Base\BaseClientIdProperty;
use App\Types\QMArr;
use App\DataSources\QMConnector;
use App\Utils\AppMode;
class VariableClientIdProperty extends BaseClientIdProperty {
	use VariableProperty;
	public $table = Variable::TABLE;
	public $parentClass = Variable::class;
	/**
	 * @param $providedParams
	 * @param $newVariable
	 * @return array
	 */
	public static function setClientIdInNewVariableArray(array $providedParams, array $newVariable): array{
		$clientId = QMArr::getValue($providedParams, [self::NAME]);
		if(empty($clientId) && AppMode::isApiRequest()){
			$clientId = BaseClientIdProperty::fromRequest(false);
		}
		if(empty($clientId) && QMConnector::getCurrentlyImportingConnector()){
			$clientId = QMConnector::getCurrentlyImportingConnector()->getConnectionIfExists()->getClientId();
			if(!$clientId){
				$clientId = BaseClientIdProperty::fromMemory();
			}
		}
		$newVariable[self::NAME] = $clientId;
		return $newVariable;
	}
	public function cannotBeChangedToNull(): bool{ return true; }
	public function showOnDetail(): bool{ return true; }
	public static function updateAll(){
		self::updateCTClientIds(CtCause::TABLE);
		self::updateCTClientIds(CtCondition::TABLE);
		self::updateCTClientIds(CtSideEffect::TABLE);
		self::updateCTClientIds(CtSymptom::TABLE);
		self::updateCTClientIds(CtTreatment::TABLE);
	}
	private static function updateCTClientIds(string $table){
		$c = OAClient::firstOrCreate([
			OAClient::FIELD_CLIENT_ID => BaseClientIdProperty::CLIENT_ID_CURE_TOGETHER,
			OAClient::FIELD_USER_ID => UserIdProperty::USER_ID_MIKE,
		]);
		db_statement("
            update variables v 
                inner join $table cc on v.id = cc.variable_id 
            set v.client_id = '$c->client_id' 
            where v.client_id = 'unknown' or v.client_id is null or v.client_id = ''
        ");
	}
}
