<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsUrl;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
use App\DataSources\Connectors\FacebookConnector;

class BaseUserUrlProperty extends BaseProperty{
	use IsUrl;
    public const SYNONYMS = [
        'userUrl',
        'url',
        'link'
    ];
	public $dbInput = 'string,2083:nullable';
	public $dbType = PhpTypes::STRING;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'URL of the user, e.g. website address.';
	public $example = 'https://plus.google.com/+MikeSinn';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::OLD_USER;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::LINK;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $maxLength = 2083;
	public $name = self::NAME;
	public const NAME = 'user_url';
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable|max:2083';
	public $title = 'User Url';
	public $type = PhpTypes::STRING;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|max:2083';
    /**
     * @param array $profile
     * @param $connectorName
     * @return string
     */
    public static function getUserUrlFromNewUserArray(array $profile, string $connectorName = null){
        if ($connectorName === FacebookConnector::NAME) {
            return "https://facebook.com/profile.php?id=" . $profile['id'];
        }
        $url = parent::pluck($profile);
        if (!$url) {
            foreach ($profile as $key => $value) {
                if (is_string($value) && stripos($value, "http") === 0) {
                    $url = $value;
                }
            }
        }
        return $url;
    }

}
