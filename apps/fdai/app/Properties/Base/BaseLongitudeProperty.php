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
class BaseLongitudeProperty extends BaseProperty{
	use IsFloat;
	public $dbInput = 'float,10,0';
	public $dbType = 'float';
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'Longitude at which the measurement was taken';
	public $example = -2.9918;
	public $fieldType = 'float';
	public $fontAwesome = FontAwesome::QUESTION_CIRCLE;
	public $htmlType = 'text';
	public $image = ImageUrls::QUESTION_MARK;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = true;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'longitude';
	public $phpType = 'float';
	public $rules = 'nullable|numeric';
	public $title = 'Longitude';
	public $type = self::TYPE_NUMBER;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|numeric';
    /**
     * @param $ip_address
     * @return int|string
     */
    public static function fromIP(string $ip_address){
        /** @noinspection UnserializeExploitsInspection */
        $geoPlugin = unserialize(file_get_contents('http://www.geoplugin.net/php.gp?ip=' . $ip_address));
        if (is_numeric($geoPlugin['geoplugin_longitude'])) {
            $longitude = $geoPlugin['geoplugin_longitude'];
            return $longitude;
        }
        return null;
    }
    /**
     * @param null $data
     * @return null
     */
    public static function getDefault($data = null){
        $value = parent::pluck($data) ?? $_SERVER['HTTP_LONGITUDE'] ?? null;
        if ($value) {$_SERVER['HTTP_LONGITUDE'] = $value;}
        if (!$value) {return null;}
        return GeoLocation::returnNullIfUnknown($value);
    }
}
