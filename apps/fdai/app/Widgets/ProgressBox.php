<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Widgets;
class ProgressBox extends BaseWidget
{
    /**
     * Treat this method as a controller action.
     * Return view() or other content to display.
     */
    public function run(){
        $this->populateFromConfigData();
        $whereString = $this->getHumanizedWhereString();
        $count = $this->getQueryCount();
        $all = $this->getTableCount();
        if($all){
            $percent = round($count/$all*100);
            $description = "$percent% $whereString";
        } else {
            $percent = 0;
            $description = "None $whereString";
        }
        $title = $this->getTitleAttribute();
        $icon = $this->getIcon();
        $bootstrapColor = $this->getColorString();
        return "
        <a href='$this->url'>
            <!-- Apply any bg-* class to to the info-box to color it -->
                <div class=\"info-box bg-$bootstrapColor\">
                    <span class=\"info-box-icon\"><i class=\"$icon\"></i></span>
                    <div class=\"info-box-content\">
                        <span class=\"info-box-text\">$title</span>
                        <span class=\"info-box-number\">$count out of $all</span>
                        <!-- The progress section is optional -->
                        <div class=\"progress\">
                            <div class=\"progress-bar\" style=\"width: $percent%\"></div>
                        </div>
                        <span class=\"progress-description\">
                          $description
                        </span>
                    </div>
                    <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
            </a>
        ";
    }
    public function getLoadingText(): string {
        $title = $this->getTitleAttribute();
        return "Calculating $title...";
    }
}
