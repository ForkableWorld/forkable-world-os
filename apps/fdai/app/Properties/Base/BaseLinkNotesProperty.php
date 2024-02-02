<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
class BaseLinkNotesProperty extends BaseNoteProperty
{
	public $canBeChangedToNull = true;
	public $dbInput = 'text,16777215:nullable';
	public $dbType = 'text';
	public $default = 'undefined';
	public $description = 'Notes about the link.';
	public $example = '{\"ionIcon\":\"ion-star\",\"name\":\"app.asNeededMeds\",\"title\":\"As Needed Meds\",\"url\":\"\\/as-needed-meds\",\"visible\":null,\"image\":null,\"description\":null,\"params\":{\"showAds\":true,\"title\":\"As Needed Meds\",\"variableCategoryName\":\"Treatments\",\"ionIcon\":\"ion-star\"},\"views\":{\"menuContent\":{\"templateUrl\":\"templates\\/favorites.html\",\"controller\":\"FavoritesCtrl\"}},\"showAds\":true,\"variableCategoryName\":\"Treatments\"}';
	public $fieldType = 'text';
	public $fontAwesome = FontAwesome::LINK;
	public $htmlInput = 'textarea';
	public $htmlType = 'textarea';
	public $image = ImageUrls::LINK;
	public $importance = false;
	public $isOrderable = false;
	public $isSearchable = true;
	public $maxLength = 16777215;
	public $name = self::NAME;
	public const NAME = 'link_notes';
            	public $order = '99';
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable|max:16777215';
	public $showOnDetail = true;
	public $title = 'Link Notes';
	public $type = PhpTypes::STRING;
	public $validations = 'nullable|max:16777215';

}
