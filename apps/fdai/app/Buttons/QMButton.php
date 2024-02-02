<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons;
use App\Charts\BarChartButton;
use App\Exceptions\MissingPropertyException;
use App\Files\FileHelper;
use App\Logging\ConsoleLog;
use App\Logging\QMLog;
use App\Models\BaseModel;
use App\Models\Button;
use App\Models\WpLink;
use App\Slim\Model\DBModel;
use App\Slim\Model\Slack\SlackAttachment;
use App\Slim\View\Request\QMRequest;
use App\Traits\CompressibleTrait;
use App\Traits\HardCodable;
use App\Traits\HasButton;
use App\Traits\HasTestUrl;
use App\Types\ObjectHelper;
use App\Types\QMArr;
use App\Types\QMStr;
use App\UI\CssHelper;
use App\UI\FontAwesome;
use App\UI\HtmlHelper;
use App\UI\ImageHelper;
use App\UI\ImageUrls;
use App\UI\InternalImageUrls;
use App\UI\IonIcon;
use App\UI\Markdown;
use App\UI\QMColor;
use App\Utils\Env;
use App\Utils\N8N;
use App\Utils\UrlHelper;
use Collective\Html\HtmlFacade;
use Dialogflow\Action\Questions\ListCard\Option;
use DigitalCreative\CollapsibleResourceManager\Resources\AbstractResource;
use DigitalCreative\CollapsibleResourceManager\Resources\ExternalLink;
use Illuminate\Support\Collection;
use ReflectionClass;
use Share;
use Spatie\Menu\Link;
use Symfony\Component\Yaml\Yaml;

