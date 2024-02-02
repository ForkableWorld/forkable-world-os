<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\User;
use App\Models\User;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\Base\BaseRegProviderProperty;
use App\Traits\PropertyTraits\UserProperty;
class UserRegProviderProperty extends BaseRegProviderProperty
{
    use UserProperty;
    public $table = User::TABLE;
    public $parentClass = User::class;
	/**
	 * @param array $data
	 * @param string|null $connectorName
	 * @return string
	 */
	public static function getRegProviderFromArray(array $data, string $connectorName = null): ?string{
		$value = static::pluck($data);
		if(!empty($value)){
			return $value;
		}
		if($connectorName){
			return $connectorName;
		}
		$clientId = BaseClientIdProperty::fromRequest(false);
		if($clientId){
			return $clientId;
		}
		return null;
	}
}
