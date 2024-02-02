<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models\Cards;
use App\Models\BaseModel;
use App\Models\Card;
use App\Models\OAClient;
use App\Models\User;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
/**
 * App\Models\Cards\PreviewCard
 * @property string|null $action_sheet_buttons
 * @property string|null $avatar
 * @property string|null $avatar_circular
 * @property string|null $background_color
 * @property string|null $buttons
 * @property string $client_id
 * @property string|null $content
 * @property Carbon $created_at
 * @property Carbon|null $deleted_at
 * @property string|null $header_title
 * @property string $html
 * @property string|null $html_content
 * @property string $id
 * @property string|null $image
 * @property string|null $input_fields
 * @property string|null $intent_name
 * @property string|null $ion_icon
 * @property string|null $link Link field is deprecated due to ambiguity.  Please use url field instead.
 * @property string|null $parameters
 * @property string|null $sharing_body
 * @property string|null $sharing_buttons
 * @property string|null $sharing_title
 * @property string|null $sub_header
 * @property string|null $sub_title
 * @property string|null $title
 * @property string $type
 * @property Carbon $updated_at
 * @property int $user_id
 * @property string|null $url URL to go to when the card is clicked
 * @method static Builder|PreviewCard newModelQuery()
 * @method static Builder|PreviewCard newQuery()
 * @method static Builder|PreviewCard query()
 * @method static Builder|PreviewCard whereActionSheetButtons($value)
 * @method static Builder|PreviewCard whereAvatar($value)
 * @method static Builder|PreviewCard whereAvatarCircular($value)
 * @method static Builder|PreviewCard whereBackgroundColor($value)
 * @method static Builder|PreviewCard whereButtons($value)
 * @method static Builder|PreviewCard whereClientId($value)
 * @method static Builder|PreviewCard whereContent($value)
 * @method static Builder|PreviewCard whereCreatedAt($value)
 * @method static Builder|PreviewCard whereDeletedAt($value)
 * @method static Builder|PreviewCard whereHeaderTitle($value)
 * @method static Builder|PreviewCard whereHtml($value)
 * @method static Builder|PreviewCard whereHtmlContent($value)
 * @method static Builder|PreviewCard whereId($value)
 * @method static Builder|PreviewCard whereImage($value)
 * @method static Builder|PreviewCard whereInputFields($value)
 * @method static Builder|PreviewCard whereIntentName($value)
 * @method static Builder|PreviewCard whereIonIcon($value)
 * @method static Builder|PreviewCard whereLink($value)
 * @method static Builder|PreviewCard whereParameters($value)
 * @method static Builder|PreviewCard whereSharingBody($value)
 * @method static Builder|PreviewCard whereSharingButtons($value)
 * @method static Builder|PreviewCard whereSharingTitle($value)
 * @method static Builder|PreviewCard whereSubHeader($value)
 * @method static Builder|PreviewCard whereSubTitle($value)
 * @method static Builder|PreviewCard whereTitle($value)
 * @method static Builder|PreviewCard whereType($value)
 * @method static Builder|PreviewCard whereUpdatedAt($value)
 * @method static Builder|PreviewCard whereUrl($value)
 * @method static Builder|PreviewCard whereUserId($value)
 * @mixin Eloquent
 * @property-read OAClient $oa_client
 * @property-read User $user
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @property string $element_id
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @method static Builder|PreviewCard whereElementId($value)
 * @property string|null $slug The slug is the part of a URL that identifies a page in human-readable keywords.
 * @property-read OAClient $client
 * @method static Builder|PreviewCard whereSlug($value)
 */
class PreviewCard extends Card {
	const CSS = 'https://static.quantimo.do/css/visual-link-preview.css';
	const CARD_BLADE = 'components.lists.preview-card';
	protected $blade = self::CARD_BLADE;
	protected $css = [
		self::CSS,
	];
}
