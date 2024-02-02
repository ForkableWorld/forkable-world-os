<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Astral;
use App\Models\User;
use App\Models\Variable;
use App\Models\WpPost;
use App\Properties\WpPost\WpPostIdProperty;
use App\Storage\S3\S3Public;
use Illuminate\Http\Request;
use App\Fields\Avatar;
use App\Fields\Text;
use Titasgailius\SearchRelations\SearchesRelations;
class WpPostBaseAstralResource extends BaseAstralAstralResource {
	use SearchesRelations;
	/**
	 * The model the resource corresponds to.
	 * @var string
	 */
	public static $model = WpPost::class;
	/**
	 * The single value that should be used to represent the resource when being displayed.
	 * @var string
	 */
	public static $title = WpPost::FIELD_POST_TITLE;
	/**
	 * The columns that should be searched.
	 * @var array
	 */
	public static $search = [];
	/**
	 * The number of results to display in the global search.
	 * @var int
	 */
	public static $globalSearchResults = 10;
	public static $searchRelations = [
		'user' => [User::FIELD_DISPLAY_NAME],
	];
	/**
	 * The per-page options used the resource index.
	 * @var array
	 */
	public static $perPageOptions = [10, 25, 50, 100];
	/**
	 * Get the fields displayed by the resource.
	 * @param Request $request
	 * @return array
	 */
	public function fields(Request $request): array{
		return [
			Avatar::make(str_repeat(' ', 8), function(){
				/** @var WpPost $this */
				return $this->getImage();
			})->disk(S3Public::DISK_NAME)->path('images/' . Variable::TABLE)->maxWidth(50)->disableDownload()->squared()
				->thumbnail(function(){
					/** @var Variable $this */
					return $this->getImage();
				})->preview(function(){
					/** @var Variable $this */
					return $this->getImage();
				}),
			Text::make('Name', Variable::FIELD_NAME, function(){
				/** @var Variable $this */
				return $this->getTitleAttribute();
			})->sortable()->readonly()->detailLink()->rules('required'),
			// Breaks view user page UnitResource::belongsTo('Unit', 'default_unit'),
			// Breaks view user page VariableCategoryResource::belongsTo('Category'),
			UserBaseAstralResource::belongsTo(),
			WpPostIdProperty::field(null, null),
		];
	}
	/**
	 * Get the displayable label of the resource.
	 * @return string
	 */
	public static function label(): string{
		return "Posts";
	}
	/**
	 * Determine if this resource is available for navigation.
	 * @param Request $request
	 * @return bool
	 */
	public static function availableForNavigation(Request $request): bool{
		return false;
	}
}
