<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use App\Models\User;
use Exception;
class UserVariableNotFoundException extends Exception {
	/**
	 * @param int|string $nameOrId
	 * @param int $userId
	 */
    public function __construct($nameOrId, int $userId){
		$user = User::findInMemoryOrDB($userId);
        parent::__construct("User variable with name or id $nameOrId not found for user $user",
            QMException::CODE_NOT_FOUND);
    }
}
