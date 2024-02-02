<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Cards;
use App\Buttons\QMButton;
use App\Buttons\Sharing\EmailSharingButton;
use App\Buttons\Sharing\FacebookSharingButton;
use App\Buttons\Sharing\TwitterSharingButton;
use App\InputFields\InputField;
use App\Models\User;
use App\Notifications\BaseNotification;
use App\Notifications\CardNotification;
use App\Slim\Model\DBModel;
use App\Slim\View\Request\QMRequest;
use App\Traits\CompressibleTrait;
use App\Types\QMStr;
use App\UI\CssHelper;
use App\UI\HtmlHelper;
use App\UI\IonIcon;
use App\UI\QMColor;
use App\Utils\AppMode;
use Dialogflow\Action\Questions\ListCard;
use stdClass;
class QMCard extends DBModel {
	use CompressibleTrait;
	public const TYPE_intro = 'intro';
	public const TYPE_study = 'study';
	public const TYPE_onboarding = 'onboarding';
	public const TYPE_tracking_reminder_notification = 'tracking_reminder_notification';
	public $actionSheetButtons;
	public $avatar;
	public $avatarCircular;
	public $buttons;
	public $buttonsSecondary;
	public $backgroundColor;
	public $fontAwesome;
	public $content;
	public $headerTitle;
	public $html;
	public $htmlContent;
	public $id;
	public $image;
	public $inputFields = [];
	public $intent;
	public $ionIcon;
	public $link;
	public $parameters = [];
	public $relatedCards = [];
	public $selectedButton;
	public $sharingBody;
	public $sharingButtons;
	public $sharingTitle;
	public $subHeader;
	public $subTitle;
	public $title;
	public $type;
	/**
	 * @param string|int $id
	 */
	public function __construct($id = null){
		if($id){
			$this->id = $id;
		}
		if(!AppMode::isApiRequest() || QMRequest::getParam([
				'renderHtml',
				'includeHtml',
			])){
			$this->setHtml();
		} // Really slow
		if(!$this->type){
			$this->type = str_replace('_card', '', $this->getShortClassName());
		}
		if(isset($this->image->url)){
			$this->image = $this->image->url;
		}
	}
	/**
	 * @param string $title
	 * @param string $body
	 * @param string|null $footer
	 * @return string
	 */
	public static function generateCardHtml(string $title, string $body, string $footer = null): string{
		$maxWidth = CssHelper::GLOBAL_MAX_POST_CONTENT_WIDTH;
		return "
                <div style='
                    text-align: center;
                    box-shadow: 0 3px 12px rgba(0, 0, 0, 0.23), 0 3px 12px rgba(0, 0, 0, 0.16);
                    transition: 0.3s;
                    padding: 1px 10px 15px 10px;
                    border-radius: 9px;
                    max-width: " . $maxWidth . "px;
                    margin: 20px auto 10px auto;
                    border: 0 solid #2b3138;
                    overflow: hidden;
                '>
                    <h1 style='text-align: center;'>" . $title . "</h1>
                    $body
                    <p> $footer </p>
                </div>
            ";
	}
	/**
	 * @return string
	 */
	public function getId(): string{
		if(!$this->id){
			$id = $this->title ?: $this->headerTitle;
			$id = QMStr::slugify($id);
			$this->setId($id);
		}
		return $this->id;
	}
	/**
	 * @param string $subTitle
	 * @return string
	 */
	public function setSubTitle(string $subTitle): string{
		return $this->subTitle = $subTitle;
	}
	/**
	 * @param string $htmlContent
	 */
	public function setContentAndHtmlContent(string $htmlContent){
		$this->setHtmlContent($htmlContent);
		$this->content = strip_tags($htmlContent);
	}
	/**
	 * @param QMButton $button
	 */
	public function addButton(QMButton $button){
		$this->buttons[] = $button;
	}
	/**
	 * @param QMButton $button
	 */
	public function addActionSheetButton(QMButton $button){
		$this->actionSheetButtons[] = $button;
	}
	/**
	 * @param string|null $image
	 */
	public function setImage(string $image = null){
		$this->image = $image;
	}
	/**
	 * @param string $avatar
	 */
	public function setAvatar(string $avatar){
		$this->avatar = $avatar;
	}
	/**
	 * @return string
	 */
	public function getSharingTitle(): string{
		return $this->sharingTitle;
	}
	/**
	 * @param string $sharingTitle
	 */
	public function setSharingTitle(string $sharingTitle){
		$this->sharingTitle = $sharingTitle;
	}
	/**
	 * @return string
	 */
	public function getSharingBody(): string{
		return $this->sharingBody;
	}
	/**
	 * @param string $sharingBody
	 */
	public function setSharingBody(string $sharingBody){
		$this->sharingBody = $sharingBody;
	}
	/**
	 * @param string $link
	 * @param string $sharingTitle
	 * @param string $sharingBody
	 */
	public function setLinkAndSharingButtons(string $link, string $sharingTitle, string $sharingBody){
		$this->setUrl($link);
		$this->setSharingTitle($sharingTitle);
		$this->setSharingBody($sharingBody);
		$this->setSharingButtonEmail();
		//$this->sharingButtons[] = new GoogleSharingButton($this->getUrl());
		$this->setSharingButtonTwitter();
		$this->setSharingButtonFacebook();
	}
	/**
	 * @param string $url
	 */
	public function setUrl(string $url){
		$this->link = $url;
	}
	/**
	 * @param array $params
	 * @return string
	 */
	public function getUrl(array $params = []): string{
		return $this->link;
	}
	/**
	 * @return void
	 */
	private function setSharingButtonEmail(): void{
		$this->sharingButtons[] =
			new EmailSharingButton($this->getUrl(), $this->getSharingTitle(), $this->getSharingBody());
	}
	/**
	 * @return void
	 */
	private function setSharingButtonFacebook(): void{
		$this->sharingButtons[] = new FacebookSharingButton($this->getUrl());
	}
	/**
	 * @return void $studyLinkTwitter
	 */
	private function setSharingButtonTwitter(): void{
		$this->sharingButtons[] = new TwitterSharingButton($this->getUrl(), $this->getSharingTitle());
	}
	/**
	 * @return string
	 */
	public function setHtml(): string{
		try {
			$html = view('components.cards/material-card', ['card' => $this])->render();
		} catch (\Throwable $e) {
			/** @var \LogicException $e */
			throw $e;
		}
		return $this->html = $html;
		//$withCss = HtmlHelper::addMaterialDesignCss($html);
		//return $this->html = $withCss;
	}
	/**
	 * @param string $ionIcon
	 */
	public function setIonIcon(string $ionIcon){
		$this->ionIcon = $ionIcon;
		if(!$this->avatar){
			$this->setAvatar(IonIcon::getIonIconPngUrl($ionIcon));
		}
		if(!$this->avatarCircular){
			$this->avatarCircular = IonIcon::getIonIconPngUrl($ionIcon);
		}
	}
	/**
	 * @param array|null $buttons
	 * @return ListCard
	 */
	public function getOptionsListCard(array $buttons = null): ListCard{
		$listCard = new ListCard();
		$listCard->title($this->title);
		if(!$buttons){
			$buttons = $this->getButtons();
		}
		foreach($buttons as $button){
			$listCard->addOption($button->getOption());
		}
		return $listCard;
	}
	/**
	 * @return QMButton[]
	 */
	public function getButtons(): array{
		$oldButtons = $this->instantiateButtons($this->buttons);
		return $this->buttons = $oldButtons;
	}
	/**
	 * @param $oldButtons
	 * @return QMButton[]
	 */
	private function instantiateButtons($oldButtons): array{
		if(isset($oldButtons[0]) && ($oldButtons[0] instanceof stdClass || is_array($oldButtons[0]))){
			$newButtons = [];
			/** @var QMButton $stdClassButton */
			foreach($oldButtons as $stdClassButton){
				if(is_array($stdClassButton)){
					$stdClassButton = json_decode(json_encode($stdClassButton));
				}
				if(!isset($stdClassButton->text) && isset($stdClassButton->title)){
					$stdClassButton->text = $stdClassButton->title;
				}
				if(!isset($stdClassButton->text)){
					$this->logError("No text or title field on button!", ['stdClassButton' => $stdClassButton]);
					continue;
				}
				$b = new QMButton($stdClassButton->text);
				foreach($stdClassButton as $key => $item){
					$b->$key = $item;
				}
				$newButtons[] = $b;
			}
			return $newButtons;
		}
		return $oldButtons;
	}
	/**
	 * @return InputField[]
	 */
	public function getInputFields(): array{
		return $this->inputFields;
	}
	/**
	 * @param InputField[] $inputFields
	 */
	public function setInputFields(array $inputFields){
		$this->inputFields = $inputFields;
	}
	/**
	 * @return string
	 */
	public function getHeaderTitle(): string{
		return $this->headerTitle;
	}
	/**
	 * @param string $headerTitle
	 */
	public function setHeaderTitle(string $headerTitle){
		$this->headerTitle = $headerTitle;
		$this->getId();
	}
	/**
	 * @return string
	 */
	public function getSubHeader(): string{
		return $this->subHeader;
	}
	/**
	 * @param string $subHeader
	 */
	public function setSubHeader(string $subHeader){
		$this->subHeader = $subHeader;
	}
	/**
	 * @return QMButton[]
	 */
	public function getActionSheetButtons(): array{
		return $this->actionSheetButtons;
	}
	/**
	 * @param QMButton[] $actionSheetButtons
	 */
	public function setActionSheetButtons(array $actionSheetButtons){
		$this->actionSheetButtons = $actionSheetButtons;
	}
	/**
	 * @return string
	 */
	public function getAvatarCircular(): string{
		if(!$this->avatarCircular){
			return $this->avatar;
		}
		return $this->avatarCircular;
	}
	/**
	 * @param $stdClass
	 * @param string|null $type
	 * @return QMCard
	 */
	public static function instantiateCard($stdClass, string $type = null): QMCard{
		if(!$type && isset($stdClass->type)){
			$type = $stdClass->type;
		}
		if(!$type){
			$type = '';
		}
		$className = ucfirst($type) . 'Card';
		$card = parent::instantiate('Cards', $className, $stdClass);
		return $card;
	}
	/**
	 * @return string
	 */
	public function getType(): string{
		return $this->type;
	}
	/**
	 * @param string $type
	 */
	public function setType(string $type){
		$this->type = $type;
	}
	/**
	 * @return array
	 */
	public function getParameters(): array{
		return $this->parameters;
	}
	/**
	 * @param array $parameters
	 */
	public function setParameters(array $parameters){
		$this->parameters = $parameters;
	}
	/**
	 * @param string $key
	 * @param $value
	 */
	public function addParameter(string $key, $value){
		$this->parameters[$key] = $value;
	}
	/**
	 * @param string $id
	 */
	public function setId($id){
		$this->id = $id;
	}
	/**
	 * @param string $htmlContent
	 */
	public function setHtmlContent(string $htmlContent){
		if(empty($htmlContent)){
			le("No htmlContent provided");
		}
		$this->htmlContent = $htmlContent;
	}
	/**
	 * @return string
	 */
	public function getContent(): string{
		return $this->content;
	}
	/**
	 * @param string $content
	 */
	public function setContent(string $content){
		$this->content = $content;
	}
	/**
	 * @return string
	 */
	public function getTitleAttribute(): string{
		return $this->title;
	}
	/**
	 * @param string $title
	 */
	public function setTitle(string $title){
		$this->title = $title;
	}
	/**
	 * @return string
	 */
	public function getHtmlContent(): string{
		$html = $this->htmlContent;
		if(!$html){
			return "<div>$this->content</div>";
		}
		$this->setHtmlContent($html);
		return $html;
	}
	/**
	 * @return string
	 */
	public function getBackgroundColor(): string{
		return $this->backgroundColor ?: QMColor::HEX_WHITE;
	}
	/**
	 * @return string
	 */
	public function getTextColor(): string{
		$backgroundColor = $this->getBackgroundColor();
		if($backgroundColor === "white"){
			return "black";
		}
		return "white";
	}
	/**
	 * @param string $backgroundColor
	 */
	public function setBackgroundColor(string $backgroundColor): void{
		$this->backgroundColor = $backgroundColor;
	}
	/**
	 * @param mixed $fontAwesome
	 * @return QMCard
	 */
	public function setFontAwesome(string $fontAwesome): self{
		$this->fontAwesome = $fontAwesome;
		return $this;
	}
	public function push(int $userId): BaseNotification{
		$u = User::findInMemoryOrDB($userId);
		$n = $this->getNotification();
		$u->notify($n);
		return $n;
	}
	public function getNotification(): BaseNotification{
		$n = new CardNotification($this);
		return $n;
	}
	/**
	 * @param InputField $inputField
	 */
	protected function addInputField(InputField $inputField){
		$this->inputFields[] = $inputField;
	}
	/**
	 * @return string
	 */
	public function getTitleAvatarHeaderHtml(): string{
		$title = $this->getTitleAttribute();
		$image = $this->getAvatarCircular();
		$textColor = $this->getTextColor();
		return "
            <table width=\"100%\" border=\"0\"
                style='
                    margin-top: 10px;
                    margin-bottom: 10px;
            '>
                <tr style='
                    font-family:sans-serif;
                     color: $textColor;
                 '>
                    <td width='50px'>
                        <span style=\"
                            padding: 5px;
                            background-color: white;
                            float: left;
                            width: 30px;
                            height: 30px;
                            border-radius: 30px;
                            border-color: $textColor;
                            border-width: 3px;
                            border-style: solid;
                        \">
                            <img src=\"$image\" alt=\"$title\" style=\"width: 100%; height: 100%;\">
                        </span>
                    </td>
                    <td><h2 style='text-align: center; margin: auto; color: $textColor;'>$title</h2>  </td>
                </tr>
            </table>
        ";
	}
	/**
	 * @param string|null $backgroundColor
	 * @return string
	 */
	public function getHtml(string $backgroundColor = null): string{
		if($backgroundColor){
			$this->setBackgroundColor($backgroundColor);
		}
		$header = $this->getTitleAvatarHeaderHtml();
		$backgroundColor = $this->getBackgroundColor();
		$body = $this->htmlContent;
		if(!$body && $this->content){
			$body = "<div>$this->content</div>";
			$this->setHtmlContent($body);
		}
		$buttons = $this->getButtons();
		$buttonHtml = '';
		foreach($buttons as $button){
			$buttonHtml .= $button->getRoundedHtml($this->getTextColor(), $this->getBackgroundColor());
		}
		return $this->html = "
            <div style='
                font-family: arial,helvetica,sans-serif;
                text-align: center;
                box-shadow: 0 3px 12px rgba(0, 0, 0, 0.23), 0 3px 12px rgba(0, 0, 0, 0.16);
                transition: 0.3s;
                padding: 1px 10px 15px 10px;
                border-radius: 9px;
                background-color: $backgroundColor;
                max-width: 600px;
                margin: 20px auto auto;
                border: 0 solid #2b3138;
                overflow: hidden;
            '>
                $header
                $body
                <div>$buttonHtml</div>
            </div>
        ";
	}
	public function getButton(array $params = []): QMButton{
		$buttons = $this->getButtons();
		return $buttons[0];
	}
	public function renderMaterialStatCard(): string{
		$numberOrTitle = $this->getTitleAttribute();
		$categoryOrSubtitle = $this->getSubtitleAttribute();
		$description = $this->getSubtitleAttribute();
		$iconBackgroundColor = $this->getBackgroundColor();
		$fontAwesome = $this->getFontAwesome();
		$buttons = $this->getButtons();
		$buttonHtml = "";
		foreach($buttons as $b){
			$url = $b->getUrl();
			$tooltip = $b->getTooltip();
			$buttonText = $b->getTitleAttribute();
			$buttonHtml .= "
                <a href=\"$url\" title=\"$tooltip\" onclick=\"window.showLoader && showLoader()\">
                    <div class=\"stats\">
                        <i class=\"material-icons\">launch</i> $buttonText
                    </div>
                </a>
            ";
		}
		$iconBackgroundColor = QMColor::toBootstrap($iconBackgroundColor);
		$id = QMStr::slugify($categoryOrSubtitle ?? $numberOrTitle);
		return "
            <div class=\"card-stats-container\" >
                <div id=\"$id-card\"
                    class=\"card card-stats\"
                    title=\"$description\">
                    <div class=\"card-header card-header-$iconBackgroundColor card-header-icon\">
                      <div class=\"card-icon\">
                        <i class=\"$fontAwesome\"></i>
                      </div>
                      <p class=\"card-category\">$categoryOrSubtitle</p>
                        <h3 class=\"card-title\">$numberOrTitle</h3>
                    </div>
                    <div class=\"card-footer\">
                      $buttonHtml
                    </div>
              </div>
            </div>
        ";
	}
	public function getSubtitleAttribute(): string{
		return $this->getContent();
	}
	public function renderBootstrap3(): string{
		return HtmlHelper::renderView(view('bootstrap-card', ['card' => $this]));
	}
	public function tailwind(): string{
		return $this->getMaterialCard();
	}
	public function getMaterialCard(): string{
		// Needs tailwind
		return HtmlHelper::renderView(view('material-card', ['card' => $this]));
	}
	public function getCssClasses(): array{
		$numberOrTitle = $this->getTitleAttribute();
		$categoryOrSubtitle = $this->getSubtitleAttribute();
		$id = QMStr::slugify($categoryOrSubtitle ?? $numberOrTitle);
		return [$id];
	}
	public function getCssClassesString(): string{
		return implode(" ", $this->getCssClasses());
	}
	public function getTailwindCard(): string {
		$arr = [];
		foreach($this as $key => $value){
			if($value){
				$arr[$key] = $value;
			}
		}
		if(isset($arr['htmlContent'])){
			$arr['content'] = $arr['htmlContent'];
			unset($arr['title']);
		}
		return HtmlHelper::renderView(view('tailwind-card', $arr));
	}
}
