<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\User;
use App\Models\User;
use App\Astral\Filters\PeopleFilter;
use App\Traits\PropertyTraits\UserProperty;
use App\Properties\Base\BaseReferrerUserIdProperty;
class UserReferrerUserIdProperty extends BaseReferrerUserIdProperty
{
    use UserProperty;
    public $table = User::TABLE;
    public $parentClass = User::class;
    /**
     * Set the default options for the filter.
     * @return string
     */
    public function defaultFilter(): string{return PeopleFilter::EVERYONE;}
    public function showOnIndex(): bool{return false;}
    public function showOnDetail(): bool{return false;}
    public function showOnCreate(): bool{return false;}
    public function showOnUpdate(): bool{return false;}
}
