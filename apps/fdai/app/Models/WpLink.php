<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use App\Menus\Routes\RoutesMenu;
use App\Models\Base\BaseWpLink;
use App\Slim\Model\States\IonicState;
use App\UI\FontAwesome;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
/**
 * App\Models\WpLink
 * @property int $link_id
 * @property string $link_url
 * @property string $link_name
 * @property string $link_image
 * @property string $link_target
 * @property string $link_description
 * @property string $link_visible
 * @property int $link_owner
 * @property int $link_rating
 * @property Carbon|null $link_updated
 * @property string $link_rel
 * @property string $link_notes
 * @property string $link_rss
 * @property Carbon $updated_at
 * @property Carbon $created_at
 * @property Carbon|null $deleted_at
 * @property string|null $client_id
 * @method static Builder|WpLink newModelQuery()
 * @method static Builder|WpLink newQuery()
 * @method static Builder|WpLink query()
 * @method static Builder|WpLink whereClientId($value)
 * @method static Builder|WpLink whereCreatedAt($value)
 * @method static Builder|WpLink whereDeletedAt($value)
 * @method static Builder|WpLink whereLinkDescription($value)
 * @method static Builder|WpLink whereLinkId($value)
 * @method static Builder|WpLink whereLinkImage($value)
 * @method static Builder|WpLink whereLinkName($value)
 * @method static Builder|WpLink whereLinkNotes($value)
 * @method static Builder|WpLink whereLinkOwner($value)
 * @method static Builder|WpLink whereLinkRating($value)
 * @method static Builder|WpLink whereLinkRel($value)
 * @method static Builder|WpLink whereLinkRss($value)
 * @method static Builder|WpLink whereLinkTarget($value)
 * @method static Builder|WpLink whereLinkUpdated($value)
 * @method static Builder|WpLink whereLinkUrl($value)
 * @method static Builder|WpLink whereLinkVisible($value)
 * @method static Builder|WpLink whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read User|null $user
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @property-read OAClient|null $client
 * @property-read OAClient|null $oa_client
 */
class WpLink extends BaseWpLink {
	public const CLASS_DESCRIPTION = 'During the rise of popularity of blogging having a blogroll (links to other sites) on your site was very much in fashion. This table holds all those links for you.';
	public const FIELD_ID = self::FIELD_LINK_ID;
	public const FONT_AWESOME = FontAwesome::LINK_SOLID;
	protected array $rules = [
		self::FIELD_LINK_ID => 'required|numeric|min:1', //|unique:wp_links,link_id', // Unique checks too slow
		self::FIELD_LINK_URL => 'nullable|max:255',
		self::FIELD_LINK_NAME => 'nullable|max:255',
		self::FIELD_LINK_IMAGE => 'nullable|max:255',
		self::FIELD_LINK_TARGET => 'nullable|max:25',
		self::FIELD_LINK_DESCRIPTION => 'nullable|max:255',
		self::FIELD_LINK_VISIBLE => 'nullable|max:20',
		self::FIELD_LINK_OWNER => 'nullable|numeric|min:0',
		self::FIELD_LINK_RATING => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_LINK_UPDATED => 'required|date',
		self::FIELD_LINK_REL => 'nullable|max:255',
		self::FIELD_LINK_NOTES => 'nullable|max:16777215',
		self::FIELD_LINK_RSS => 'required|max:255',
		self::FIELD_CLIENT_ID => 'nullable|max:80',
	];

	/**
	 * @return WpLink[]
	 */
	public static function generateLinksFromRoutes(): array{
		$m = new RoutesMenu();
		return $m->getWpLinks();
	}
	public static function generateLinksFromIonicStates(){
		$routes = IonicState::getStates();
		foreach($routes as $route){
			$route->getWpLink();
		}
	}
	/**
	 * @return WpLink[]|Collection
	 */
	public static function generateLinks(){
		self::generateLinksFromIonicStates();
		self::generateLinksFromRoutes();
		return self::all();
	}
}
