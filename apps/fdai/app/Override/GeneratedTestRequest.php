<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Override;
use App\Properties\Base\BaseClientIdProperty;
use App\Slim\View\Request\QMRequest;
use Illuminate\Http\Request;
class GeneratedTestRequest extends Request
{
    /**
     * @param array $array
     * @return static
     */
    public static function __set_state(array $array) : self
    {
        $object = new self;
        foreach ($array as $key => $value) {
            $object->{$key} = $value;
        }
        return $object;
    }
    /**
     * @return string
     */
    public static function getClientId(): ?string {
        return BaseClientIdProperty::fromRequest();
    }
    public static function getReferrer(): ?string{
        return QMRequest::getReferrer();
    }
}
