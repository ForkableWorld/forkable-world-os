<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\AppSettings\AppDesign\Menu;
use LogicException;
use App\AppSettings\AppDesign;
use App\AppSettings\AppSettings;
class MenuSettings extends AppDesign\AppDesignSettings {
    /**
     * Menu constructor.
     * @param AppSettings $appSettings
     */
    public function __construct($appSettings = null){
        if(!isset($appSettings->appDesign->menu)){
            $this->logError("No appSettings->appDesign->menu");
            return;
        }
        $this->active = self::processSingleMenu($appSettings->appDesign->menu->active);
        if(!isset($appSettings->appDesign->menu->custom)){
            $appSettings->appDesign->menu->custom = $this->active;
        }
        $this->custom = self::processSingleMenu($appSettings->appDesign->menu->custom);
        $this->type = $appSettings->appDesign->menu->type ?? "custom";
        $this->active = AppDesign::removeNullItemsFromArray($this->active);
        $this->custom = AppDesign::removeNullItemsFromArray($this->custom);
    }
    /**
     * @param $unprocessedMenuItems
     * @return mixed
     */
    public static function processSingleMenu($unprocessedMenuItems){
        $processedMenuItems = [];
        /** @var MenuItem $unprocessedMenuItem */
        foreach($unprocessedMenuItems as $unprocessedMenuItem){
            $processedMenuItems[] = new MenuItem($unprocessedMenuItem);
        }
        return $processedMenuItems;
    }
    /**
     * @param [] $menu
     */
    public static function checkMenu($menu){
        if(count($menu) < 2){
            le("Only ".count($menu)." menu items!");
        }
        foreach($menu as $menuItem){
            MenuItem::checkMenuItem($menuItem);
        }
    }
}
