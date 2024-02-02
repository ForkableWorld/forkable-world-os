<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Logging\QMLog;
use App\Traits\PropertyTraits\IsString;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
use stdClass;
class BaseUpcOneTwoProperty extends BaseProperty {
	use IsString;
	public $dbInput = 'string,255:nullable';
	public $dbType = PhpTypes::STRING;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'upc_12';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::QUESTION_CIRCLE;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::BUSINESS_COLLECTION_CART_12;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $maxLength = 255;
	public $name = self::NAME;
	public const NAME = 'upc_12';
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable|max:255';
	public $title = 'Upc 12';
	public $type = PhpTypes::STRING;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|max:255';
	public function authorizeUpdate(): void{ $this->authorizeIfLoggedIn(); }
	/**
	 * @param string $upc
	 * @return bool
	 */
	public static function searchWalgreensByUpc(string $upc = "0373127-59382"){
		$upc = str_replace("%", "", $upc);
		if(!ctype_digit($upc)){
			QMLog::info("$upc is not a numeric upc");
			return false;
		}
		$postBody = new stdClass();
		$postBody->affId = "YOUR AFFILIATE ID";
		$postBody->token = "ACCESS TOKEN FROM ABOVE";
		$postBody->lng = "YOUR AFFILIATE ID";
		$postBody->rxNo = "YOUR AFFILIATE ID";
		$postBody->appCallBackScheme = "YOUR APP CALLBACK URI SCHEME";
		$postBody->appCallBackAction = "YOUR APP CALLBACK ACTION";
		$postBody->trackingId = "YOUR OWN TRACKING ID";
		$postBody->appId = "refillByScan";
		$postBody->act = "chkExpRx";
		$postBody->devinf = "YOUR AFFILIATE ID";
		$postBody->appver = "YOUR AFFILIATE ID";
		//APIHelper::makePostRequest()
		return $postBody;
	}
}
