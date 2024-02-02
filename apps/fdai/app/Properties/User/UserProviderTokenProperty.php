<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\User;
use App\Models\User;
use App\Traits\PropertyTraits\UserProperty;
use App\Properties\Base\BaseProviderTokenProperty;
use App\Slim\View\Request\QMRequest;
class UserProviderTokenProperty extends BaseProviderTokenProperty
{
    use UserProperty;
    public $table = User::TABLE;
    public $parentClass = User::class;
    /**
     * @return mixed|string|null
     */
    public static function idTokenFromRequest(): ?string {
        return QMRequest::getParam('idToken');
    }
}
