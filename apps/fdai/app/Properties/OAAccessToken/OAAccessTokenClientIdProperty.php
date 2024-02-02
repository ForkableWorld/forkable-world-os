<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\OAAccessToken;
use App\Models\OAAccessToken;
use App\Traits\PropertyTraits\OAAccessTokenProperty;
use App\Properties\Base\BaseClientIdProperty;
class OAAccessTokenClientIdProperty extends BaseClientIdProperty
{
    use OAAccessTokenProperty;
    public $table = OAAccessToken::TABLE;
    public $parentClass = OAAccessToken::class;
    public function showOnUpdate(): bool {return true;}
    public function showOnCreate(): bool {return true;}
    public function showOnIndex(): bool {return true;}
    public function showOnDetail(): bool {return true;}
}
