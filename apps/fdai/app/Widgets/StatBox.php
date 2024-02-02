<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Widgets;
use App\Models\BaseModel;
class StatBox extends BaseWidget
{
    /**
     * Treat this method as a controller action.
     * Return view() or other content to display.
     */
    public function run(){
        $this->populateFromConfigData();
        $count = $this->getQueryCount();
        return view('widgets.stat_box', [
            'config' => $this->config,
            'name' => $this->config['name'],
            'url' => $this->config['url'],
            'tooltip' =>  $this->config['tooltip'],
            'number' => $count,
            'icon' => $this->config['icon'],
            'color' => BaseModel::COLOR,
        ]);
    }
    public function getLoadingText(): string {
        $title = $this->getTitleAttribute();
        return "Calculating $title...";
    }
}
