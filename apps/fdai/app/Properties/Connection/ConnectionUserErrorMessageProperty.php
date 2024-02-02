<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Connection;
use App\Models\Connection;
use App\Traits\PropertyTraits\ConnectionProperty;
use App\Properties\Base\BaseUserErrorMessageProperty;
use App\Utils\SecretHelper;
class ConnectionUserErrorMessageProperty extends BaseUserErrorMessageProperty
{
    use ConnectionProperty;
    public $table = Connection::TABLE;
    public $parentClass = Connection::class;
    public function validate(): void {
        parent::validate();
        $val = $this->getDBValue();
        $connector = $this->getConnection();
        if(!empty($val)){
            if($connector->update_status !== ConnectionUpdateStatusProperty::IMPORT_STATUS_ERROR &&
            $connector->connect_status !== ConnectionConnectStatusProperty::CONNECT_STATUS_ERROR){
                $this->throwException("Should not have user error message if connection is not errored");
            }
        }
    }
    public function getShouldNotContain(): array{
        $arr = parent::getShouldNotContain();
        $arr = array_merge($arr, SecretHelper::getSecretValues());
        $arr[] = "unauthorized"; // Should go in the error message, not regular message
        $arr[] = "@quantimo.do"; // Help suggestions should not be saved in DB
        return $arr;
    }
}
