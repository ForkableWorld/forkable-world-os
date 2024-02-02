<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use App\Exceptions\AccessTokenExpiredException;
use App\Slim\Middleware\QMAuth;
use App\Traits\HasJsonFile;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Buttons\RelationshipButtons\RelationshipButton;
use App\Buttons\RelationshipButtons\UnitCategory\UnitCategoryUnitsButton;
use App\Models\Base\BaseUnitCategory;
use App\Slim\Model\DBModel;
use App\Slim\Model\QMUnit;
use App\Slim\Model\QMUnitCategory;
use App\Traits\HasDBModel;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
/**
 * App\Models\UnitCategory
 * @SWG\Definition (
 *      definition="UnitCategory",
 *      required={""},
 *      @SWG\Property(
 *          property="id",
 *          description="id",
 *          type="boolean"
 *      ),
 *      @SWG\Property(
 *          property="name",
 *          description="Unit category name",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="created_at",
 *          description="created_at",
 *          type="string",
 *          format="date-time"
 *      ),
 *      @SWG\Property(
 *          property="updated_at",
 *          description="updated_at",
 *          type="string",
 *          format="date-time"
 *      ),
 *      @SWG\Property(
 *          property="can_be_summed",
 *          description="can_be_summed",
 *          type="boolean"
 *      ),
 *      @SWG\Property(
 *          property="deleted_at",
 *          description="deleted_at",
 *          type="string",
 *          format="date-time"
 *      )
 * )
 * @property int $id
 * @property string $name Unit category name
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property bool $can_be_summed
 * @property Carbon|null $deleted_at

 * @property-read Collection|Unit[] $units
 * @property-read int|null $units_count
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static Builder|UnitCategory newModelQuery()
 * @method static Builder|UnitCategory newQuery()
 * @method static \Illuminate\Database\Query\Builder|UnitCategory onlyTrashed()
 * @method static Builder|UnitCategory query()
 * @method static Builder|UnitCategory whereCanBeSummed($value)
 * @method static Builder|UnitCategory whereCreatedAt($value)
 * @method static Builder|UnitCategory whereDeletedAt($value)
 * @method static Builder|UnitCategory whereId($value)
 * @method static Builder|UnitCategory whereName($value)
 * @method static Builder|UnitCategory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|UnitCategory withTrashed()
 * @method static \Illuminate\Database\Query\Builder|UnitCategory withoutTrashed()
 * @mixin \Eloquent
 * @property mixed $raw
 * @property int $sort_order
 * @method static Builder|UnitCategory whereSortOrder($value)
 * @property-read OAClient $client
 * @property-read OAClient $oa_client
 */
class UnitCategory extends BaseUnitCategory {
    use HasFactory;
    use HasJsonFile;
	use SoftDeletes, HasDBModel;
	public static function getSlimClass(): string{ return QMUnitCategory::class; }
	public $table = self::TABLE;
	public const CLASS_DESCRIPTION = 'Category for the unit of measurement such as weight, rating, distance, or volume.';
	public const DEFAULT_IMAGE = ImageUrls::FITNESS_MEASURING_TAPE;
	public const FONT_AWESOME = FontAwesome::RULER_COMBINED_SOLID;
	public const DEFAULT_ORDERINGS = [self::FIELD_NAME => self::ORDER_DIRECTION_ASC];
	public const DEFAULT_LIMIT = 200;
	public function getSubtitleAttribute(): string{
		if(!$this->hasId()){
			return static::getClassDescription();
		}
		return $this->getDBModel()->getSubtitleAttribute();
	}
	public function getFontAwesome(): string{
		if(!$this->hasId()){
			return static::FONT_AWESOME;
		}
		return $this->getDBModel()->getFontAwesome();
	}
	public function getImage(): string{
		if(!$this->hasId()){
			return static::DEFAULT_IMAGE;
		}
		return $this->getDBModel()->getImage();
	}
	/**
	 * @return DBModel|QMUnitCategory
	 */
	public function getDBModel(): DBModel{
		$name = $this->name;
		return QMUnitCategory::getByName($name);
	}
	public function units(): HasMany{
		return $this->hasMany(Unit::class, Unit::FIELD_UNIT_CATEGORY_ID, Unit::FIELD_ID);
	}
	/**
	 * Determine if this resource is available for navigation.
	 * @param Request $request
	 * @return bool
	 */
	public static function availableForNavigation(Request $request): bool{ return true; }
	public function getTitleAttribute():string{
		return $this->getNameAttribute();
	}
	/**
	 * @param int|string $nameOrId
	 * @return static|null
	 * @noinspection PhpParameterNameChangedDuringInheritanceInspection
	 */
	public static function findInMemory($nameOrId): ?BaseModel{
		if(!$nameOrId){le("no nameOrId");}
		$mem = parent::findInMemory($nameOrId);
		if($mem){return $mem;}
		$mem = QMUnitCategory::find($nameOrId);
		return $mem->attachedOrNewLaravelModel();
	}
	/**
	 * @return RelationshipButton[]
	 */
	public function getInterestingRelationshipButtons(): array{
		$buttons = [];
		$buttons[] = new UnitCategoryUnitsButton($this, $this->units());
		return $buttons;
	}
	/**
	 * @return \Illuminate\Support\Collection
	 */
	public function getQMUnits(): \Illuminate\Support\Collection{
		return collect(QMUnit::all())->filter(function(QMUnit $unit){
			return $unit->getUnitCategoryId() === $this->getId();
		});
	}
    public function getUnits(){
        $this->loadMissing('units');
        return $this->units;
    }
    /**
     * @param null $writer
     * @return bool
     * @throws AccessTokenExpiredException
     */
    public function canCreateMe($writer = null): bool{
        return QMAuth::isAdmin();
    }
}
