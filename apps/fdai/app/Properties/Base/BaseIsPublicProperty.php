<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsBoolean;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
use App\Types\BoolHelper;
use App\Slim\Middleware\QMAuth;
use OpenApi\Generator;
class BaseIsPublicProperty extends BaseProperty{
	use IsBoolean;
	public $dbInput = 'boolean:nullable';
	public $dbType = self::TYPE_BOOLEAN;
	public $default = Generator::UNDEFINED;
	public $description = 'If is_public is set to true, the related data will be publicly accessible.';
	public $fieldType = self::TYPE_BOOLEAN;
	public $fontAwesome = FontAwesome::GALACTIC_REPUBLIC;
	public $htmlInput = 'checkbox,1';
	public $htmlType = 'checkbox';
	public $image = ImageUrls::BUSINESS_COLLECTION_TIME_IS_MONEY;
	public $isOrderable = false;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'is_public';
	public $canBeChangedToNull = true;
	public $phpType = PhpTypes::BOOL;
	public $showOnDetail = true;
	public $title = 'Is Public';
	public $type = self::TYPE_BOOLEAN;
    public const SYNONYMS = [
        'is_public',
        'share_user_measurements',
    ];
    /**
     * Set the default options for the filter.
     *
     * @return string
     */
    public function defaultFilter(): string{return BoolHelper::TRUE_STRING;}
    public function showOnIndex(): bool{return QMAuth::isAdmin();}
    public function showOnDetail(): bool{return QMAuth::isAdmin();}
    public function showOnCreate(): bool{return QMAuth::isAdmin();}
    public function showOnUpdate(): bool{return QMAuth::isAdmin();}

}
