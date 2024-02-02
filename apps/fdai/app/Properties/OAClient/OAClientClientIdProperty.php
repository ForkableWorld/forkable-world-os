<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\OAClient;
use App\Traits\PropertyTraits\IsPrimaryKey;
use App\Models\OAClient;
use App\Traits\PropertyTraits\OAClientProperty;
use App\Properties\Base\BaseClientIdProperty;
class OAClientClientIdProperty extends BaseClientIdProperty{
	use IsPrimaryKey;
    use OAClientProperty;
    public $table = OAClient::TABLE;
    public $parentClass = OAClient::class;
    public $isPrimary = true;
    public function showOnUpdate(): bool {return false;}
    public function showOnCreate(): bool {return true;}
    public function showOnIndex(): bool {return true;}
    public function showOnDetail(): bool {return true;}
}
