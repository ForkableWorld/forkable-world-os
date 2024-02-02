<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsString;
use App\Exceptions\NoGeoDataException;
use App\Logging\QMLog;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
use App\Utils\GeoLocation;
use Exception;
use InvalidArgumentException;
use OpenApi\Generator;
class BaseZipCodeProperty extends BaseProperty{
	use IsString;
	public const SYNONYMS = ['zip', 'zipcode', 'postal_code', 'postal'];
    public const ZIP_CODE_FORMATS = [
        "US" => "^\d{5}([\-]?\d{4})?$",
        "UK" => "^(GIR|[A-Z]\d[A-Z\d]??|[A-Z]{2}\d[A-Z\d]??)[ ]??(\d[A-Z]{2})$",
        "DE" => "\b((?:0[1-46-9]\d{3})|(?:[1-357-9]\d{4})|(?:[4][0-24-9]\d{3})|(?:[6][013-9]\d{3}))\b",
        "CA" => "^([ABCEGHJKLMNPRSTVXY]\d[ABCEGHJKLMNPRSTVWXYZ])\ {0,1}(\d[ABCEGHJKLMNPRSTVWXYZ]\d)$",
        "FR" => "^(F-)?((2[A|B])|[0-9]{2})[0-9]{3}$",
        "IT" => "^(V-|I-)?[0-9]{5}$",
        "AU" => "^(0[289][0-9]{2})|([1345689][0-9]{3})|(2[0-8][0-9]{2})|(290[0-9])|(291[0-4])|(7[0-4][0-9]{2})|(7[8-9][0-9]{2})$",
        "NL" => "^[1-9][0-9]{3}\s?([a-zA-Z]{2})?$",
        "ES" => "^([1-9]{2}|[0-9][1-9]|[1-9][0-9])[0-9]{3}$",
        "DK" => "^([D-d][K-k])?( |-)?[1-9]{1}[0-9]{3}$",
        "SE" => "^(s-|S-){0,1}[0-9]{3}\s?[0-9]{2}$",
        "BE" => "^[1-9]{1}[0-9]{3}$"
    ];
    public $dbInput = 'string,255:nullable';
	public $dbType = PhpTypes::STRING;
	public $default = Generator::UNDEFINED;
	public $description = 'zip_code';
	public $example = '62025';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::ZIP_CODE;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::ZIP_CODE;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $maxLength = 255;
	public $name = self::NAME;
	public const NAME = 'zip_code';
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable|max:255';
	public $title = 'Zip Code';
	public $type = PhpTypes::STRING;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|max:255';
	/**
	 * @param string $ip_address
	 * @return string
	 */
    public static function fromIP(string $ip_address): ?string{
        try {
            $geoLocation = GeoLocation::ipData($ip_address);
            $zip = $geoLocation->zip;
            return $zip;
        } catch (NoGeoDataException $e) {
            QMLog::info("Could not getZipCodeFromIp because " . $e->getMessage());
            return null;
        }
    }
    /**
     * @param string $zip
     * @return object|false
     */
    public static function getLatitudeAndLongitudeFromZipCode(string $zip){
        if(isset(GeoLocation::$latLongFromZip[$zip])){
            return GeoLocation::$latLongFromZip[$zip];
        }
        try {
            $result = GeoLocation::googleMapsRequest(['address' => $zip]);
        } catch (NoGeoDataException $e) {
            QMLog::error(__METHOD__.": ".$e->getMessage());
            return GeoLocation::$latLongFromZip[$zip] = false;
        }
        return GeoLocation::$latLongFromZip[$zip] = $result->geometry->location;
    }
    /**
     * @param string $zip
     * @return bool
     */
    public static function validUSZip(string $zip): bool{
        return self::zipCodeValidForCountry('US', $zip);
    }
	/**
	 * @param $latitude
	 * @param $longitude
	 * @return bool
	 * @throws \App\Exceptions\NoGeoDataException
	 */
    public static function getZipCodeFromLatitudeAndLongitude($latitude, $longitude): bool{
        $result = GeoLocation::googleMapsRequest(['latlng' => $latitude.','.$longitude]);
        if(!empty($result)){
            $addressComponents = $result->address_components;
            foreach($addressComponents as $addressComponent){
                if($addressComponent->types[0] === 'postal_code'){
                    //Return the zipcode
                    return $addressComponent->long_name;
                }
            }
            return false;
        }
        return false;
    }
    /**
     * @param string $country_code
     * @param string $zip_postal
     * @return bool
     */
    public static function zipCodeValidForCountry(string $country_code, string $zip_postal): bool{
        if(self::ZIP_CODE_FORMATS[$country_code]){
            if(!preg_match("/".self::ZIP_CODE_FORMATS[$country_code]."/i", $zip_postal)){
                //Validation failed, provided zip/postal code is not valid.
                return false;
            }
            //Validation passed, provided zip/postal code is valid.
            return true;
        }
        throw new InvalidArgumentException("$country_code not supported!");
    }
}
