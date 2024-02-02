<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\User;
use App\Models\User;
use App\Traits\PropertyTraits\UserProperty;
use App\Properties\Base\BaseDisplayNameProperty;
class UserDisplayNameProperty extends BaseDisplayNameProperty
{
    use UserProperty;
    public $table = User::TABLE;
    public $parentClass = User::class;
    protected $shouldNotContain = [
        "\n",
        //"{", Why isn't this allowed?  If it's necessary, you need to clean up users table first
        //"}", Why isn't this allowed?  If it's necessary, you need to clean up users table first
    ];
    protected $requiredStrings = [
    ];
}
