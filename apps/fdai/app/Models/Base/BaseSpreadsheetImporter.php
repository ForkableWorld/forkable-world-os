<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

/** @noinspection PhpMissingDocCommentInspection */
/** @noinspection PhpUnused */
/** @noinspection PhpFullyQualifiedNameUsageInspection */
/** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
/** Created by Reliese Model.
 */
namespace App\Models\Base;
use App\Models\BaseModel;
use App\Models\OAClient;
use App\Models\SpreadsheetImporter;
use App\Models\WpPost;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
/** Class BaseSpreadsheetImporter
 * @property int $id
 * @property string $name
 * @property string $display_name
 * @property string $image
 * @property string $get_it_url
 * @property string $short_description
 * @property string $long_description
 * @property bool $enabled
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $client_id
 * @property Carbon $deleted_at
 * @property int $wp_post_id
 * @property int $number_of_measurement_imports
 * @property int $number_of_measurements
 * @property int $sort_order
 * @property OAClient $oa_client
 * @property WpPost $wp_post
 * @package App\Models\Base

 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel applyRequestParams($request)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel exclude($columns)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel excludeLargeColumns()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel nPerGroup($group, $n = 10)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseSpreadsheetImporter newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseSpreadsheetImporter newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseSpreadsheetImporter onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseSpreadsheetImporter query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseSpreadsheetImporter whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseSpreadsheetImporter whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseSpreadsheetImporter whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseSpreadsheetImporter
 *     whereDisplayName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseSpreadsheetImporter whereEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseSpreadsheetImporter whereGetItUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseSpreadsheetImporter whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseSpreadsheetImporter whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseSpreadsheetImporter
 *     whereLongDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseSpreadsheetImporter whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseSpreadsheetImporter
 *     whereNumberOfMeasurementImports($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseSpreadsheetImporter
 *     whereNumberOfMeasurements($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseSpreadsheetImporter
 *     whereShortDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseSpreadsheetImporter whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseSpreadsheetImporter whereWpPostId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseSpreadsheetImporter withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseSpreadsheetImporter withoutTrashed()
 * @mixin \Eloquent
 * @property mixed $raw
 */
