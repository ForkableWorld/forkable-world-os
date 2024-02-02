<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Connection;
use App\Models\Connection;
use App\Traits\PropertyTraits\ConnectionProperty;
use App\Properties\Base\BaseUserMessageProperty;
use App\Utils\SecretHelper;
class ConnectionUserMessageProperty extends BaseUserMessageProperty
{
    use ConnectionProperty;
    public $table = Connection::TABLE;
    public $parentClass = Connection::class;
	public $shouldNotContain = [
		"Try reconnecting",
	];
    public function getShouldNotContain(): array{
        $arr = parent::getShouldNotContain();
        $arr = array_merge($arr, SecretHelper::getSecretValues());
        $arr[] = "unauthorized"; // Should go in the error message, not regular message
        $arr[] = "@quantimo.do"; // Help suggestions should not be saved in DB
        return $arr;
    }
	public function validate(): void{
		$this->validateBlackListedStrings();
		parent::validate();
	}
}
