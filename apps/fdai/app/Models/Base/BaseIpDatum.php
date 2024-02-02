<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

/** @noinspection PhpMissingDocCommentInspection */
/** @noinspection PhpUnused */
/** @noinspection PhpFullyQualifiedNameUsageInspection */
/** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\BaseModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class BaseIpDatum
 *
 * @property int $id
 * @property Carbon $created_at
 * @property Carbon $deleted_at
 * @property Carbon $updated_at
 * @property string $ip
 * @property string $hostname
 * @property string $type
 * @property string $continent_code
 * @property string $continent_name
 * @property string $country_code
 * @property string $country_name
 * @property string $region_code
 * @property string $region_name
 * @property string $city
 * @property string $zip
 * @property float $latitude
 * @property float $longitude
 * @property string $location
 * @property string $time_zone
 * @property string $currency
 * @property string $connection
 * @property string $security
 * @package App\Models\Base
 * @property-read \App\Models\OAClient $client
 * @property mixed|null $calculated
 * @property-read array $invalid_record_for
 * @property-read string $name
 * @property mixed|null $raw
 * @property-read string $report_title
 * @property-read array|mixed|string|string[]|null $rule_for
 * @property-read array $rules_for
 * @property-read string $subtitle
 * @property-read string $title
 * @property-read \App\Models\OAClient $oa_client
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @method static Builder|BaseModel nPerGroup($group, int $n = 10)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseIpDatum newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseIpDatum newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseIpDatum onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseIpDatum query()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseIpDatum whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseIpDatum whereConnection($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseIpDatum whereContinentCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseIpDatum whereContinentName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseIpDatum whereCountryCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseIpDatum whereCountryName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseIpDatum whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseIpDatum whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseIpDatum whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseIpDatum whereHostname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseIpDatum whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseIpDatum whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseIpDatum whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseIpDatum whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseIpDatum whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseIpDatum whereRegionCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseIpDatum whereRegionName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseIpDatum whereSecurity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseIpDatum whereTimeZone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseIpDatum whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseIpDatum whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseIpDatum whereZip($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseIpDatum withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseIpDatum withoutTrashed()
 * @mixin \Eloquent
 */
class BaseIpDatum extends BaseModel
{
	use SoftDeletes;
	public const FIELD_CITY = 'city';
	public const FIELD_CONNECTION = 'connection';
	public const FIELD_CONTINENT_CODE = 'continent_code';
	public const FIELD_CONTINENT_NAME = 'continent_name';
	public const FIELD_COUNTRY_CODE = 'country_code';
	public const FIELD_COUNTRY_NAME = 'country_name';
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_CURRENCY = 'currency';
	public const FIELD_DELETED_AT = 'deleted_at';
	public const FIELD_HOSTNAME = 'hostname';
	public const FIELD_ID = 'id';
	public const FIELD_IP = 'ip';
	public const FIELD_LATITUDE = 'latitude';
	public const FIELD_LOCATION = 'location';
	public const FIELD_LONGITUDE = 'longitude';
	public const FIELD_REGION_CODE = 'region_code';
	public const FIELD_REGION_NAME = 'region_name';
	public const FIELD_SECURITY = 'security';
	public const FIELD_TIME_ZONE = 'time_zone';
	public const FIELD_TYPE = 'type';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const FIELD_ZIP = 'zip';
	//protected $connection = 'tddd';
	public const TABLE = 'ip_data';
	protected $table = self::TABLE;
	public const CLASS_DESCRIPTION = '';

