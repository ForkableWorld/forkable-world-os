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
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
/** Class BaseConnectorDevice
 * @property int $id
 * @property string $name
 * @property string $display_name
 * @property string $image
 * @property string $get_it_url
 * @property string $short_description
 * @property string $long_description
 * @property int $enabled
 * @property int $oauth
 * @property int $qm_client
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $client_id
 * @property Carbon $deleted_at
 * @property int $is_parent
 * @package App\Models\Base

 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel applyRequestParams($request)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel exclude($columns)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel excludeLargeColumns()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel nPerGroup($group, $n = 10)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseConnectorDevice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseConnectorDevice newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseConnectorDevice onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseConnectorDevice query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseConnectorDevice whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseConnectorDevice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseConnectorDevice whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseConnectorDevice whereDisplayName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseConnectorDevice whereEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseConnectorDevice whereGetItUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseConnectorDevice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseConnectorDevice whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseConnectorDevice whereIsParent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseConnectorDevice
 *     whereLongDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseConnectorDevice whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseConnectorDevice whereOauth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseConnectorDevice whereQmClient($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseConnectorDevice
 *     whereShortDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseConnectorDevice whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseConnectorDevice withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseConnectorDevice withoutTrashed()
 * @mixin \Eloquent
 * @property mixed $raw
 */
abstract class BaseConnectorDevice extends BaseModel {
	use SoftDeletes;
	public const FIELD_CLIENT_ID = 'client_id';
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_DELETED_AT = 'deleted_at';
	public const FIELD_DISPLAY_NAME = 'display_name';
	public const FIELD_ENABLED = 'enabled';
	public const FIELD_GET_IT_URL = 'get_it_url';
	public const FIELD_ID = 'id';
	public const FIELD_IMAGE = 'image';
	public const FIELD_IS_PARENT = 'is_parent';
	public const FIELD_LONG_DESCRIPTION = 'long_description';
	public const FIELD_NAME = 'name';
	public const FIELD_OAUTH = 'oauth';
	public const FIELD_QM_CLIENT = 'qm_client';
	public const FIELD_SHORT_DESCRIPTION = 'short_description';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const TABLE = false;
	protected $table = self::TABLE;
	public const CLASS_DESCRIPTION = 'Various devices whose data may be obtained from a given connector\'s API';
	public $incrementing = false;
	protected $casts = [
        self::FIELD_CREATED_AT => 'datetime',
        self::FIELD_UPDATED_AT => 'datetime',
        self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_CLIENT_ID => 'string',
		self::FIELD_DISPLAY_NAME => 'string',
		self::FIELD_ENABLED => 'int',
		self::FIELD_GET_IT_URL => 'string',
		self::FIELD_ID => 'int',
		self::FIELD_IMAGE => 'string',
		self::FIELD_IS_PARENT => 'int',
		self::FIELD_LONG_DESCRIPTION => 'string',
		self::FIELD_NAME => 'string',
		self::FIELD_OAUTH => 'int',
		self::FIELD_QM_CLIENT => 'int',
		self::FIELD_SHORT_DESCRIPTION => 'string',	];
	protected array $rules = [
		self::FIELD_CLIENT_ID => 'nullable|max:255',
		self::FIELD_DISPLAY_NAME => 'nullable|max:255',
		self::FIELD_ENABLED => 'nullable|boolean',
		self::FIELD_GET_IT_URL => 'nullable|max:2083',
		self::FIELD_IMAGE => 'nullable|max:2083',
		self::FIELD_IS_PARENT => 'nullable|boolean',
		self::FIELD_LONG_DESCRIPTION => 'nullable',
		self::FIELD_NAME => 'nullable|max:255',
		self::FIELD_OAUTH => 'nullable|boolean',
		self::FIELD_QM_CLIENT => 'nullable|boolean',
		self::FIELD_SHORT_DESCRIPTION => 'nullable|max:16777215',
	];
	protected $hints = [
		self::FIELD_ID => '',
		self::FIELD_NAME => '',
		self::FIELD_DISPLAY_NAME => '',
		self::FIELD_IMAGE => '',
		self::FIELD_GET_IT_URL => '',
		self::FIELD_SHORT_DESCRIPTION => '',
		self::FIELD_LONG_DESCRIPTION => '',
		self::FIELD_ENABLED => '',
		self::FIELD_OAUTH => '',
		self::FIELD_QM_CLIENT => '',
		self::FIELD_CREATED_AT => 'datetime',
		self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_CLIENT_ID => '',
		self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_IS_PARENT => '',
	];
	protected array $relationshipInfo = [];
}
