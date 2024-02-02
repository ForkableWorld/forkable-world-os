<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsFloat;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
use App\Utils\GeoLocation;
class BaseLatitudeProperty extends BaseProperty{
	use IsFloat;
	public $dbInput = 'float,10,0';
	public $dbType = 'float';
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'latitude';
	public $example = 47.6703;
	public $fieldType = 'float';
	public $fontAwesome = FontAwesome::LATITUDE;
	public $htmlType = 'text';
	public $image = ImageUrls::QUESTION_MARK;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = true;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'latitude';
	public $phpType = 'float';
	public $rules = 'nullable|numeric';
	public $title = 'Latitude';
	public $type = self::TYPE_NUMBER;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|numeric';
    /**
     * @param array $array
     * @return null
     */
    public static function getDefault($array = []){
        $value = parent::pluck($array) ?? $_SERVER['HTTP_LATITUDE'] ?? null;
        if ($value) {$_SERVER['HTTP_LATITUDE'] = $value;}
        if (!$value) {return null;}
        return GeoLocation::returnNullIfUnknown($value);
    }
    /**
     * @param $ip_address
     * @return int|string
     */
    public static function fromIP(string $ip_address){
        /** @noinspection UnserializeExploitsInspection */
        $geoplugin = unserialize(file_get_contents('http://www.geoplugin.net/php.gp?ip=' . $ip_address));
        if (is_numeric($geoplugin['geoplugin_latitude'])) {
            $latitude = $geoplugin['geoplugin_latitude'];
            return $latitude;
        }
    }
}
