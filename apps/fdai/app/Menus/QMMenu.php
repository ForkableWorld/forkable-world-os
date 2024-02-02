<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Menus;
use App\Buttons\QMButton;
use App\Exceptions\GitAlreadyUpToDateException;
use App\Exceptions\GitBranchAlreadyExistsException;
use App\Exceptions\GitBranchNotFoundException;
use App\Exceptions\GitConflictException;
use App\Exceptions\GitLockException;
use App\Exceptions\GitNoStashException;
use App\Exceptions\GitRepoAlreadyExistsException;
use App\Files\FileHelper;
use App\Logging\QMLog;
use App\Menus\DataLab\DataLabIndexRoutesMenu;
use App\Menus\RoleBased\GuestMenu;
use App\Menus\Routes\AdminRoutesMenu;
use App\Menus\Routes\DataLabRoutesMenu;
use App\Menus\Routes\DevRoutesMenu;
use App\Menus\Routes\ExamplesRoutesMenu;
use App\Repos\QMAPIRepo;
use App\Slim\Middleware\QMAuth;
use App\Traits\HasClassName;
use App\Types\QMStr;
use App\UI\FontAwesome;
use App\UI\HtmlHelper;
use Spatie\Menu\Menu;
use Symfony\Component\Yaml\Yaml;
abstract class QMMenu {
	use HasClassName;
	public const VIEW_FOLDER = 'resources/views/menus';
	public $backgroundColor;
	public array $buttons = [];
	public $badge;
	public $image;
	public $ionIcon;
	public $expanded = false;
	public $title;
	public $tooltip;
	public $fontAwesome;
	public $rememberMenuState = true;
	/**
	 * @var Menu
	 */
	protected $spatieMenu;
	protected $html;
	/**
	 * @return QMButton[]
	 */
	abstract public function getButtons(): array;
	/**
	 * @return string
	 */
	abstract public function getTitleAttribute(): string;
	/**
	 * @return string
	 */
	abstract public function getImage(): string;
	/**
	 * @return string
	 */
	abstract public function getFontAwesome(): string;
	/**
	 * @return string
	 */
	abstract public function getTooltip(): string;
	/**
	 * @return string
	 * @noinspection PhpUnused
	 */
	public function getSubtitleAttribute():string{return $this->getTooltip();}
	public function getDropDownMenu(): string{
		try {
			$buttons = $this->getButtons();
		} catch (\Throwable $e) {
			QMLog::info(__METHOD__.": ".$e->getMessage());
			$buttons = $this->getButtons();
		}
		$title = $this->getTitleHtml();
		$color = $this->getBackgroundColor();
		$id = $this->getId();
		return HtmlHelper::generateDropDownButtonHtml($title, $buttons, $this->tooltip, $color, $id);
	}
	/**
	 * @return string
	 */
	public function getBackgroundColor(): string{
		return $this->backgroundColor;
	}
	public function getTitleHtml(): string{
		$fa = "";
		if($this->fontAwesome){
			$fa = FontAwesome::html($this->fontAwesome);
		}
		return "<span title=\"$this->tooltip\">$fa {$this->getTitleAttribute()}</span>";
	}
	public static function saveHtmlMenusAndButtons(){
		(new AdminRoutesMenu())->saveButtons();
		(new DataLabRoutesMenu())->saveButtons();
		(new DataLabRoutesMenu())->saveHtml();
		//(new APIRoutesMenu())->saveHtml();
		(new DevRoutesMenu())->saveHtml();
		//(new RoutesMenu())->saveHtmlAndButtons();
		(new AdminRoutesMenu())->saveHtml();
		(new ExamplesRoutesMenu())->saveHtml();
	}
	public function getWpLinks(): array{
		$buttons = $this->getButtons();
		$links = [];
		foreach($buttons as $button){
			$links[] = $button->getWpLink();
		}
		return $links;
	}
	/**
	 * @return string
	 */
	public function toHtml(): string{
		if($this->html){
			return $this->html;
		}
		//QMProfile::startProfile();
		$menu = $this->getHiddenSearchMenuList();
		$str = $menu->__toString();
		$str = str_replace("</li><li>", "</li>\n<li>", $str);
		$str = str_replace("<li><a", "<li>\n\t<a", $str);
		$str = str_replace("><i ", ">\n\t\t<i ", $str);
		$str = str_replace("</a></li>", "\n\t</a>\n</li>", $str);
		$str = str_replace("</li></ul>", "</li>\n</ul>", $str);
		$str = str_replace("><li>", ">\n<li>", $str);
		return $this->html = $str;
	}
	/**
	 * @return string
	 */
	public static function getViewPath(): string{
		$view = static::getViewName();
		return static::VIEW_FOLDER . "/$view.blade.php";
	}
	public static function getViewName(): string{
		return static::getSlugifiedClassName();
	}
	/**
	 * @return string
	 */
	public function saveHtml(): string{
		$html = $this->toHtml();
		FileHelper::writeByFilePath(self::getViewPath(), $html);
		return $html;
	}
	/**
	 * @return Menu
	 */
	public function getHiddenSearchMenuList(): Menu{
		if($this->spatieMenu){
			return $this->spatieMenu;
		}
		//QMProfile::startProfile();
		$menu = Menu::new();
		try {
			$buttons = $this->getButtons();
			foreach($buttons as $b){
				if(str_contains($b->action, '/assets/')){
					continue;
				}
				$menu->add($b->getSpatieLink());
			}
		} catch (\Throwable $e) {
			QMLog::error(__METHOD__.": ".$e->getMessage());
		}
		$menu->addClass("sidebar-menu");
		$menu->setAttribute("style", "display: none;");
		$menu->setAttribute("id", "searchable-menu-list");
		return $this->spatieMenu = $menu;
	}
	public function getId(): string{
		return $this->getSlugifiedClassName();
	}
	/**
	 * @param QMButton[] $buttons
	 * @return static
	 */
	public function addButtons(array $buttons): QMMenu{
		foreach($buttons as $button){
			$this->addButton($button);
		}
		return $this;
	}
	/**
	 * @throws GitRepoAlreadyExistsException
	 * @throws GitLockException
	 * @throws GitConflictException
	 * @throws GitNoStashException
	 * @throws GitBranchNotFoundException
	 * @throws GitBranchAlreadyExistsException
	 * @throws GitAlreadyUpToDateException
	 * @throws OutOfMemoryException
	 */
	public static function generateAndCommitMenusAndButtons(){
		QMAPIRepo::createFeatureBranch('updated-menus');
		self::saveHtmlMenusAndButtons();
		QMAPIRepo::addFilesInFolder(static::VIEW_FOLDER);
		QMAPIRepo::commitAndPush("Updated menus");
	}
	public function saveButtons(){
		$buttons = $this->getButtons();
		foreach($buttons as $button){
			$button->saveHardCodedModel();
		}
	}
	/** @noinspection PhpUnused */
	public function getMaterialHtml(string $menuName, string $directionToOpen = "bottom-right"): string{
		$chips = "";
		$buttons = $this->getButtons();
		foreach($buttons as $button){
			$chips .= $button->getMDLMenuItem();
		}
		$menuName = "$menuName-menu";
		return "
            <button id=\"$menuName\"
                    class=\"mdl-button mdl-js-button mdl-button--icon\">
              <i class=\"material-icons\">more_vert</i>
            </button>
            <ul class=\"mdl-menu mdl-menu--$directionToOpen mdl-js-menu mdl-js-ripple-effect\" for=\"$menuName\">
              $chips
            </ul>
        ";
	}
	public function getMDLAvatarList(): string{
		$chips = "";
		$buttons = $this->getButtons();
		foreach($buttons as $button){
			$chips .= $button->getMDLAvatarListItem();
		}
		return HtmlHelper::getMDLTags() . "
            <style type=\"text/css\">
                .demo-list-three {
                  width: 650px;
                }
            </style>
            <ul class=\"demo-list-three mdl-list\">
              $chips
            </ul>
        ";
	}
	public function getLabelsHtml(): string{
		$chips = "";
		$buttons = $this->getButtons();
		foreach($buttons as $button){
			$chips .= $button->getLabel() . "\n";
		}
		return "
            <p>
                $chips
            </p>
        ";
	}
	public function getMaterialStatCards(string $style = ''): string{
		$str = "";
		$buttons = $this->getButtons();
		foreach($buttons as $button){
			$str .= $button->getMaterialStatCard();
		}
		return "
            <div id=\"material-cards-container\"  class=\"content\" style=\"$style\">
                <div class=\"container-fluid\">
                    <div class=\"row\" >
                        $str
                    </div>
                </div>
            </div>
        ";
	}
	public function getCountBoxesHtml(): string{
		$str = "";
		$buttons = $this->getButtons();
		foreach($buttons as $button){
			$str .= $button->getStatBox();
		}
		return "
            <div class=\"row\">
                $str
            </div>
        ";
	}
	/** @noinspection getChipHtml */
	public function getMDLChipsHtml(): string{
		$chips = HtmlHelper::getMDLTags();
		$buttons = $this->getButtons();
		foreach($buttons as $button){
			$chips .= $button->getMDLChip();
		}
		return $chips;
	}
	public function getYAML(): string{
		return Yaml::dump($this->toArray());
	}
	public function toArray(): array{
		$items = [];
		foreach($this->getButtons() as $button){
			$items[] = $button->toArray();
		}
		return ['title' => $this->getTitleAttribute(), 'image' => $this->image, 'items' => $items];
	}
	public function getSmallTailwindChipsHtml(): string{
		$chips = HtmlHelper::getTailwindTags();
		$buttons = $this->getButtons();
		foreach($buttons as $button){
			$chips .= $button->getChipSmall();
		}
		return "
<div class=\"flex flex-wrap justify-center\">
$chips
</div>
";
	}
	public function getMaterialNavTabs(string $title, string $titleOfActiveTab = null): string{
		$buttons = $this->getButtons();
		$items = '';
		foreach($buttons as $b){
			$css = '';
			if($titleOfActiveTab === $b->getTitleAttribute()){
				$css = 'active';
			}
			$items .= $b->getMaterialNavTabItem($css);
		}
		return "
            <div class=\"nav-tabs-navigation\">
                <div class=\"nav-tabs-wrapper\">
                    <span class=\"nav-tabs-title\">$title:</span>
                    <ul class=\"nav nav-tabs\" data-tabs=\"tabs\">
                        $items
                    </ul>
                </div>
            </div>
        ";
	}
	public function getListWithCountBadgesHtml(): string{
		$str = "";
		$buttons = $this->getButtons();
		foreach($buttons as $button){
			$link = $button->getBadgeListItem();
			$str .= $link;
		}
		return $str;
	}
	/** @noinspection PhpUnused */
	public function getAvatarBadgesListBoxHtml(): string{
		$img = $this->getImage();
		$name = $this->getTitleAttribute();
		$description = $this->getTooltip();
		$relationships = $this->getListWithCountBadgesHtml();
		return "
        <div class=\"box box-widget widget-user-2\">
            <!-- Add the bg color to the header using any of the bg-* classes -->
            <div class=\"widget-user-header bg-yellow\">
              <div class=\"widget-user-image\">
                <img class=\"img-circle\" src=\"$img\" alt=\"Avatar\">
              </div>
              <!-- /.widget-user-image -->
              <h3 class=\"widget-user-username\" style='line-height: 1;'>$name</h3>
              <h5 class=\"widget-user-desc\">$description</h5>
            </div>
            <div class=\"box-footer no-padding\">
              <ul class=\"nav nav-stacked\">
                $relationships
              </ul>
            </div>
        </div>
        ";
	}
	/**
	 * @return static
	 */
	public static function instance(): QMMenu{
		return new static();
	}
	public function getButtonByTitle(string $title): ?QMButton{
		$title = strtolower($title);
		$buttons = $this->getButtons();
		foreach($buttons as $b){
			if(strtolower($title) === strtolower($b->getTitleAttribute())){
				return $b;
			}
		}
		foreach($buttons as $b){
			if(stripos($b->getTitleAttribute(), $title) !== false){
				return $b;
			}
		}
		return null;
	}
	public function getMaterialMenu(): string{
		$html = '';
		foreach($this->getButtons() as $b){
			$html .= $b->getMaterialNavItem();
		}
		return $html;
	}
	public static function generateMaterialMenu(): string{
		$m = self::generateMenu();
		return $m->getMaterialMenu();
	}
	public static function generateMenu(): QMMenu{
		if(QMAuth::isAdmin()){
			return new AdminRoutesMenu();
		}
		if(QMAuth::getQMUser()){
			return new DataLabIndexRoutesMenu();
		}
		return new GuestMenu();
	}
	/**
	 * @return QMButton[]
	 */
	public static function buttons(): array{
		$m = new static();
		$buttons = $m->getButtons();
		return $buttons;
	}
	public function getListItems(string $liClass = "", string $aClass = ""): string{
		$html = "";
		$i = new static();
		$buttons = $i->getButtons();
		foreach($buttons as $b){
			$html .= $b->getListItem($liClass, $aClass);
		}
		return $html;
	}
	public function getLinksHtml(string $aClass = "", string $style = "",
		string $glue = "\n<span class=\"px-1\">&middot;</span>\n"): string{
		$html = "";
		$i = new static();
		$buttons = $i->getButtons();
		foreach($buttons as $b){
			$b->setClasses([$aClass]);
			$b->setStyles($style);
			$html .= $b->getLink() . $glue;
		}
		return $html;
	}
	public function getLinks(): array{
		$links = [];
		$i = new static();
		$buttons = $i->getButtons();
		foreach($buttons as $b){
			$links[$b->getTitleAttribute()] = $b->getUrl();
		}
		return $links;
	}
	public function getUnOrderedList(): string{
		$items = $this->getListItems();
		$id = $this->getId();
		return "
<ul id=\"$id\">
   $items
</ul>
        ";
	}
	public function getHorizontalList(): string{
		$items = $this->getListItems();
		$id = $this->getId();
		return "
<ul id=\"$id\" class=\"nav navbar-nav\" style=\"flex-direction: row;\">
   $items
</ul>
        ";
	}
	public function getTailwindHorizontalLinks(): string{
		return $this->getLinksHtml("dim no-underline p-4 text-80", "display: inline-block;");
	}
	public function getUnOrderedListWithDescriptionAndLink(): string{
		$items = "";
		$i = new static();
		$buttons = $i->getButtons();
		foreach($buttons as $b){
			$items .= $b->getListItemWithDescriptionAndUrl();
		}
		$id = $this->getId();
		return "
<ul id=\"$id\">
   $items
</ul>
        ";
	}
	public function getW3AvatarList(): string{
		$chips = "";
		$buttons = $this->getButtons();
		foreach($buttons as $button){
			$chips .= $button->getW3ListItem();
		}
		/** @noinspection JSUnresolvedLibraryURL */
		return "
            <link rel=\"stylesheet\" href=\"https://www.w3schools.com/w3css/4/w3.css\">
            <div class=\"w3-container\">
              <h2>Avatar List</h2>
              <p>You can combine w3-ul and the w3-bar classes to create an avatar list:</p>
              <ul class=\"w3-ul w3-card-4\">
                $chips
              </ul>
            </div>
        ";
	}
	public static function saveMaterialStatCards(string $style = 'display: none;'){
		$html = "<!-- Start generated by " . static::class . "::" . __FUNCTION__ . " -->\n";
		$html .= (new static())->getMaterialStatCards($style);
		$html .= "<!-- End generated by " . static::class . "::" . __FUNCTION__ . " -->\n";
		$slug = static::getSlugifiedClassName() . "-material-cards-" . QMStr::slugify($style);
		$html = str_replace(\App\Utils\Env::getAppUrl(), "{{ \App\Utils\Env::getAppUrl() }}", $html);
		FileHelper::writeByFilePath("resources/views/$slug.blade.php", $html);
	}
	public function addButton(QMButton $button): void{
		$t = $button->getTitleAttribute();
		if(isset($this->buttons[$t])){
			QMLog::exceptionIfTesting("$t button already set!");
		}
		$this->buttons[$t] = $button;
	}
	public function chips(): string{
		$html = "";
		$buttons = $this->getButtons();
		foreach($buttons as $button){
			$html .= $button->getChipSmall();
		}
		return $html;
	}
	public static function getAstralMenu(): TopLevelResource{
		$menu = new static();
		return TopLevelResource::make([
			'label' => $menu->getTitleAttribute(),
			'expanded' => $menu->isExpanded(),
			'badge' => $menu->getBadge(),
			'remember_menu_state' => $menu->isRememberMenuState(),
			'icon' => $menu->getFontAwesomeHtml(),
			'resources' => $menu->getNovMenuItems(),
		]);
	}
	/**
	 * @return array
	 * You can override this and add custom resources as described at
	 * https://astralpackages.com/packages/digital-creative/collapsible-resource-manager
	 */
	public function getNovMenuItems(): array{
		$resources = [];
		foreach($this->getButtons() as $button){
			try {
				$resources[] = $button->getAstralMenuItem();
			} catch (\Throwable $e) {
				QMLog::info(__METHOD__.": ".$e->getMessage());
				$resources[] = $button->getAstralMenuItem();
			}
		}
		return $resources;
	}
	/**
	 * @return bool
	 */
	public function isExpanded(): bool{
		return $this->expanded;
	}
	/**
	 * @param bool $expanded
	 */
	public function setExpanded(bool $expanded): void{
		$this->expanded = $expanded;
	}
	/**
	 * @return string
	 */
	public function getBadge(): ?string{
		return $this->badge;
	}
	/**
	 * @param string $badge
	 */
	public function setBadge(string $badge): void{
		$this->badge = $badge;
	}
	/**
	 * @return bool
	 */
	public function isRememberMenuState(): bool{
		return $this->rememberMenuState;
	}
	/**
	 * @param bool $rememberMenuState
	 */
	public function setRememberMenuState(bool $rememberMenuState): void{
		$this->rememberMenuState = $rememberMenuState;
	}
	/**
	 * @return string
	 */
	public function getFontAwesomeHtml(): string{
		return FontAwesome::html($this->getFontAwesome());
	}
	public function setTitle(string $title): QMMenu{
		$this->title = $title;
		return $this;
	}
	public function getHtml(): string{
		return $this->toHtml();
	}
	public function getUrls(): array{
		$urls = [];
		foreach($this->getButtons() as $button){
			$urls[] = $button->getUrl();
		}
		return $urls;
	}
	public function logUrls(){
		$arr = [];
		foreach($this->getButtons() as $button){
			$arr[] = $button->getTitleAttribute() . " => " . $button->getUrl();
		}
		QMLog::logBox($this->getTitleAttribute(), implode("\n", $arr));
	}
	public function testUrls(){
		foreach($this->getButtons() as $button){
			$button->testUrl();
		}
	}
	/**
	 * @return static
	 */
	public static function get(): self{
		return new static();
	}
	public function getUrl(): string{
		return qm_url("menus", ['class'=> static::class]);
	}
}
