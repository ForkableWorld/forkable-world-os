<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Application;
use App\Models\Application;
use App\Traits\PropertyTraits\ApplicationProperty;
use App\Properties\Base\BaseStripePlanProperty;
class ApplicationStripePlanProperty extends BaseStripePlanProperty
{
    use ApplicationProperty;

    const MONTHLY_PLAN = 'monthly';
    public $table = Application::TABLE;
    public $parentClass = Application::class;
}
