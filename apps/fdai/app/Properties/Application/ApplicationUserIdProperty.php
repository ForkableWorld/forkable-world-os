<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Application;
use App\Models\Application;
use App\Properties\User\UserIdProperty;
use App\Traits\PropertyTraits\ApplicationProperty;
use App\Properties\Base\BaseUserIdProperty;
use App\Traits\HasUserFilter;
class ApplicationUserIdProperty extends BaseUserIdProperty
{
    use ApplicationProperty;
    use HasUserFilter;
    public $table = Application::TABLE;
    public $parentClass = Application::class;	
	public $default = self::DEFAULT;
	public const DEFAULT = UserIdProperty::USER_ID_MIKE;
}
