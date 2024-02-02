<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;

use App\Exceptions\NoGeoDataException;
use App\Models\Base\BaseIpDatum;
use App\Properties\Base\BaseCountryProperty;
use App\Types\QMStr;
use Illuminate\Database\Eloquent\Builder;
/**
 * \App\Models\IpDatum
 * @property int $id Automatically generated unique id for the ip data
 * @property \Illuminate\Support\Carbon $created_at The time the record was originally created
 * @property \Illuminate\Support\Carbon|null $deleted_at The time the record was deleted
 * @property \Illuminate\Support\Carbon $updated_at The time the record was last modified
 * @property string $ip Example: 134.201.250.155
 * @property string $hostname Example: 134.201.250.155
 * @property string $type Example: ipv4
 * @property string $continent_code Example: NA
 * @property string $continent_name Example: North America
 * @property string $country_code Example: US
 * @property string $country_name Example: United States
 * @property string $region_code Example: CA
 * @property string $region_name Example: California
 * @property string $city Example: Los Angeles
 * @property string $zip Example: 90013
 * @property float $latitude Example: 34.0453
 * @property float $longitude Example: -118.2413
 * @property array $location Example: {geoname_id:5368361,capital:Washington D.C.,languages:[{code:en,name:English,native:English}],country_flag:https://assets.ipstack.com/images/assets/flags_svg/us.svg,country_flag_emoji:ud83cuddfaud83cuddf8,country_flag_emoji_unicode:U+1F1FA U+1F1F8,calling_code:1,is_eu:false}
 * @property array $time_zone Example: {id:America/Los_Angeles,current_time:2018-03-29T07:35:08-07:00,gmt_offset:-25200,
 * code:PDT,is_daylight_saving:true}
 * @property array $currency Example: {code:USD,name:US Dollar,plural:US dollars,symbol:$,symbol_native:$}
 * @property array $connection Example: {asn:25876,isp:Los Angeles Department of Water & Power}
 * @property array $security Example: {is_proxy:false,proxy_type:null,is_crawler:false,crawler_name:null,crawler_type:null,is_tor:false,threat_level:low,threat_types:null}
 * @property-read \App\Models\OAClient $client
 * @property-read string $name
 * @property mixed|null $raw
 * @property-read string $report_title
 * @property-read array|mixed|string|string[]|null $rule_for
 * @property-read array $rules_for
 * @property-read string $subtitle
 * @property-read string $title
 * @property-read \App\Models\OAClient $oa_client
 * @property string $time_zone_name
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static Builder|IpDatum newModelQuery()
 * @method static Builder|IpDatum newQuery()
 * @method static Builder|IpDatum query()
 * @method static Builder|IpDatum whereCity($value)
 * @method static Builder|IpDatum whereConnection($value)
 * @method static Builder|IpDatum whereContinentCode($value)
 * @method static Builder|IpDatum whereContinentName($value)
 * @method static Builder|IpDatum whereCountryCode($value)
 * @method static Builder|IpDatum whereCountryName($value)
 * @method static Builder|IpDatum whereCreatedAt($value)
 * @method static Builder|IpDatum whereCurrency($value)
 * @method static Builder|IpDatum whereDeletedAt($value)
 * @method static Builder|IpDatum whereHostname($value)
 * @method static Builder|IpDatum whereId($value)
 * @method static Builder|IpDatum whereIp($value)
 * @method static Builder|IpDatum whereLatitude($value)
 * @method static Builder|IpDatum whereLocation($value)
 * @method static Builder|IpDatum whereLongitude($value)
 * @method static Builder|IpDatum whereRegionCode($value)
 * @method static Builder|IpDatum whereRegionName($value)
 * @method static Builder|IpDatum whereSecurity($value)
 * @method static Builder|IpDatum whereTimeZone($value)
 * @method static Builder|IpDatum whereType($value)
 * @method static Builder|IpDatum whereUpdatedAt($value)
 * @method static Builder|IpDatum whereZip($value)
 * @mixin \Eloquent
 */
class IpDatum extends BaseIpDatum
{
    public function __construct(array $attributes = [])
    {
        foreach ($attributes as $key => $value) {
            $snake = QMStr::snakize($key);
            unset($attributes[$key]);
            $attributes[$snake] = $value;
        }
        parent::__construct($attributes);
    }