	protected $casts = [
        self::FIELD_CREATED_AT => 'datetime',
        self::FIELD_DELETED_AT => 'datetime',
        self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_CITY => 'string',
		self::FIELD_CONNECTION => 'string',
		self::FIELD_CONTINENT_CODE => 'string',
		self::FIELD_CONTINENT_NAME => 'string',
		self::FIELD_COUNTRY_CODE => 'string',
		self::FIELD_COUNTRY_NAME => 'string',
		self::FIELD_CURRENCY => 'string',
		self::FIELD_HOSTNAME => 'string',
		self::FIELD_ID => 'int',
		self::FIELD_IP => 'string',
		self::FIELD_LATITUDE => 'float',
		self::FIELD_LOCATION => 'string',
		self::FIELD_LONGITUDE => 'float',
		self::FIELD_REGION_CODE => 'string',
		self::FIELD_REGION_NAME => 'string',
		self::FIELD_SECURITY => 'string',
		self::FIELD_TIME_ZONE => 'string',
		self::FIELD_TYPE => 'string',
		self::FIELD_ZIP => 'string'
	];

	protected array $rules = [
		self::FIELD_CITY => 'required|max:255',
		//self::FIELD_CONNECTION => 'required',
		self::FIELD_CONTINENT_CODE => 'required|max:255',
		self::FIELD_CONTINENT_NAME => 'required|max:255',
		self::FIELD_COUNTRY_CODE => 'required|max:255',
		self::FIELD_COUNTRY_NAME => 'required|max:255',
		self::FIELD_CURRENCY => 'required',
		self::FIELD_HOSTNAME => 'required|max:255',
		self::FIELD_IP => 'required|max:255',
		self::FIELD_LATITUDE => 'required|numeric',
		self::FIELD_LOCATION => 'required',
		self::FIELD_LONGITUDE => 'required|numeric',
		self::FIELD_REGION_CODE => 'required|max:255',
		self::FIELD_REGION_NAME => 'required|max:255',
		self::FIELD_SECURITY => 'required',
		self::FIELD_TIME_ZONE => 'required',
		self::FIELD_TYPE => 'required|max:255',
		self::FIELD_ZIP => 'required|max:255'
	];
	protected $hints = [
		self::FIELD_ID => 'Automatically generated unique id for the ip data',
		self::FIELD_CREATED_AT => 'The time the record was originally created',
		self::FIELD_DELETED_AT => 'The time the record was deleted',
		self::FIELD_UPDATED_AT => 'The time the record was last modified',
		self::FIELD_IP => 'Example: 134.201.250.155',
		self::FIELD_HOSTNAME => 'Example: 134.201.250.155',
		self::FIELD_TYPE => 'Example: ipv4',
		self::FIELD_CONTINENT_CODE => 'Example: NA',
		self::FIELD_CONTINENT_NAME => 'Example: North America',
		self::FIELD_COUNTRY_CODE => 'Example: US',
		self::FIELD_COUNTRY_NAME => 'Example: United States',
		self::FIELD_REGION_CODE => 'Example: CA',
		self::FIELD_REGION_NAME => 'Example: California',
		self::FIELD_CITY => 'Example: Los Angeles',
		self::FIELD_ZIP => 'Example: 90013',
		self::FIELD_LATITUDE => 'Example: 34.0453',
		self::FIELD_LONGITUDE => 'Example: -118.2413',
		self::FIELD_LOCATION => 'Example: {geoname_id:5368361,capital:Washington D.C.,languages:[{code:en,name:English,native:English}],country_flag:https://assets.ipstack.com/images/assets/flags_svg/us.svg,country_flag_emoji:ud83cuddfaud83cuddf8,country_flag_emoji_unicode:U+1F1FA U+1F1F8,calling_code:1,is_eu:false}',
		self::FIELD_TIME_ZONE => 'Example: {id:America/Los_Angeles,current_time:2018-03-29T07:35:08-07:00,gmt_offset:-25200,code:PDT,is_daylight_saving:true}',
		self::FIELD_CURRENCY => 'Example: {code:USD,name:US Dollar,plural:US dollars,symbol:$,symbol_native:$}',
		self::FIELD_CONNECTION => 'Example: {asn:25876,isp:Los Angeles Department of Water & Power}',
		self::FIELD_SECURITY => 'Example: {is_proxy:false,proxy_type:null,is_crawler:false,crawler_name:null,crawler_type:null,is_tor:false,threat_level:low,threat_types:null}'
	];

	protected array $relationshipInfo = [

	];
}
