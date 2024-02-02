<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons;
use App\Menus\Routes\DevRoutesMenu;
use App\Models\BaseModel;
use App\Properties\User\UserIdProperty;
use App\Types\QMStr;
use App\Utils\QMRoute;
class RouteButton extends QMButton {
	protected $route;
	public function __construct(QMRoute $route){
		$this->route = $route;
		parent::__construct($route->getTitleAttribute());
		$this->setUrl($route->uri, [], true); // Don't use getUrl or it adds random host origins
		$this->setImage($route->getImage());
		$this->setParameters($route->action);
		$this->setTarget("self");
		$this->setTooltip($route->getTooltip());
		$this->setVisibility($route->getVisibility());
		$this->setFontAwesome($route->getFontAwesome());
		$this->setBackgroundColor($route->getBackgroundColor());
		$this->setId(QMStr::slugify($route->uri) . '-button');
		$this->userId = UserIdProperty::USER_ID_SYSTEM;
		if(!$this->title){
			$this->getTitleAttribute();
			ddd($route);
			throw new \LogicException("no title!");
		}
	}
	public function getModel(): ?BaseModel{
		return $this->getRoute()->getModel();
	}
	/**
	 * @return QMRoute
	 */
	public function getRoute(): QMRoute{
		return $this->route;
	}
	public function getModelClass(): ?string{
		if(!$this->getModel()){
			return null;
		}
		return get_class($this->getModel());
	}
	/**
	 * @inheritDoc
	 */
	public static function getHardCodedDirectory(): string{
		$folder = QMButton::BUTTONS_FOLDER;
		$url = (new static())->link;
		$url->link = QMStr::after('localhost/', $url->link, $url->link);
		$arr = explode('/', $url->link);
		$sub = $arr[0];
		if($sub === "datalab"){
			$title = QMStr::routeToTitle($arr[1]);
			$sub = "DataLab/" . QMStr::toClassName($title);
		} elseif($sub === "api"){
			$sub = "API/" . QMStr::toClassName($arr[2]);
		} else{
			$sub = ucfirst($sub) . "Routes";
		}
		return $folder . '/' . $sub;
	}
	public static function generateDevRouteButtons(){
		$routes = DevRoutesMenu::getRoutes();
		foreach($routes as $r){
			$b = $r->getButton();
			$b->saveHardCodedModel();
		}
	}
}
