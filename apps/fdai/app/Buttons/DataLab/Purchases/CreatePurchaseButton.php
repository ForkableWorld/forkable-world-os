<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\DataLab\Purchases;
use App\Buttons\QMButton;
use App\Models\Purchase;
class CreatePurchaseButton extends QMButton {
	public $accessibilityText = 'Create Purchase';
	public $action = 'datalab/purchases/create';
	public $color = Purchase::COLOR;
	public $fontAwesome = Purchase::FONT_AWESOME;
	public $id = 'datalab-purchases-create-button';
	public $image = Purchase::DEFAULT_IMAGE;
	public $link = 'datalab/purchases/create';
	public $parameters = [
		'middleware' => [
			'web',
			'auth',
		],
		'as' => 'datalab.purchases.create',
		'uses' => 'App\\Http\\Controllers\\DataLab\\PurchaseController@create',
		'controller' => 'App\\Http\\Controllers\\DataLab\\PurchaseController@create',
		'namespace' => 'App\\Http\\Controllers',
		'prefix' => '/datalab',
		'where' => [],
	];
	public $target = 'self';
	public $text = 'Create Purchase';
	public $title = 'Create Purchase';
	public $tooltip = Purchase::CLASS_DESCRIPTION;
	public $visible = true;
}
