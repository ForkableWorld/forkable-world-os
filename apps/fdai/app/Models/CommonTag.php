<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Base\BaseCommonTag;
use App\Traits\HasDBModel;
use App\Traits\ModelTraits\CommonTagTrait;
use App\UI\FontAwesome;
use App\Variables\QMCommonTag;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
/**
 * Class CommonTag
 * @property int $id
 * @property int $tagged_variable_id
 * @property int $tag_variable_id
 * @property int $number_of_data_points
 * @property float $standard_error
 * @property int $tag_variable_unit_id
 * @property int $tagged_variable_unit_id
 * @property float $conversion_factor
 * @property string $client_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $deleted_at
 * @property Variable $variable
 * @package App\Models
 * @property-read Variable $tag_variable
 * @property-read Variable $tagged_variable
 * @method static bool|null forceDelete()
 * @method static Builder|CommonTag newModelQuery()
 * @method static Builder|CommonTag newQuery()
 * @method static \Illuminate\Database\Query\Builder|CommonTag onlyTrashed()
 * @method static Builder|CommonTag query()
 * @method static bool|null restore()
 * @method static Builder|CommonTag whereClientId($value)
 * @method static Builder|CommonTag whereConversionFactor($value)
 * @method static Builder|CommonTag whereCreatedAt($value)
 * @method static Builder|CommonTag whereDeletedAt($value)
 * @method static Builder|CommonTag whereId($value)
 * @method static Builder|CommonTag whereNumberOfDataPoints($value)
 * @method static Builder|CommonTag whereStandardError($value)
 * @method static Builder|CommonTag whereTagVariableId($value)
 * @method static Builder|CommonTag whereTagVariableUnitId($value)
 * @method static Builder|CommonTag whereTaggedVariableId($value)
 * @method static Builder|CommonTag whereTaggedVariableUnitId($value)
 * @method static Builder|CommonTag whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|CommonTag withTrashed()
 * @method static \Illuminate\Database\Query\Builder|CommonTag withoutTrashed()
 * @mixin \Eloquent
 * @property-read OAClient|null $oa_client
 * @property-read Unit|null $tagged_variable_unit
 * @property-read Unit|null $tag_variable_unit
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @property-read OAClient|null $client
 */
class CommonTag extends BaseCommonTag {
    use HasFactory;

	use CommonTagTrait;
	public const CLASS_DESCRIPTION = "Variable tags are used to infer the user intake of the different ingredients by just entering the foods. The inferred intake levels will then be used to determine the effects of different nutrients on the user during analysis.";
	use SoftDeletes, HasDBModel;
	public const FONT_AWESOME = FontAwesome::TAGS_SOLID;
	const CLASS_CATEGORY = Variable::CLASS_CATEGORY;
	protected array $rules = [
		self::FIELD_TAGGED_VARIABLE_ID => 'required|integer|min:1|max:2147483647',
		self::FIELD_TAG_VARIABLE_ID => 'required|integer|min:1|max:2147483647',
		self::FIELD_NUMBER_OF_DATA_POINTS => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_STANDARD_ERROR => 'nullable|numeric',
		self::FIELD_TAG_VARIABLE_UNIT_ID => 'nullable|integer|min:1|max:65535',
		self::FIELD_TAGGED_VARIABLE_UNIT_ID => 'nullable|integer|min:1|max:65535',
		self::FIELD_CONVERSION_FACTOR => 'required|numeric',
		self::FIELD_CLIENT_ID => 'nullable|max:80',
	];
	protected $casts = [
		'tagged_variable_id' => 'int',
		'tag_variable_id' => 'int',
		'number_of_data_points' => 'int',
		'standard_error' => 'float',
		'tag_variable_unit_id' => 'int',
		'tagged_variable_unit_id' => 'int',
		'conversion_factor' => 'float',
	];
	public static function getSlimClass(): string{
		return QMCommonTag::class;
	}
}
