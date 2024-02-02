<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Models\User;
class BaseReferrerUserIdProperty extends BaseUserIdProperty{
	public $description = 'referrer_user_id';
    public const NAME = User::FIELD_REFERRER_USER_ID;
    public $name = self::NAME;
	public $title = 'Referrer User';
	public $canBeChangedToNull = true;
}
