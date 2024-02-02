<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Links;
use App\Buttons\QMButton;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
class WordPressPluginButton extends QMButton {
	public $fontAwesome = FontAwesome::WORDPRESS;
	public $image = ImageUrls::BACKGROUNDS_CIRCLE_WORDPRESS;
	public $link = "https://wordpress.org/plugins/quantimodo";
	public $title = 'WordPress Plugin';
	public $tooltip = "The WordPress Plugin facilitates the democratization of clinical research by allowing anyone to create their own research platform.  It allows users to record, aggregate, analyze and visualize their health and life-tracking data.  The data collection, analysis and visualization functionality may be included in any page or post.";
}
