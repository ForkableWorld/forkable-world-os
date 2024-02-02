<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Admin;
use App\Buttons\AdminButton;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
class VariableSearchButton extends AdminButton {
	public $accessibilityText = 'Variable Search Button';
	public $action = 'variables';
	public $color = '#3467d6';
	public $fontAwesome = FontAwesome::SEARCH_SOLID;
	public $id = 'admin-variable-search-button';
	public $image = ImageUrls::BASIC_FLAT_ICONS_SEARCH;
	public $link = 'variables';
	public $target = 'self';
	public $text = 'Variable Search';
	public $title = 'Variable Search';
	public $tooltip = 'Search for a variable such as a symptom or treatment.';
	public $visible = true;
	public function __construct(){
		parent::__construct();
		$this->setUrl(qm_url('variables'));
	}
}