	protected array $rules = [
		self::FIELD_CITY => 'required|max:255',
		//self::FIELD_CONNECTION => 'required',
		self::FIELD_CONTINENT_CODE => 'required|max:255',
		self::FIELD_CONTINENT_NAME => 'max:255',
		self::FIELD_COUNTRY_CODE => 'required|max:255',
		self::FIELD_COUNTRY_NAME => 'required|max:255',
		//self::FIELD_CURRENCY => 'required',
		self::FIELD_HOSTNAME => 'max:255',
		self::FIELD_IP => 'required|max:255',
		self::FIELD_LATITUDE => 'required|numeric',
		//self::FIELD_LOCATION => 'required|numeric',
		self::FIELD_LONGITUDE => 'required|numeric',
		self::FIELD_REGION_CODE => 'max:255',
		self::FIELD_REGION_NAME => 'max:255',
		//self::FIELD_SECURITY => 'required',
		//self::FIELD_TIME_ZONE => 'required',
		self::FIELD_TYPE => 'max:255',
		self::FIELD_ZIP => 'required|max:255'
	];
	protected $casts = [
		self::FIELD_CITY => 'string',
		self::FIELD_CONNECTION => 'array',
		self::FIELD_CONTINENT_CODE => 'string',
		self::FIELD_CONTINENT_NAME => 'string',
		self::FIELD_COUNTRY_CODE => 'string',
		self::FIELD_COUNTRY_NAME => 'string',
		self::FIELD_CURRENCY => 'array',
		self::FIELD_HOSTNAME => 'string',
		self::FIELD_ID => 'int',
		self::FIELD_IP => 'string',
		self::FIELD_LATITUDE => 'float',
		self::FIELD_LOCATION => 'array',
		self::FIELD_LONGITUDE => 'float',
		self::FIELD_REGION_CODE => 'string',
		self::FIELD_REGION_NAME => 'string',
		self::FIELD_SECURITY => 'array',
		self::FIELD_TIME_ZONE => 'array',
		self::FIELD_TYPE => 'string',
		self::FIELD_ZIP => 'string'
	];
	/**
	 * @return string
	 */
	public function getTimeZoneNameAttribute(): ?string{
		$tz = $this->time_zone;
		return $tz['name'] ?? $tz['id'];
	}
	protected function fillableFromArray(array $attributes): array{
		$attributes = parent::fillableFromArray($attributes);
		$aliases = [
			'country_code2' => self::FIELD_COUNTRY_CODE,
			'zipcode' => self::FIELD_ZIP,
		];
		foreach($aliases as $alias => $column){
			if(isset($attributes[$alias]) && !isset($attributes[$column])){
				$attributes[$column] = $attributes[$alias];
				unset($attributes[$alias]);
			}
		}
		foreach($attributes as $column => $value){
			if(is_object($value)){
				$attributes[$column] = (array) $value;
			}
		}
		foreach($attributes as $column => $value){
			if(!self::hasColumn($column)){
				$attributes[self::FIELD_LOCATION][$column] = $value;
				unset($attributes[$column]);
			}
		}
		return $attributes;
	}

    /**
     * @return string|null
     * @throws NoGeoDataException
     */
    public function getCountryName(): ?string
    {
        $name = $this->country_name;
        if(!$name){
            $location = $this->location;
            if(isset($location['country_name'])){
                $name = $location['country_name'];
            }
            throw new NoGeoDataException("No country name for IP: $this->ip!  IP Data: ".$this->print());
        }
        return BaseCountryProperty::getCountryNameFromString($name);
    }
    public function save(array $options = []): bool
    {
        $location = $this->location;
        if($location){
            $location = QMStr::decodeIfJson($location);
            foreach ($location as $key => $value) {
                $snake = QMStr::snakize($key);
                if($value && static::hasColumn($snake)){
                    $this->setAttribute($snake, $value);
                    unset($location->$key);
                }
            }
            $this->location = $location;
        }
	    $timeZone = $this->attributes[self::FIELD_TIME_ZONE] ?? null;
	    if(is_object($timeZone)){
            $this->time_zone = $timeZone->name;
        }
        try {
            return parent::save($options);
        } catch (\Throwable $e) {
            return parent::save($options);
            //le($e);
        }
    }
}
