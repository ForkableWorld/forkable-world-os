<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use App\Models\Base\BaseCard;
use App\Properties\Base\BaseImageUrlProperty;
use App\Traits\HasModel\HasUser;
use App\UI\FontAwesome;
use App\UI\HtmlHelper;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
/**
 * App\Models\Card
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
 * @property string|null $html
 * @property string|null $html_content
 * @property string $id
 * @property string|null $image
 * @property string|null $input_fields
 * @property string|null $intent_name
 * @property string|null $ion_icon
 * @property string|null $link
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
 * @method static Builder|Card newModelQuery()
 * @method static Builder|Card newQuery()
 * @method static Builder|Card query()
 * @method static Builder|Card whereActionSheetButtons($value)
 * @method static Builder|Card whereAvatar($value)
 * @method static Builder|Card whereAvatarCircular($value)
 * @method static Builder|Card whereBackgroundColor($value)
 * @method static Builder|Card whereButtons($value)
 * @method static Builder|Card whereClientId($value)
 * @method static Builder|Card whereContent($value)
 * @method static Builder|Card whereCreatedAt($value)
 * @method static Builder|Card whereDeletedAt($value)
 * @method static Builder|Card whereHeaderTitle($value)
 * @method static Builder|Card whereHtml($value)
 * @method static Builder|Card whereHtmlContent($value)
 * @method static Builder|Card whereId($value)
 * @method static Builder|Card whereImage($value)
 * @method static Builder|Card whereInputFields($value)
 * @method static Builder|Card whereIntentName($value)
 * @method static Builder|Card whereIonIcon($value)
 * @method static Builder|Card whereLink($value)
 * @method static Builder|Card whereParameters($value)
 * @method static Builder|Card whereSharingBody($value)
 * @method static Builder|Card whereSharingButtons($value)
 * @method static Builder|Card whereSharingTitle($value)
 * @method static Builder|Card whereSubHeader($value)
 * @method static Builder|Card whereSubTitle($value)
 * @method static Builder|Card whereTitle($value)
 * @method static Builder|Card whereType($value)
 * @method static Builder|Card whereUpdatedAt($value)
 * @method static Builder|Card whereUserId($value)
 * @mixin Eloquent
 * @property-read OAClient $oa_client
 * @property-read User $user
 * @property string|null $url URL to go to when the card is clicked
 * @method static Builder|Card whereUrl($value)
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @property string $element_id
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @method static Builder|Card whereElementId($value)
 * @property string|null $slug The slug is the part of a URL that identifies a page in human-readable keywords.
 * @property-read OAClient $client
 * @method static Builder|Card whereSlug($value)
 */
class Card extends BaseCard {
	use HasUser;
	const CLASS_CATEGORY = BillingPlan::CLASS_CATEGORY;
	public const FONT_AWESOME = FontAwesome::ID_CARD;
	public const CLASS_DESCRIPTION = "An informational card with action buttons.";
	protected $blade = 'resources/views/components/cards/material-card.blade.php';
	protected $css = [
		'visual-link-preview.css',
	];
	/**
	 * @return string
	 */
	public function getHtmlAttribute(): string{
		$html = $this->attribute[self::FIELD_HTML] ?? null;
		if(!$html){
			try {
				$html = view('components.cards.preview-card', ['card' => $this])->render();
				HtmlHelper::validateHtml($html, $this->title . " Card");
			} catch (\Throwable $e) {
				throw new \LogicException(__METHOD__.": ".$e->getMessage());
			}
			$this->html = $html;
		}
		return $html;
	}
	/**
	 * @param $value
	 */
	public function setImageAttribute($value){
		if(!$value){
			throw new \LogicException("No image url provided!");
		}
		BaseImageUrlProperty::assertIsImageUrl($value, static::FIELD_IMAGE);
		$this->attributes[self::FIELD_IMAGE] = $value;
	}
	/**
	 * @return array|string
	 */
	public function renderMaterialHtml(): string{
		return HtmlHelper::renderView(view('components.cards.material-card', [
			'card' => $this,
		]));
	}
	public function getUserId(): ?int{
		return $this->attributes[self::FIELD_USER_ID];
	}
}
