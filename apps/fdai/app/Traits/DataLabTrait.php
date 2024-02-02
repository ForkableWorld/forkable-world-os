<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits;
use App\Buttons\Analyzable\DataLabAnalyzeButton;
use App\Buttons\DataLabIndex\DataLabIndexButton;
use App\Buttons\DataLabIndex\DataLabTrashButton;
use App\Buttons\Model\DataLabDeleteButton;
use App\Buttons\Model\DataLabEditButton;
use App\Buttons\Model\DataLabOpenButton;
use App\Buttons\Model\ModelButton;
use App\Buttons\QMButton;
use App\Cards\QMCard;
use App\Exceptions\NoIdException;
use App\Logging\QMLog;
use App\Menus\DataLab\DataLabIndexMenu;
use App\Menus\DataLab\DataLabRelationshipMenu;
use App\Menus\DataLab\SingleModelDataLabMenu;
use App\Menus\DataLab\UsersDataLabIndexMenu;
use App\Models\User;
use App\Slim\View\Request\QMRequest;
use App\Types\QMStr;
use App\UI\FontAwesome;
use App\UI\HtmlHelper;
use App\UI\ImageUrls;
use App\UI\Markdown;
use App\Utils\UrlHelper;
use App\Fields\Text;
trait DataLabTrait {
	abstract public function getTitleAttribute(): string;
	abstract public function getImage(): string;
	abstract public function getSubtitleAttribute(): string;
	abstract public function getFontAwesome(): string;
	/**
	 * @return QMButton[]
	 */
	public function getDataLabModelButtons(): array{
		try {
			return $this->getDataLabSingleModelMenu()->getButtons();
		} catch (NoIdException $e) {
			return [static::getDataLabIndexButton()];
		}
	}
	public static function getDataLabIndexButton(): QMButton{
		return new DataLabIndexButton(static::class);
	}
	public function getDataLabSingleModelMenu(string $title = null): SingleModelDataLabMenu{
		return new SingleModelDataLabMenu($this, $title);
	}
	public function getDataLabProfileButton(array $params = []): QMButton{
		$params[QMRequest::PARAM_PROFILE] = 1;
		$b = new QMButton();
		$b->setFontAwesome(FontAwesome::HOURGLASS);
		$b->setTextAndTitle("Profile View");
		$b->setTooltip("Profile View");
		$b->setUrl($this->getDataLabShowUrl($params));
		$b->setImage(ImageUrls::EDUCATION_HOURGLASS);
		$b->setBackgroundColor($this->getColor());
		return $b;
	}
	public function getDataLabEditButton(array $params = []): QMButton{
		return new DataLabEditButton($this, $params);
	}
	/**
	 * @param array $params
	 * @return QMButton
	 */
	public function getDataLabOpenButton(array $params = []): QMButton{
		return new DataLabOpenButton($this, $params);
	}
	/**
	 * @param array $params
	 * @return QMButton
	 */
	public function getDataLabNameButton(array $params = []): QMButton{
		try {
			$b = $this->getDataLabOpenButton($params);
		} catch (NoIdException $e) {
			$b = static::getDataLabIndexButton();
		}
		$name = QMStr::truncate($this->getTitleAttribute(), 20);
		$b->setTextAndTitle($name);
		return $b;
	}
	/**
	 * @param null $id
	 * @param string|null $title
	 * @param array $params
	 * @param string|null $tooltip
	 * @return \App\Buttons\QMButton
	 */
	public static function generateDataLabShowButton($id = null, string $title = null, array $params = [],
		string $tooltip = null): QMButton{
		$b = new ModelButton(static::class);
		$b->setFontAwesome(static::FONT_AWESOME);
		if(!$title){
			$title = QMStr::truncate(QMStr::classToTitle(static::class), 20) . " $id";
		}
		$b->setTextAndTitle($title);
		$b->setTooltip($tooltip ?? "View $title");
		$b->setUrl(static::generateDataLabShowUrl($id, $params));
		$b->setImage(static::DEFAULT_IMAGE);
		$b->setBackgroundColor(static::COLOR);
		return $b;
	}
	public function getDataLabDeleteButton(): QMButton{
		return new DataLabDeleteButton($this);
	}
	public function getDataLabModelDropDownButton(): string{
		$m = $this->getDataLabSingleModelMenu();
		return $m->getDropDownMenu();
	}
	/**
	 * @param $id
	 * @param array $params
	 * @param string|null $table
	 * @return string
	 */
	public static function generateDataLabEditUrl($id, array $params = [], string $table = null): string{
		if(!$table){
			$table = static::TABLE;
		}
		if(!$table){
			le("No table provided to " . __FUNCTION__);
		}
		$route = QMStr::tableToRouteName($table);
		$path = $route . "/$id/edit";
		$path = str_replace("//edit", "/edit", $path);
		return UrlHelper::getDataLabUrl($path, $params);
	}
	public static function getDataLabIndexUrl(array $params = [], string $table = null): string{
		return static::generateDataLabIndexUrl($params, $table);
	}
	public static function getDataLabIndexMDLink(array $params = [], string $table = null): string{
		$url = static::generateDataLabIndexUrl($params, $table);
		return Markdown::link(static::getClassNameTitlePlural(), $url);
	}
	public static function getDataLabIndexLink(array $params = [], string $table = null): string{
		$url = static::generateDataLabIndexUrl($params, $table);
		return HtmlHelper::generateLink(static::getClassNameTitlePlural(), $url, true);
	}
	/** @noinspection PhpUnused */
	public static function generateDataLabTrashButton(array $params = [], string $table = null): DataLabTrashButton{
		$b = new DataLabTrashButton($table ?? static::TABLE, $params);
		return $b;
	}
	/**
	 * @param $id
	 * @param array $params
	 * @param string|null $table
	 * @return string
	 */
	public static function generateDataLabShowUrl($id, array $params = [], string $table = null): string{
		$urlEncoded = urlencode($id);
		return static::generateDataLabUrl($urlEncoded, $params, $table);
	}
	/**
	 * @param null $id
	 * @param string|null $title
	 * @param array $params
	 * @param string|null $tooltip
	 * @return string
	 */
	public static function generateDataLabShowLink($id = null, string $title = null, array $params = [],
		string $tooltip = null): string{
		$b = static::generateDataLabShowButton($id, $title, $params, $tooltip);
		return $b->getLink();
	}
	public function getDataLabEditUrl(array $params = []): string{
		$id = $this->getId();
		if($id === null){
			/** @noinspection PhpToStringImplementationInspection */
			le("No id to generate edit url on $this " . get_class($this));
		}
		return static::generateDataLabEditUrl($id, $params);
	}
	/**
	 * @param array $params
	 * @return string
	 */
	public function getDataLabShowUrl(array $params = []): string{
		if(!$this->hasId()){
			$this->logDebug("No id for show url so returning index url");
			return $this->getDataLabIndexUrl();
		}
		$id = $this->getId();
		return static::generateDataLabUrl($id, $params);
	}
	public static function generateDataLabIndexUrls(): array{
		$t = static::getClassNameTitlePlural();
		return [
			"$t" => static::generateDataLabIndexUrl(),
			"Deleted $t" => static::generateDataLabTrashedUrl(),
			"Analysis Progress $t" => static::generateDataLabAnalysisProgressUrl(),
		];
	}
	public static function logDataLabIndexUrls(){
		QMLog::list(static::generateDataLabIndexUrls(),
			(new \ReflectionClass(static::class))->getShortName() . " Indexes");
	}
	public static function generateDataLabIndexUrlsString(): string{
		return \App\Logging\QMLog::print_r(self::generateDataLabIndexUrls(), true);
	}
	public static function generateDataLabTrashedUrl(): string{
		return static::generateDataLabIndexUrl([QMRequest::PARAM_TRASHED => 1]);
	}
	public static function generateDataLabAnalysisProgressUrl(): string{
		return static::generateDataLabIndexUrl([QMRequest::PARAM_ANALYZABLE => 1]);
	}
	/**
	 * @return QMButton
	 */
	public function getDataLabAnalyzeButton(): QMButton{
		return new DataLabAnalyzeButton($this);
	}
	/**
	 * @param array $params
	 * @param string|null $style
	 * @return string
	 */
	public function getDataLabImageLink(array $params = [], string $style = null): string{
		try {
			$b = $this->getDataLabOpenButton($params);
		} catch (NoIdException $e) {
			$b = static::getDataLabIndexButton();
		}
		return $b->getImageLink($style);
	}
	/**
	 * @param array $params
	 * @param string|null $style
	 * @return string
	 * @throws NoIdException
	 */
	public function getDataLabImageNameLink(array $params = [], string $style = null): string{
		$b = $this->getDataLabOpenButton($params);
		$b->setTextAndTitle($this->getTitleAttribute());
		return $b->getImageTextLink($style);
	}
	/**
	 * @return string|null
	 */
	public function getDataLabAnalyzeUrl(): string{
		return $this->getDataLabShowUrl([QMRequest::PARAM_ANALYZE => true]);
	}
	public function getDataLabDisplayNameLink(array $params = [], int $maxLength = 50): string{
		$url = $this->getDataLabShowUrl($params);
		$fullName = $this->getTitleAttribute();
		$name = $this->getTitleAttribute();
		$truncated = QMStr::truncate($name, $maxLength);
		$class = QMStr::classToTitle((new \ReflectionClass(static::class))->getShortName());
		$tooltip = str_replace('"', '', "See $fullName $class Details");
		return "<a href=\"$url\" target='_self' title=\"$tooltip\">$truncated</a>";
	}
	public function getDataLabMDLChipHtml(array $params = [], int $maxLength = 50): string{
		$b = $this->getDataLabButton($params);
		return $b->getMDLChip();
	}
	/**
	 * @param array $params
	 * @param int|string $badgeText
	 * @param string $name
	 * @param string $fontAwesome
	 * @param string $tooltip
	 * @param string $color
	 * @param string|null $url
	 * @return QMButton
	 */
	public static function generateDataLabIndexButton(array $params, $badgeText = null, string $name = null,
		string $fontAwesome = null, string $tooltip = null, string $color = null, string $url = null): QMButton{
		$b = new DataLabIndexButton(static::class, $params);
		if($tooltip){
			$b->setTooltip($tooltip);
		}
		if($fontAwesome){
			$b->setFontAwesome($fontAwesome);
		}
		if($color){
			$b->setBackgroundColor($color);
		}
		if($name){
			$b->setTextAndTitle($name);
		}
		if($url){
			$b->setUrl($url, $params);
		}
		if($badgeText !== null){
			$b->setBadgeText($badgeText);
		}
		return $b;
	}
	public function getDataLabImageNameDropDown(): string{
		$buttons = $this->getDataLabModelButtons();
		if(!$buttons){
			return "";
		}
		return HtmlHelper::generateImageNameDropDown($this->getImage(), $buttons, $this->getTitleAttribute() . " Options",
			$this->getTitleAttribute());
	}
	public function getDataLabImageDropDown(): string{
		$buttons = $this->getDataLabModelButtons();
		if(!$buttons){
			return "";
		}
		return HtmlHelper::generateImageDropDown($this->getImage(), $buttons, $this->getTitleAttribute() . " Options",
			$this->getTitleAttribute());
	}
	/** @noinspection PhpUnused */
	public function getDataLabNameDropDownButton(string $title = null): string{
		return $this->getDataLabSingleModelMenu($title)->getDropDownMenu();
	}
	/** @noinspection PhpUnused */
	public static function getDataLabIndexDropDown(string $table = null): string{
		return static::getDataLabIndexMenu($table)->getDropDownMenu();
	}
	public static function getDataLabIndexMenu(string $table = null){
		$table = static::TABLE ?? $table;
		if($table === User::TABLE){
			return new UsersDataLabIndexMenu();
		}
		return new DataLabIndexMenu(static::TABLE ?? $table);
	}
	public function getDataLabIdLink(array $params = []): string{
		$b = $this->getDataLabOpenButton($params);
		$b->setTextAndTitle($this->getId());
		$b->setTooltip($this->getTitleAttribute());
		$b->setFontAwesome($this->getFontAwesome());
		return $b->getLink();
	}
	/**
	 * @return QMButton[]
	 * @noinspection PhpUnused
	 */
	public function getDataLabButtonsHtml(): string{
		$html = '';
		$buttons = $this->getDataLabModelButtons();
		foreach($buttons as $button){
			$html .= $button->getRoundOutlineWithIcon();
		}
		return $html;
	}
	/**
	 * @param array $params
	 * @return QMButton
	 */
	public function getDataLabButton(array $params = []): QMButton{
		return $this->getDataLabNameButton($params);
	}
	public function getDataLabCard(): QMCard{
		$c = new QMCard($this->getUniqueIndexIdsSlug());
		$c->setBackgroundColor($this->getColor());
		$c->setImage($this->getImage());
		$c->setSubTitle($this->getSubtitleAttribute());
		$c->setContent($this->getSubtitleAttribute());
		$c->setTitle($this->getTitleAttribute());
		$c->setUrl($this->getDataLabShowUrl());
		$buttons = $this->getDataLabModelButtons();
		$c->setActionSheetButtons($buttons);
		$c->setHtmlContent($this->getDataLabButtonsHtml());
		return $c;
	}
	public function logDataLabUrl(){
		QMLog::linkButton("View " . $this->getTitleAttribute(), $this->getDataLabShowUrl());
	}
	public static function generateDataLabUrl(string $path = null, array $params = [], string $table = null): string{
		if(!$table){
			$table = static::TABLE;
		}
		if(!$table){
			le("No table provided to " . __FUNCTION__);
		}
		$route = QMStr::tableToRouteName($table);
		if($path){
			$path = $route . "/$path";
		} else{
			$path = $route;
		}
		$path = str_replace($route . "//", $route . "/", $path);
		return UrlHelper::getDataLabUrl($path, $params);
	}
	public static function generateDataLabIndexUrl(array $params = [], string $table = null): string{
		return static::generateDataLabUrl(null, $params, $table);
	}
	public function getDataLabDeleteUrl(array $params = []): ?string{
		$id = $this->getId();
		if($id === null){
			/** @noinspection PhpToStringImplementationInspection */
			le("No id to generate delete url on $this " . get_class($this));
		}
		return static::generateDataLabShowUrl($this->getId(), $params);
	}
	public function getDataLabUrls(array $params = []): array{
		try {
			$title = $this->getTitleAttribute();
		} catch (\Throwable $e) {
			$title = "[Could not get title because: " . $e->getMessage() . "]";
		}
		$arr = ["All " . QMStr::tableToTitle(static::TABLE) => static::generateDataLabUrl(null, $params)];
		if($this->hasId()){
			$arr["Open $title"] = $this->getDataLabShowUrl($params);
		}
		return $arr;
	}
	public function getDataLabInterestingRelationshipsMenu(): DataLabRelationshipMenu{
		/** @var DataLabTrait $l */
		$l = $this->l();
		return $l->getDataLabRelationshipMenu();
	}
	public function getDataLabRelationshipMenu(): DataLabRelationshipMenu{
		return new DataLabRelationshipMenu($this);
	}
	public function dataLabLinkField(string $title = "More Details"): Text{
		return Text::make(" ", function(){
			$html = $this->getDataLabButton()->tailwindLink();
			return $html;
		})->asHtml();
	}
	public function getDataLabLinkField(string $title = "More Details"): Text{
		return $this->dataLabLinkField($title);
	}
}