class QMButton extends DBModel {
	use CompressibleTrait, HardCodable, HasTestUrl;
	const TABLE = Button::TABLE;
	const BUTTONS_FOLDER = 'app/Buttons';
	const TARGET_BLANK = '_blank';
	const TARGET_SELF = '_self';
	const LARAVEL_CLASS = Button::class;
	public const UNIQUE_INDEX_COLUMNS = [Button::FIELD_SLUG];
	protected $markdownBadgeLogo;
	protected $menus = [];
	protected $secondaryImage;
	protected $spatieLink;
	protected $classes = [];
	protected $styles = "";
	protected $elementId;
	protected $keywords;
	public $accessibilityText;
	public $action; // Needed for Web push action buttons
	public $additionalInformation;
	public $badgeText;
	public $backgroundColor;
	public $color;
	public $confirmationText;
	public $fontAwesome;
	public $functionName;
	public $html;
	public $icon;
	public $id;
	public $image;
	public $ionIcon;
	public $link;
	public $onClick;
	public $parameters = [];
	public $subtitle;
	public $successAlertBody;
	public $successAlertTitle;
	public $successToastText;
	public $target;
	public $text;
	public $textColor;
	public $title; // Needed for Android action buttons
	public $tooltip;
	public $type;
	public $userId;
	public $visible;
	public $webhookUrl;
	public $slug;
	public const ACTION = null;
	/**
	 * @param string|null $text
	 * @param string|null $url
	 * @param string|null $backgroundColor
	 * @param string|null $ionIcon
	 * @param null $additionalInformation
	 */
	public function __construct(string $text = null, string $url = null, string $backgroundColor = null,
		string $ionIcon = null, $additionalInformation = null){
		if($text){
			$this->text = $this->title = $text;
		}
		if($ionIcon){
			$this->ionIcon = $ionIcon;
		}
		if($url){
			$this->setUrl($url);
		}
		if($backgroundColor){
			$this->backgroundColor = $this->color = $backgroundColor;
		}
		if($additionalInformation){
			$this->additionalInformation = $additionalInformation;
		}
		if($this->text === null){
			$this->text = $this->title;
		}
		if(!$this->ionIcon && $this->icon){
			$this->ionIcon = $this->icon;
		}
//		if($this->text !== null){
//			$this->setTextIconAndImageHtml($this->text, $this->ionIcon);
//		}
		// Do this as needed, so we don't take up too much memory, and so we do it after the child class has had a chance to set the text
		if(!$this->accessibilityText){
			$this->accessibilityText = $this->text;
		}
		if(!$this->tooltip){
			$this->tooltip = $this->text ?: $this->title;
		}
		if(!isset($this->title)){
			$this->title = $this->text;
		}
		$this->getAction();
	}
	public function getSlug(): string{
		if($this->slug){
			return $this->slug;
		}
		return $this->slug = QMStr::slugify($this->getUrl());
	}
	/**
	 * @param string $folder
	 * @return QMButton[]
	 */
	public static function getButtonsInFolder(string $folder): array{
		$classes = FileHelper::getClassesInFolder(self::BUTTONS_FOLDER . '/' . $folder);
		$buttons = [];
		foreach($classes as $class){
			/** @noinspection PhpUnhandledExceptionInspection */
			if((new ReflectionClass($class))->isAbstract()){
				continue;
			}
			try {
				$b = new $class();
				$buttons[] = $b;
			} catch (\Throwable $e) {
				ConsoleLog::debug("Could not create button because: " . $e->getMessage());
			}
		}
		return $buttons;
	}
	/**
	 * @param array $links
	 * @return LinkButton[]
	 */
	public static function linksToButtons(array $links): array{
		$buttons = [];
		foreach($links as $text => $url){
			$buttons[] = new LinkButton($text, $url);
		}
		return $buttons;
	}
	/**
	 * @param HasButton[]|Collection $objects
	 * @return QMButton[]
	 */
	public static function toButtons($objects): array{
		$buttons = [];
		foreach($objects as $object){
			$buttons[] = $object->getButton();
		}
		return $buttons;
	}
	/**
	 * @return string
	 */
	public function getRectangleWPButton(): string{
		$text = $this->getTitleAttribute();
		$elementId = $this->getElementId();
		$classes = $this->getClassesString();
		$this->setClasses(["wp-block-button__link"]);
		$attr = $this->getAnchorAttributesString();
		return "
            <div $elementId class=\"wp-block-button $classes\"
                style=\"$this->styles\">
                <a $attr>
                    $text
                </a>
            </div>
        ";
	}
	/**
	 * @param string $textLeft
	 * @param string $url
	 * @param string|null $image
	 * @param string $backgroundColor
	 * @param string|null $tooltip
	 * @return string
	 */
	public static function generateRoundedTableRowHtml(string $textLeft, string $url, string $image = null,
		string $backgroundColor = 'black', string $tooltip = null): string{
		$tooltip = $tooltip ?: $textLeft;
		return BarChartButton::generateHtml($textLeft, $url, $image, $tooltip, $backgroundColor);
	}
	public function getTailwindLink(): string{
		return HtmlHelper::getTailwindLink($this->getUrl(), $this->getTitleAttribute());
	}
	/**
	 * @param null|string $ionIcon
	 */
	public function setIonIcon(string $ionIcon){
		$this->ionIcon = $ionIcon;
		if(!$this->image && $ionIcon){
			$this->setIonIconAndImage($ionIcon);
		}
	}
	/**
	 * @param string $text
	 * @return static
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public function setTextAndTitle(string $text){
		$this->text = $text;
		if(!empty($text)){
			$this->title = $text;
		}
		if($this->subtitle === $this->title){
			$this->subtitle = null;
		}
		$this->setHtml();
		return $this;
	}
	/**
	 * @return Option
	 */
	public function getOption(): Option{
		$option = new Option();
		$option->title($this->getTitleAttribute());
		//$option->key($this->getId());
		$option->key(QMStr::slugify($this->getTitleAttribute()));
		$option->image($this->image);
		$option->description($this->additionalInformation);
		return $option;
	}
	/**
	 * @param string $additionalInformation
	 */
	public function setAdditionalInformationAndTooltip(string $additionalInformation){
		$this->tooltip = $this->additionalInformation = $additionalInformation;
	}
	/**
	 * @param string $color
	 */
	public function setBackgroundColor(string $color){
		$this->backgroundColor = $this->color = $color;
		$this->setHtml();
	}
	/**
	 * @param string $action
	 */
	public function setAction(string $action){
		$this->action = $action;
	}
	/**
	 * @return string
	 */
	public function getTooltip(): ?string{
		if(!$this->tooltip){
			return null;
		}
		return str_replace('"', '', $this->tooltip);
	}
	/**
	 * @return array
	 */
	protected function getKeywords(): array{
		$t = $this->getTitleAttribute();
		$this->keywords[$t] = $t;
		return $this->keywords;
	}
	/**
	 * @return string
	 */
	public function getKeywordString(): string{
		return implode(", ", $this->getKeywords());
	}
	/**
	 * @param string $tooltip
	 * @return QMButton
	 */
	public function setTooltip(string $tooltip): self{
		$this->tooltip = $tooltip;
		return $this;
	}
	/**
	 * @param string $functionName
	 */
	public function setFunctionName(string $functionName){
		$this->functionName = $functionName;
	}
	/**
	 * @param array $parameters
	 */
	public function setParameters(array $parameters){
		$this->parameters = $parameters;
	}
	/**
	 * @param string $confirmationText
	 */
	public function setConfirmationText(string $confirmationText){
		$this->confirmationText = $confirmationText;
	}
	/**
	 * @param string $successToastText
	 */
	public function setSuccessToastText(string $successToastText){
		$this->successToastText = $successToastText;
	}
	/**
	 * @param string $successAlertTitle
	 */
	public function setSuccessAlertTitle(string $successAlertTitle){
		$this->successAlertTitle = $successAlertTitle;
	}
	/**
	 * @param string $successAlertBody
	 */
	public function setSuccessAlertBody(string $successAlertBody){
		$this->successAlertBody = $successAlertBody;
	}
	/**
	 * @param string $ionIcon
	 */
	public function setIonIconAndImage(string $ionIcon){
		$this->icon = $ionIcon;
		//$this->image = IonIcon::getIonIconSvgUrl($ionIcon); // SVG's won't work in email
		if(!$this->image){
			$this->setImage(IonIcon::getIonIconPngUrl($ionIcon));
		}
	}
	/**
	 * @param string $id
	 * @return string
	 */
	public function setId($id): string{
		if(is_object($id)){
			le("id is an object!");
		}
		$id = strtolower(str_replace(' ', '-', $id));
		if(strpos($id, 'button') === false){
			$id = $id . '-button';
		}
		$this->id = $id;
		$this->setHtml();
		return $id;
	}
	/**
	 * @param string $image
	 * @return string
	 */
	public function setImageHtml(string $image): string{
		$html = '<md-tooltip>' . $this->getTooltip() . '</md-tooltip>';
		$html .= '<img class="md-user-avatar" style="height: 100%;" ng-src="' . $image . '"/>';
		return $this->html = $html;
	}
	/**
	 * @param string $url
	 * @param array $params
	 * @param bool $allowPaths
	 * @return static
	 * @noinspection PhpUnhandledExceptionInspection
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public function setUrl(string $url, array $params = [], bool $allowPaths = false): self{
		$url = UrlHelper::addParams($url, $params);
		if(!$this->action){
			$this->action = $url;
		}
		$type = $this->text ?? $this->title ?? static::class;
		//QMValidatingTrait::assertStringDoesNotContain($url, ['predictors/:', 'outcomes/:'], $type);
		if(!$allowPaths){
			QMStr::assertIsUrl($url, $type);
		}
		$this->link = $url;
		return $this;
	}
	/**
	 * @return string
	 */
	public function getId(): ?string{
		if(!$this->id){
			$this->id = QMStr::slugify($this->title ?? $this->text ?? static::class);
		}
		return $this->id;
	}
	/**
	 * @param string $image
	 * @param string|null $accessibilityText
	 * @return QMButton
	 */
	public function setImage(string $image, string $accessibilityText = null): QMButton{
		$this->image = $image;
		if($accessibilityText){
			$this->accessibilityText = $accessibilityText;
		}
		return $this;
	}
	/**
	 * @return string
	 */
	public function getTitleAttribute(): string{
		if(!$this->title){
			return $this->title = $this->text;
		}
		return $this->title;
	}
	/**
	 * @return string
	 */
	public function getImage(): string{
		if(!$this->image){
			if(stripos($this->title, "population") !== false){
				return $this->image === InternalImageUrls::BETTER_WORLD_THROUGH_DATA_PEOPLE;
			}
			if(stripos($this->title, "edit") !== false){
				return $this->image === ImageUrls::ESSENTIAL_COLLECTION_EDIT;
			}
			if(stripos($this->title, "effect") !== false){
				return $this->image === ImageUrls::SCIENCE_ATOM;
			}
			if(stripos($this->title, "cause") !== false){
				return $this->image === ImageUrls::SCIENCE_ATOM;
			}
			if($this->ionIcon){
				$ionIconPngUrl = IonIcon::getIonIconPngUrl($this->ionIcon);
				if($ionIconPngUrl){
					return $this->image = $ionIconPngUrl;
				}
			}
			$e = new MissingPropertyException("Missing Image on " . $this->title . " Button!",
				['Search for an Image' => ImageHelper::SEARCH_IMAGES_URL]);
			throw $e;
		}
		return $this->image;
	}
	/**
	 * @param string|null $name
	 * @return mixed
	 */
	public function getParameters(string $name = null): array{
		if($name){
			return $this->parameters[$name] ?? [];
		}
		return $this->parameters;
	}
	/**
	 * @param string $name
	 * @return mixed
	 */
	public function getParameter(string $name){
		return $this->parameters[$name] ?? null;
	}
	/**
	 * @param string $webhookUrl
	 * @return string
	 */
	public function setWebhookUrl(string $webhookUrl): string{
		return $this->webhookUrl = $webhookUrl;
	}
	/**
	 * @return null
	 */
	public function getAction(): ?string{
		$action = $this->action;
		if(empty($action)){
			if(static::ACTION){
				$action = static::ACTION;
			} elseif($this->link){
				$action = $this->getUrl();
			}
		}
		return $this->action = $action;
	}
	/**
	 * @param array $params
	 * @return string
	 */
	public function getUrl(array $params = []): string{
		if($this->parameters){$params = array_merge($this->parameters, $params);}
		$url = $this->link ?? $this->webhookUrl;
		if(!$url){le("no url on $this", $this);}
		if(str_starts_with($url, "/")){$url = Env::getAppUrl().$url;}
		$params = ObjectHelper::unsetNullAndEmptyArrayOrStringProperties($params);
		$url = UrlHelper::addParams($url, $params);
		return $this->link = $url;
	}
	/**
	 * @param string $key
	 * @param $value
	 */
	public function addParameter(string $key, $value){
		$this->parameters[$key] = $value;
	}
	/**
	 * @return string
	 */
	public function getLogMetaDataString(): string{
		return $this->title ?: $this->text ?: $this->accessibilityText ?: $this->action;
	}
	/**
	 * @param string $textLeft
	 * @param $textRight
	 * @return string
	 */
	public function getBarChartTableRowHtmlForEmail(string $textLeft, $textRight): string{
		$bar = new BarChartButton();
		$bar->setUrl($this->getUrl());
		$bar->setBackgroundColor($this->getBackgroundColor());
		$bar->setToolTip($this->getTooltip());
		$bar->setImage($this->getImage());
		$bar->setTextLeft($textLeft);
		$bar->setTextRight($textRight);
		return $bar->getTableHtml();
	}
	/**
	 * @return string|null
	 */
	public function getLink(): string{
		$icon = $this->getFontAwesomeHtml();
		$attr = $this->getAnchorAttributesString();
		return "
            <a $attr>
                $icon $this->badgeText $this->title
            </a>
        ";
	}
	/**
	 * @return string|null
	 */
	public function getTextLink(): string{
		$attr = $this->getAnchorAttributesString();
		return "
            <a $attr>
                $this->title
            </a>
        ";
	}
	/**
	 * @return string
	 */
	public function getBackgroundColor(): string{
		return $this->color ?? QMColor::HEX_DARK_GRAY;
	}
	/**
	 * @return static
	 */
	public static function instance($params = null): QMButton{
		if($params){
			return new static($params);
		} else{
			return new static();
		}
	}
	/**
	 * @param string|null $backgroundColor
	 * @param string|null $textColor
	 * @return string
	 */
	public function getRoundedHtml(string $backgroundColor = null, string $textColor = null): string{
		if($backgroundColor){
			$this->setBackgroundColor($backgroundColor);
		}
		if($textColor){
			$this->setTextColor($textColor);
		}
		return self::generateRoundedTableRowHtml($this->getTitleAttribute(), $this->getUrl(), $this->getImage(),
			$this->getBackgroundColor(), $this->getTooltip());
	}
	/**
	 * @return string
	 */
	public function getTextColor(): string{
		if(!empty($this->textColor)){
			return $this->textColor;
		}
		$backgroundColor = $this->getBackgroundColor();
		if($backgroundColor === "white"){
			return "black";
		}
		return "white";
	}
	/**
	 * @param mixed $textColor
	 */
	public function setTextColor(string $textColor): void{
		$this->textColor = $textColor;
	}
	/**
	 * @return string
	 */
	private function setHtml(): string{
		$text = $this->title;
		$ionIconName = $this->ionIcon;
		$image = $this->image;
		$style = $this->styles;
		if($this->color){
			$style .= "color: $this->color;";
		}
		if($style){$style = "style=\"$style\"";}
		$html = '<span id="' . $this->getId() . '" ' . $style . '>';
		if($image){
			$html .= '<img class="md-user-avatar" style="width: 100%;" src="'.$image.'"/>';
		} elseif($ionIconName){
			$html .= '<i class="icon ion-' . $ionIconName . '"></i>';
		}
		$html .= $text . '</span>';
		return $this->html = $html;
	}
	public function setFontAwesome(?string $string): QMButton{
		$this->fontAwesome = $string;
		return $this;
	}
	/**
	 * @return string
	 */
	public function getFontAwesome(): string{
		if(!$this->fontAwesome){
			le("No fontAwesome for $this " . static::class);
		}
		return $this->fontAwesome;
	}
	public function getBadgeListItem(): string{
		try {
			$url = $this->getUrl();
		} catch (\Throwable $e) {
			$url = Links\HelpButton::url();
		}
		return HtmlHelper::generateBadgeListItemHtml($this->badgeText, $this->getTitleAttribute(), $url, $this->getTooltip(),
			$this->getBackgroundColor());
	}
	public function getStatBox(): string{
		return HtmlHelper::generateStatBoxHtml($this->badgeText, $this->getTitleAttribute(), $this->getTooltip(),
			$this->getBackgroundColor(), $this->getFontAwesome(), $this->getUrl());
	}
	public function getMaterialStatCard(): string{
		if($this->badgeText !== null){
			$numberOrTitle = $this->badgeText;
			$categoryOrSubtitle = $this->getTitleAttribute();
		} else{
			$numberOrTitle = $this->getTitleAttribute();
			$categoryOrSubtitle = $this->subtitle;
		}
		$tt = $this->getTooltip();
		if(!$tt){
			le("no tooltip on $this", $this);
		}
		return HtmlHelper::generateMaterialStatCard($numberOrTitle, $categoryOrSubtitle, $tt,
			$this->getBackgroundColor(), $this->getFontAwesome(), $this->getUrl());
	}
	/**
	 * @return string
	 */
	public function getBadgeText(): ?string{
		return $this->badgeText;
	}
	/**
	 * @param string|null $badgeText
	 */
	public function setBadgeText(?string $badgeText): void{
		$this->badgeText = $badgeText;
	}
	public function getAnchorAttributesString(): string{
		$arr = $this->getAnchorAttributesArray();
		return implode("\n", $arr);
	}
	public function getMaterialTableRow(): string{
		$link = $this->getLink();
		$attr = $this->getAnchorAttributesString();
		$tooltip = $this->getTooltip();
		return "
                <tr>
                    <td>
                        $link
                    </td>
                    <td class=\"td-actions text-right\">
                        <a $attr>
                            <button type=\"button\" rel=\"tooltip\" title=\"$tooltip\"
                            class=\"btn btn-primary btn-link btn-sm\"
                                data-original-title=\"$tooltip\">
                                <i class=\"material-icons\">edit</i>
                            </button>
                        </a>
                    </td>
                </tr>

        ";
	}
	public function getColorGradientCss(): string{
		return CssHelper::generateGradientBackground($this->getBackgroundColor());
	}
	/**
	 * @return string
	 */
	public function getSolidColorCss(): string{
		$hex = QMColor::toHex($this->getBackgroundColor());
		return "background: $hex;";
	}
	public function getTailwindChipWithClose(): string{
		$this->getUrl();
		$html = HtmlHelper::renderView(view('tailwind-chip-with-close', ['button' => $this]));
		return $html;
	}
	public function getChipSmall(): string{
		return HtmlHelper::renderView(view('small-tailwind-chip', ['button' => $this]));
	}
	protected function getBackgroundColorString(): string{
		return QMColor::toString($this->backgroundColor);
	}
	public function getChipMedium(): string{
		return HtmlHelper::renderView(view('tailwind-chip-medium', ['button' => $this]));
	}
	public function toYAML(): string{
		return Yaml::dump($this->toArray());
	}
	public function getMDLChip(): string{
		$name = $this->getTitleAttribute();
		$colorStyle = $this->getSolidColorCss();
		if($this->image){
			$imgHtml = "<img class=\"mdl-chip__contact\" src=\"$this->image\" alt=\"$name\" style='$colorStyle'>";
		} else{
			$imgHtml =
				"<span class=\"mdl-chip__contact mdl-color-text--white\" style='$colorStyle'><i class=\"$this->fontAwesome\"></i></span>";
		}
		$attr = $this->getAnchorAttributesString();
		return "
            <a $attr>
                <!-- Contact Chip -->
                <span class=\"mdl-chip mdl-chip--contact\" style='$colorStyle;'>
                    $imgHtml
                    <span class=\"mdl-chip__text mdl-color-text--white\">
                        $name
                        <i class=\"fa fa-arrow-circle-right\"></i>
                    </span>
                </span>
            </a>
        ";
	}
	public function getSubtitleAttribute(): string{
		return $this->getTooltip();
	}
	public function getMDLMenuItem(): string{
		$icon = $this->getFontAwesomeHtml();
		$attr = $this->getAnchorAttributesString();
		return "
            <a $attr>
                <li class=\"mdl-menu__item\">$icon $this->title</li>
            </a>
        ";
	}
	public function getW3ListItem(): string{
		return "
            <li class=\"w3-bar\">
              <span onclick=\"this.parentElement.style.display='none'\"
              class=\"w3-bar-item w3-button w3-white w3-xlarge w3-right\">×</span>
              <img src=\"$this->image\"
               class=\"w3-bar-item w3-circle w3-hide-small\"
              style=\"width:85px\"
              alt=\"$this->title\">
              <div class=\"w3-bar-item\">
                <span class=\"w3-large\">$this->title</span><br>
                <span>$this->tooltip</span>
              </div>
            </li>
        ";
	}
	/**
	 * @param string $class
	 * @param string $aClass
	 * @return string
	 */
	public function getListItem(string $class = "", string $aClass = ""): string{
		$this->setClasses([$aClass]);
		$link = $this->getLink();
		return "
            <li class='$class'>
               $link
            </li>
        ";
	}
	/**
	 * @param string $class
	 * @return string
	 */
	public function getListItemWithDescriptionAndUrl(string $class = ""): string{
		$url = $this->getUrl();
		$title = $this->getTitleAttribute();
		$desc = $this->getTooltip();
		return "
            <li class='$class'>
               <b>$title</b> - $desc => $url
            </li>
        ";
	}
	public function getFontAwesomeHtml(): string{
		if(!$this->fontAwesome){
			return "";
		}
		return FontAwesome::html($this->getFontAwesome());
	}
	public function getLabel(): string{
		$color = $this->getBackgroundColor();
		if(!$color){
			$color = QMColor::randomHexColor();
		}
		$bootstrap = QMColor::toBootstrap($color);
		$attr = $this->getAnchorAttributesString();
		$html = "
            <a $attr>
                <span class=\"label label-$bootstrap\">$this->badgeText $this->text</span>
            </a>
        ";
		return $html;
	}
	public function getImageLink(string $style = null): string{
		if(!$style){
			$style = CssHelper::SMALL_IMAGE_STYLE;
		}
		$image = $this->getImage();
		$name = $this->getTitleAttribute();
		$attr = $this->getAnchorAttributesString();
		return "
            <a $attr>
                <img src=\"$image\" style=\"$style\" alt=\"$name\"/>
            </a>
        ";
	}
	/**
	 * @param string|null $style
	 * @return string
	 */
	public function getImageTextLink(string $style = null): string{
		$name = $this->getTitleAttribute();
		$img = $this->getImageLink($style);
		$attr = $this->getAnchorAttributesString();
		return "
$img
<a $attr>
$name
</a>
";
	}
	/**
	 * @return string
	 */
	public function getTarget(): string{
		if($this->target){
			return $this->target;
		}
		$url = $this->link;
		if(stripos($url, "#") === 0){
			return '_self';
		}
		if($url === "javascript:void(0)"){
			return '_self';
		}
		$target = '_self';
		if(stripos($url, env('APP_URL')) === false){
			$target = self::TARGET_BLANK;
		}
		if(stripos($url, 'generateCode') !== false){
			$target = self::TARGET_BLANK;
		}
		return $this->target = $target;
	}
	public function getJsonEncodedParameters(): string{
		return str_replace('"', "'", QMStr::prettyJsonEncode($this->getParameters()));
	}
	public function getNameLink(): string{
		return $this->getTextLink();
	}
	public function getShareLinks(string $class = null, string $title = null, string $id = null): string{
		$options = [];
		if($class){
			$options['class'] = $class;
		}
		if($id){
			$options['id'] = $id;
		}
		if($title){
			$options['title'] = $title;
		}
		// https://github.com/jorenvh/laravel-share
		$html = Share::page($this->getUrl(), $this->getTitleAttribute(), $options)->facebook()->linkedin($this->getTooltip())
			->pinterest()->reddit()->telegram()->twitter()->whatsapp();
		return $html;
	}
	public function getLinkButtonWithIcon(): string{
		$icon = $this->getFontAwesomeHtml();
		$title = $this->getTitleAttribute();
		$att = $this->getAnchorAttributesString();
		return "
            <a $att
                class=\"btn btn-primary\">
                $icon
                &nbsp; $title
            </a>
        ";
	}
	/**
	 * @return string
	 */
	public function getMarkdownBadgeLogo(): string{
		return $this->markdownBadgeLogo;
	}
	public function getMarkdownBadge(): string{
		return Markdown::badge($this->getUrl(), $this->getTitleAttribute(), $this->getSubtitleAttribute(), $this->getBackgroundColor(),
			$this->getMarkdownBadgeLogo());
	}
	public function getMarkdownLink(bool $lineBreaks = true): string{
		return Markdown::link($this->getTitleAttribute(), $this->getUrl(), $lineBreaks);
	}
	public function getSlackAttachment(): SlackAttachment{
		$a = new SlackAttachment([]);
		$a->setTitle($this->getTitleAttribute());
		if($this->text !== $this->title){
			$a->setText($this->text);
		}
		if(!$a->getText() && $this->tooltip && $this->tooltip !== $this->title){
			$a->setText($this->tooltip);
		}
		$a->setThumbUrl($this->getImage());
		$a->setTitleLink($this->getUrl());
		$a->setColor($this->getBackgroundColor());
		return $a;
	}
	public function getWpLink(): WpLink{
		$l = new WpLink();
		$l->link_name = $this->getTitleAttribute();
		$l->link_description = $this->getSubtitleAttribute();
		$l->link_id = $this->getId();
		$l->link_rel = $this->getTarget();
		$l->link_image = $this->getImage();
		$l->link_owner = $this->userId;
		$l->link_visible = true;
		$l->link_notes = $this->getJsonEncodedParameters();
		$l->link_url = $this->getUrl();
		return $l;
	}
	/**
	 * @param string $target
	 * @return QMButton
	 */
	public function setTarget(string $target): QMButton{
		$this->target = $target;
		if($target === self::TARGET_SELF){
			$this->setShowLoader();
		}
		return $this;
	}
	public function setVisibility(bool $visible): QMButton{
		$this->visible = $visible;
		return $this;
	}
	public function getSpatieLink(): Link{
		if($this->spatieLink){
			return $this->spatieLink;
		}
		$url = $this->getUrl();
		$link = Link::to($url, $this->getTitleWithIcon());
		$link->setAttribute('target', self::TARGET_BLANK);
		$link->setAttribute('title', $this->getTooltip() . " @ $url");
		return $this->spatieLink = $link;
	}
	private function getTitleWithIcon(): string{
		$titleText = $this->getTitleAttribute();
		$icon = $this->getFontAwesomeHtml();
		return $icon . $titleText;
	}
	/**
	 * @return string
	 */
	public function __toString(){ // Needed for array_unique
		return $this->getTitleAttribute() . " " . static::getClassTitlePlural();
	}
	public function getHtml(): string{
		$url = $this->getUrl();
		$color = $this->getBackgroundColor();
		$text = $this->getTitleAttribute();
		return "
        <table class=\"action\" align=\"center\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" role=\"presentation\">
            <tr>
                <td align=\"center\">
                    <table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" role=\"presentation\">
                        <tr>
                            <td align=\"center\">
                                <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" role=\"presentation\">
                                    <tr>
                                        <td>
                                            <a href=\"$url\" style=\"
                                                border-radius: 3px;
                                                box-shadow: 0 2px 3px rgba(0, 0, 0, 0.16);
                                                color: #fff;
                                                display: inline-block;
                                                text-decoration: none;
                                                -webkit-text-size-adjust: none;
                                                background-color: $color;
                                                border-top: 10px solid $color;
                                                border-right: 18px solid $color;
                                                border-bottom: 10px solid $color;
                                                border-left: 18px solid $color;\"
                                                target=\"_blank\">
                                                $text
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        ";
	}
	/**
	 * @return string
	 */
	public function getSecondaryImage(): ?string{
		return $this->secondaryImage;
	}
	/**
	 * @param string $secondaryImage
	 */
	public function setSecondaryImage(string $secondaryImage): void{
		$this->secondaryImage = $secondaryImage;
	}
	/**
	 * @param bool $simple
	 * @return array
	 */
	public function toArray(bool $simple = true): array{
		if($simple){
			return [
				'title' => $this->getTitleAttribute(),
				'image' => $this->image,
				'url' => $this->link,
				'tooltip' => $this->tooltip,
			];
		}
		$arr = [];
		foreach($this as $key => $value){
			if($value){
				$arr[$key] = $value;
			}
		}
		return $arr;
	}
	/**
	 * @return array
	 */
	public function getClasses(): array{
		$classes = $this->classes;
		$id = $this->id;
		if($id){
			if(stripos($id, "-button") === false){
				$id .= "-button";
			}
			$classes[] = $id;
		}
		if(static::class !== QMButton::class){
			$classes[] = QMStr::slugify((new \ReflectionClass(static::class))->getShortName());
		}
		return array_unique($classes);
	}
	/**
	 * @return string
	 */
	public function getClassesString(): ?string{
		$classes = $this->getClasses();
		if(empty($classes)){
			return null;
		}
		if(count($classes) === 1){
			return trim($classes[0]);
		}
		$str = implode(" ", $classes);
		return trim($str);
	}
	/**
	 * @param array $classes
	 */
	public function setClasses(array $classes): void{
		$this->classes = $classes;
	}
	/**
	 * @param string $class
	 */
	public function addClass(string $class): void{
		$this->classes[] = $class;
	}
	/**
	 * @return string
	 */
	public function getElementId(): ?string{
		return $this->elementId;
	}
	/**
	 * @param string $elementId
	 */
	public function setElementId(string $elementId): void{
		$this->elementId = $elementId;
	}
	/**
	 * @param string $styles
	 */
	public function setStyles(string $styles): void{
		$this->styles = $styles;
	}
	/**
	 * @param string $keyword
	 */
	public function addKeyword(string $keyword): void{
		$this->keywords[$keyword] = $keyword;
	}
	public function title(string $string): QMButton{
		$this->setTextAndTitle($string);
		return $this;
	}
	public function image(string $url): QMButton{
		$this->setImage($url);
		return $this;
	}
	public function fontAwesome(string $icon): QMButton{
		$this->setFontAwesome($icon);
		return $this;
	}
	/**
	 * @inheritDoc
	 */
	public static function getHardCodedDirectory(): string{
		$folder = self::BUTTONS_FOLDER;
		if($url = (new static())->link){
			$arr = explode('/', $url);
			foreach($arr as $value){
				$folder .= '/' . ucfirst($value);
			}
		}
		return $folder;
	}
	protected function generateFileContentOfHardCodedModel(): string{
		$namespace = QMStr::pathToNameSpace($this->getHardCodedDirectory());
		$shortClassName = $this->getHardCodedShortClassName();
		$use = $this->getUseStatements();
		$properties = $this->getHardCodedPropertiesString();
		return "<?php
namespace $namespace;
$use
class $shortClassName extends " . QMStr::toShortClassName(static::class) . " {
$properties
}";
	}
	protected function getHardCodedShortClassName(): string{
		$class = str_replace(' ', '', $this->title) . "Button";
		return QMStr::toClassName($class);
	}
	public function getModel(): ?BaseModel{
		return null;
	}
	/**
	 * @return BaseModel
	 */
	public function getModelClass(): ?string{
		if(!$this->getModel()){
			return null;
		}
		return get_class($this->getModel());
	}
	/**
	 * @return string
	 */
	protected function getHardCodedPropertiesString(): string{
		$modelClass = $this->getModelClass();
		$properties = '';
		foreach($this as $key => $value){
			if($key == 'html'){
				continue;
			}
			if($value !== null && !is_object($value)){
				try {
					if($modelClass){
						$exported = $modelClass::getConstantStringForValue($value, true);
					} else{
						$exported = VarExporter::export($value);
					}
					$properties .= "\tpublic $$key = " . $exported . ";\n";
				} catch (\Throwable $e) { // Catch closure failures
					QMLog::info(__METHOD__.": ".$e->getMessage());
					continue;
				}
			}
		}
		return $properties;
	}
	/**
	 * @return string
	 */
	protected function getUseStatements(): string{
		$modelClass = $this->getModelClass();
		$use = "use " . static::class . ";";
		if($modelClass){
			$use .= "\nuse $modelClass;";
		}
		return $use;
	}
	public function getMaterialNavItem(): string{
		$url = $this->getUrl();
		$fa = $this->getFontAwesomeHtml();
		$title = $this->getTitleAttribute();
		$css = '';
		if(QMRequest::pathContains($url)){
			$css = 'active';
		}
		$this->setClasses(["nav-link"]);
		$attr = $this->getAnchorAttributesString();
		return "
            <li class=\"nav-item $css\" onclick=\"window.showLoader && showLoader()\">
                <a $attr>
                    $fa
                    <p>$title</p>
                </a>
            </li>
        ";
	}
	/**
	 * @param string $css
	 * @return string
	 */
	public function getMaterialNavTabItem(string $css = 'active'): string{
		$url = $this->getUrl();
		$fa = $this->getFontAwesomeHtml();
		$title = $this->getTitleAttribute();
		$toggle = "";
		if(stripos($url, "http") !== 0){
			$toggle = "data-toggle=\"tab\"";
		}
		$this->setClasses(["nav-link $css"]);
		$attr = $this->getAnchorAttributesString();
		return "
            <li class=\"nav-item\" onclick=\"window.showLoader && showLoader()\">
                <a $attr
                    $toggle>
                    $fa $title
                    <div class=\"ripple-container\"></div>
                <div class=\"ripple-container\"></div>
                </a>
            </li>
        ";
	}
	/**
	 * @return static[]|Collection
	 */
	public static function all(): Collection{
		$folder = static::BUTTONS_FOLDER;
		if(!$folder){
			$folder = FileHelper::classToFolderPath(static::class);
		}
		$files = FileHelper::getClassesInFolder($folder, true);
		$buttons = [];
		foreach($files as $class){
			try {
				/** @var static $b */
				$b = new $class();
				$buttons[$b->getTitleAttribute()] = $b;
			} catch (\Throwable $e) {
				QMLog::debug("Skipping $class because constructor needs more info to generate title and url.  Exception: " .
					$e->getMessage());
			}
		}
		ksort($buttons); // Keep same order for test html comparisons
		return collect($buttons);
	}
	/**
	 * @param BaseModel $v
	 */
	protected function populateByModel(BaseModel $v): void{
		$this->setTextAndTitle($v->getTitleAttribute());
		$this->setFontAwesome($v->getFontAwesome());
		$this->setImage($v->getImage());
		$this->setTooltip($v->getSubtitleAttribute());
	}
	public static function make(): self{
		return new static();
	}
	public function getMDLAvatarListItem(): string{
		return "
            <li class=\"mdl-list__item mdl-list__item--two-line\">
                <span class=\"mdl-list__item-primary-content\">
                <a href=\"$this->link\">
                    <img class=\"material-icons mdl-list__item-avatar\" src=\"$this->image\" alt=\"\">
                </a>
                  <span>$this->title</span>
                  <span class=\"mdl-list__item-sub-title\">$this->tooltip</span>
                </span>
                <span class=\"mdl-list__item-secondary-content\">
                  <a class=\"mdl-list__item-secondary-action\" href=\"$this->link\">
                    <img class=\"material-icons mdl-list__item-avatar\" src=\"$this->secondaryImage\" alt=\"\">
                  </a>
                </span>
            </li>
        ";
	}
	private function getHrefAttribute(): ?string{
		if($url = $this->getUrl()){
			return "href=\"$url\"";
		}
		return null;
	}
	private function getTooltipAttribute(): ?string{
		if($tt = $this->getTooltip()){
			return "title=\"$tt\"";
		}
		return null;
	}
	private function getOnClickAttribute(): ?string{
		if($url = $this->onClick){
			return "onclick=\"$url\"";
		}
		return null;
	}
	protected function getAnchorAttributesArray(): array{
		$attributes = [
			//$this->getIdAttribute(),
			$this->getHrefAttribute(),
			$this->getTooltipAttribute(),
			$this->getOnClickAttribute(),
			$this->getTargetAttribute(),
			$this->getClassesAttribute(),
			$this->getStyleAttribute(),
		];
		return QMArr::removeNulls($attributes);
	}
	private function getTargetAttribute(): ?string{
		if($url = $this->target){
			return "target=\"$url\"";
		}
		return null;
	}
	private function getClassesAttribute(): ?string{
		$classes = $this->getClassesString();
		if($classes){
			return "class=\"$classes\"";
		}
		return null;
	}
	private function getStyleAttribute(): ?string{
		if($styles = $this->styles){
			return "style=\"$styles\"";
		}
		return null;
	}
	private function setShowLoader(){
		$this->onClick = "window.showLoader && showLoader()";
	}
	public function tailwindLink(): string{
		$url = $this->getUrl();
		$title = $this->getTitleAttribute();
		$target = $this->getTargetAttribute();
		return "
<a href=\"$url\"
    $target
    class=\"no-underline font-bold dim text-primary\">
    $title
</a>
";
	}
	/**
	 * @param array|Collection $buttons
	 * @param string $placeholder
	 * @param string $heading
	 * @param QMButton[] $notFoundButtons
	 * @return string
	 */
	public static function toChipSearch($buttons, string $placeholder, string $heading,
		array $notFoundButtons = null): string{
		$view = view('chip-search', [
			'buttons' => $buttons,
			'heading' => $heading ?? null,
			'placeholder' => $placeholder,
			'searchId' => QMStr::slugify($heading ?? $placeholder),
			'notFoundButtons' => $notFoundButtons,
		]);
		return HtmlHelper::renderView($view);
	}
	public function getTailwindCenteredRoundOutlineWithIcon(): string{
		return $this->getCenteredRoundOutlineWithIcon();
	}
	public function getCenteredRoundOutlineWithIcon(): string{
		$button = $this->getRoundOutlineWithIcon();
		return "
<div class=\"centered-round-outline-with-icon-container py-1\"
    style=\"margin: auto; text-align: center;\">
    $button
</div>
";
	}
	public function getRoundOutlineWithIcon(string $color = "indigo"): string{
		return "
<a {$this->getAnchorAttributesString()}>
    <button class=\"round-outline-with-icon-button text-$color-500 bg-transparent border border-solid border-$color-500 hover:bg-$color-500 hover:text-white active:bg-$color-600 font-bold uppercase px-3 py-2 rounded-full outline-none focus:outline-none mr-1 mb-1\"
    type=\"button\"
    style=\"transition: all .15s ease\">
        <i class=\"{$this->getFontAwesome()}\"></i>&nbsp;&nbsp;{$this->getTitleAttribute()}
    </button>
</a>
";
	}
	/**
	 * Get an attribute from the model.
	 * @param string|string[] $key
	 * @return mixed
	 * @noinspection DuplicatedCode
	 * @noinspection PhpParameterNameChangedDuringInheritanceInspection
	 */
	public function getAttribute($key){
		if(is_array($key)){
			foreach($key as $one){
				$result = $this->getAttribute($one);
				if(isset($result)){
					return $result;
				}
			}
			return null;
		}
		$camel = QMStr::camelize($key);
		if(!isset($this->$camel)){
			return null;
		}
		return $this->$camel;
	}
	/**
	 * @param $key
	 * @param $value
	 * @return void
	 */
	public function setAttribute($key, $value){ $this->$key = $value; }
	/**
	 * @param string $key
	 * @param $value
	 */
	public function setParam(string $key, $value){
		$this->parameters[$key] = $value;
		$this->setUrl(UrlHelper::addParams($this->getUrl()), $this->parameters);
	}
	public function getPinkRoundedButton(): string{
		return view('button-pink-rounded', ['button' => $this]);
	}
	// https://www.creative-tim.com/learning-lab/tailwind-starter-kit/landing
	public function getTailwindCardWithIconCircle(): string{
		return HtmlHelper::renderView(view('tailwind-card-with-circled-icon', ['button' => $this]));
	}
	public function getAstralMenuItem(): AbstractResource{
		return $this->getAstralExternalLink();
	}
	protected function getAstralExternalLink(): ExternalLink{
		$url = $this->getUrl($this->parameters);
		return ExternalLink::make([
			'label' => $this->getTitleAttribute(),
			'badge' => $this->getBadgeText(),
			// can be used to indicate the number of updates or notifications in this resource
			// HTML/SVG string or callback that produces one, see below
			'icon' => $this->getFontAwesomeHtml(),
			'target' => $this->getTarget(),
			'url' => $url,
		]);
	}
	public function getNameAttribute(): string{
		return $this->getTitleAttribute();
	}
	public function logUrl(){
		QMLog::logLink($this->getUrl(), $this->getTitleAttribute());
	}
	/**
	 * @param array|object $params
	 * @return string
	 */
	public static function url($params = []): string{
        $QMButton = static::instance();
        $url = $QMButton->getUrl($params);
		return $url;
	}
	public static function redirect(array $params = []){
		return UrlHelper::redirect(self::url($params));
	}
	public static function generateUrlAndOpen(array $params = []){
		N8N::openUrl(static::url($params));
	}
	public function open(array $params = []){
		N8N::openUrl($this->getUrl($params));
	}
	public static function imageHtml()
	{
		return (new static())->getImageHtml();
	}
	private function getImageHtml(): ?string {
		$url = $this->getImage();
		if(!isset($url)){
			return null;
		}
		return HtmlFacade::image($url, $this->getTitleAttribute(), [
			'class' => $this->getClasses(),
			'style' => $this->styles,
		]);
	}
}
