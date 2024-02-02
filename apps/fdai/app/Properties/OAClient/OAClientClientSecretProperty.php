<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\OAClient;
use App\Models\OAClient;
use App\Properties\Base\BaseClientSecretProperty;
use App\Slim\Model\User\QMUser;
use App\Traits\PropertyTraits\OAClientProperty;
class OAClientClientSecretProperty extends BaseClientSecretProperty
{
    use OAClientProperty;
    public $table = OAClient::TABLE;
    public $parentClass = OAClient::class;
    public static function generate():string{
        return QMUser::generateRandomString(32);
    }
}