abstract class BaseSpreadsheetImporter extends BaseModel {
	use SoftDeletes;
	public const FIELD_CLIENT_ID = 'client_id';
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_DELETED_AT = 'deleted_at';
	public const FIELD_DISPLAY_NAME = 'display_name';
	public const FIELD_ENABLED = 'enabled';
	public const FIELD_GET_IT_URL = 'get_it_url';
	public const FIELD_ID = 'id';
	public const FIELD_IMAGE = 'image';
	public const FIELD_LONG_DESCRIPTION = 'long_description';
	public const FIELD_NAME = 'name';
	public const FIELD_NUMBER_OF_MEASUREMENT_IMPORTS = 'number_of_measurement_imports';
	public const FIELD_NUMBER_OF_MEASUREMENTS = 'number_of_measurements';
	public const FIELD_SHORT_DESCRIPTION = 'short_description';
	public const FIELD_SORT_ORDER = 'sort_order';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const FIELD_WP_POST_ID = 'wp_post_id';
	public const TABLE = 'spreadsheet_importers';
	protected $table = self::TABLE;
	public const CLASS_DESCRIPTION = '';
	protected $casts = [
        self::FIELD_CREATED_AT => 'datetime',
        self::FIELD_UPDATED_AT => 'datetime',
        self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_CLIENT_ID => 'string',
		self::FIELD_DISPLAY_NAME => 'string',
		self::FIELD_ENABLED => 'bool',
		self::FIELD_GET_IT_URL => 'string',
		self::FIELD_ID => 'int',
		self::FIELD_IMAGE => 'string',
		self::FIELD_LONG_DESCRIPTION => 'string',
		self::FIELD_NAME => 'string',
		self::FIELD_NUMBER_OF_MEASUREMENTS => 'int',
		self::FIELD_NUMBER_OF_MEASUREMENT_IMPORTS => 'int',
		self::FIELD_SHORT_DESCRIPTION => 'string',
		self::FIELD_SORT_ORDER => 'int',
		self::FIELD_WP_POST_ID => 'int',	];
	protected array $rules = [
		self::FIELD_CLIENT_ID => 'nullable|max:80',
		self::FIELD_DISPLAY_NAME => 'required|max:30',
		self::FIELD_ENABLED => 'required|boolean',
		self::FIELD_GET_IT_URL => 'nullable|max:2083',
		self::FIELD_IMAGE => 'required|max:2083',
		self::FIELD_LONG_DESCRIPTION => 'required',
		self::FIELD_NAME => 'required|max:30',
		self::FIELD_NUMBER_OF_MEASUREMENTS => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_MEASUREMENT_IMPORTS => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_SHORT_DESCRIPTION => 'required|max:65535',
		self::FIELD_SORT_ORDER => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_WP_POST_ID => 'nullable|numeric|min:0',
	];
	protected $hints = [
		self::FIELD_ID => 'Spreadsheet Importer ID number',
		self::FIELD_NAME => 'Lowercase system name for the data source',
		self::FIELD_DISPLAY_NAME => 'Pretty display name for the data source',
		self::FIELD_IMAGE => 'URL to the image of the Spreadsheet Importer logo',
		self::FIELD_GET_IT_URL => 'URL to a site where one can get this device or application',
		self::FIELD_SHORT_DESCRIPTION => 'Short description of the service (such as the categories it tracks)',
		self::FIELD_LONG_DESCRIPTION => 'Longer paragraph description of the data provider',
		self::FIELD_ENABLED => 'Set to 1 if the Spreadsheet Importer should be returned when listing Spreadsheet Importers',
		self::FIELD_CREATED_AT => 'datetime',
		self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_CLIENT_ID => '',
		self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_WP_POST_ID => '',
		self::FIELD_NUMBER_OF_MEASUREMENT_IMPORTS => 'Number of Spreadsheet Import Requests for this Spreadsheet Importer.
                            [Formula:
                                update spreadsheet_importers
                                    left join (
                                        select count(id) as total, spreadsheet_importer_id
                                        from spreadsheet_importer_requests
                                        group by spreadsheet_importer_id
                                    )
                                    as grouped on spreadsheet_importers.id = grouped.spreadsheet_importer_id
                                set spreadsheet_importers.number_of_spreadsheet_importer_requests = count(grouped.total)
                            ]
                            ',
		self::FIELD_NUMBER_OF_MEASUREMENTS => 'Number of Measurements for this Spreadsheet Importer.
                                [Formula: update spreadsheet_importers
                                    left join (
                                        select count(id) as total, spreadsheet_importer_id
                                        from measurements
                                        group by spreadsheet_importer_id
                                    )
                                    as grouped on spreadsheet_importers.id = grouped.spreadsheet_importer_id
                                set spreadsheet_importers.number_of_measurements = count(grouped.total)]',
		self::FIELD_SORT_ORDER => '',
	];
	protected array $relationshipInfo = [
		'oa_client' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => OAClient::class,
			'foreignKeyColumnName' => 'client_id',
			'foreignKey' => SpreadsheetImporter::FIELD_CLIENT_ID,
			'otherKeyColumnName' => 'client_id',
			'otherKey' => OAClient::FIELD_CLIENT_ID,
			'ownerKeyColumnName' => 'client_id',
			'ownerKey' => SpreadsheetImporter::FIELD_CLIENT_ID,
			'methodName' => 'oa_client',
		],
		'wp_post' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => WpPost::class,
			'foreignKeyColumnName' => 'wp_post_id',
			'foreignKey' => SpreadsheetImporter::FIELD_WP_POST_ID,
			'otherKeyColumnName' => 'ID',
			'otherKey' => WpPost::FIELD_ID,
			'ownerKeyColumnName' => 'wp_post_id',
			'ownerKey' => SpreadsheetImporter::FIELD_WP_POST_ID,
			'methodName' => 'wp_post',
		],
	];
	public function oa_client(): BelongsTo{
		return $this->belongsTo(OAClient::class, SpreadsheetImporter::FIELD_CLIENT_ID, OAClient::FIELD_CLIENT_ID,
			SpreadsheetImporter::FIELD_CLIENT_ID);
	}
	public function wp_post(): BelongsTo{
		return $this->belongsTo(WpPost::class, SpreadsheetImporter::FIELD_WP_POST_ID, WpPost::FIELD_ID,
			SpreadsheetImporter::FIELD_WP_POST_ID);
	}
}
