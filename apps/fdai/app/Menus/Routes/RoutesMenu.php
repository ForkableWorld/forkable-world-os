<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Menus\Routes;
use App\Buttons\QMButton;
use App\Menus\QMMenu;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use App\Utils\QMRoute;
use function le;
class RoutesMenu extends QMMenu {
	public function getTitleAttribute(): string{ return "Available Routes"; }
	public function getImage(): string{ return ImageUrls::ADMIN; }
	public function getFontAwesome(): string{ return FontAwesome::ADMIN; }
	public function getTooltip(): string{ return "Search for an administrative resource..."; }
	/**
	 * @return QMButton[]
	 */
	public function getButtons(): array{
		$routes = static::getRoutes();
		$buttons = [];
		foreach($routes as $route){
			$name = $route->getName();
			if($name && str_contains($name, '.store') !== false){
				continue; // TODO: Maybe create form buttons
			}
			$uri = $route->uri;
			if($uri && str_contains($uri, '{') ){
				continue; // TODO: Maybe start creating these if necessary?
			}
			$b = $route->getButton();
			$buttons[$b->getTitleAttribute()] = $b;
		}
		ksort($buttons); // Keep same order for test html comparisons
		$this->addButtons($buttons);
		if(!$this->buttons){
			le("no buttons on " . static::class);
		}
		return $this->buttons;
	}
	/**
	 * @return QMRoute[]
	 */
	public static function getRoutes(): array{
		$routes = QMRoute::getRoutes();
		$filtered = QMRoute::filterRoutesLike($routes, [
			'_debugbar',
			'.css',
			'{',
			'.js',
		]);
		$authorizedRoutes = QMRoute::filterRoutesBasedOnAuthentication($filtered);
		return $authorizedRoutes;
	}
}
