<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Purchase;
use App\Models\Purchase;
use App\Traits\PropertyTraits\PurchaseProperty;
use App\Properties\Base\BaseClientIdProperty;
class PurchaseClientIdProperty extends BaseClientIdProperty
{
    use PurchaseProperty;
    public $table = Purchase::TABLE;
    public $parentClass = Purchase::class;
}
