<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\DataLab\Purchases;
use App\Buttons\QMButton;
use App\Models\Purchase;
class ListPurchasesButton extends QMButton {
	public $accessibilityText = 'List Purchases';
	public $action = 'datalab/purchases';
	public $color = Purchase::COLOR;
	public $fontAwesome = Purchase::FONT_AWESOME;
	public $id = 'datalab-purchases-button';
	public $image = Purchase::DEFAULT_IMAGE;
	public $link = 'datalab/purchases';
	public $parameters = [
		'middleware' => [
			'web',
			'auth',
		],
		'as' => 'datalab.purchases.index',
		'uses' => 'App\\Http\\Controllers\\DataLab\\PurchaseController@index',
		'controller' => 'App\\Http\\Controllers\\DataLab\\PurchaseController@index',
		'namespace' => 'App\\Http\\Controllers',
		'prefix' => '/datalab',
		'where' => [],
	];
	public $target = 'self';
	public $text = 'List Purchases';
	public $title = 'List Purchases';
	public $tooltip = Purchase::CLASS_DESCRIPTION;
	public $visible = true;
}
