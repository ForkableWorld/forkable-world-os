<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Models\User;
use App\Storage\S3\S3Public;
use App\Traits\IsImageUrl;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
use App\Fields\Avatar;
use App\Fields\Field;
use OpenApi\Generator;
class BaseAvatarImageProperty extends BaseProperty{
    use IsImageUrl;
    public const SYNONYMS = [
        'image',
        'imageUrl',
        'avatar',
        'picture_url',
        'avatar_url',
        'picture'
    ];
	public $dbInput = 'string,2083:nullable';
	public $dbType = PhpTypes::STRING;
	public $default = Generator::UNDEFINED;
	public $description = 'avatar_image';
	public $example = 'https://studies.quantimo.do/images/crowdsourcing-utopia-icon-1024-1024.png';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::AVATAR_IMAGE;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::AVATAR_IMAGE;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $maxLength = 2083;
	public $name = self::NAME;
	public const NAME = 'avatar_image';
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable|max:2083';
	public $title = 'Avatar Image';
	public $type = PhpTypes::STRING;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|max:2083';
    /**
     * @param array $data
     * @return string
     */
    public static function pluck($data): ?string{
        $avatar = parent::pluck($data);
        if (is_array($avatar) && isset($avatar["data"]["url"])) {
            $avatar = $avatar["data"]["url"];
        }
        if (is_array($avatar) && isset($avatar["url"])) {
            $avatar = $avatar["url"];
        }
        return $avatar;
    }
    public function getField($resolveCallback = null, string $name = null): Field{
        return Avatar::make("Avatar", User::FIELD_AVATAR_IMAGE)
            ->disk(S3Public::DISK_NAME)
            ->path('avatars')
            ->maxWidth(50)
            ->disableDownload()
            ->hideFromIndex()
            ->thumbnail(function () {
                return $this->getDBValue();
            })
            ->preview(function () {
                return $this->getDBValue();
            });
    }
}
