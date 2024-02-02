<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Models\Variable;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Slim\Middleware\QMAuth;
class BaseCreatorUserIdProperty extends BaseUserIdProperty{
	public $description = 'The person who first created this variable.';
	public $fontAwesome = FontAwesome::OLD_USER;
	public $image = ImageUrls::OLD_USER;
	public $canBeChangedToNull = false;
    public const NAME = Variable::FIELD_CREATOR_USER_ID;
    public $name = self::NAME;
	public $title = 'Creator User';
    public function showOnUpdate(): bool {return false;}
    public function showOnCreate(): bool {return false;}
    public function showOnIndex(): bool {return false;}
    public function showOnDetail(): bool {return QMAuth::isAdmin();}
    public function shouldShowFilter(): bool{return false;}
    public const SYNONYMS = [
        self::NAME,
    ];
    /**
     * @param null $data
     * @return int
     */
    public static function getDefault($data = null): ?int{
        return null; // Avoid filtering by the creator user id in search results.
    }
}
