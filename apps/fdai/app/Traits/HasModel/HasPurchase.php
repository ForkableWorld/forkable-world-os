<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\HasModel;
use App\Buttons\QMButton;
use App\Models\BaseModel;
use App\Models\Purchase;
use App\Properties\BaseProperty;
use App\Slim\Model\DBModel;
trait HasPurchase {
	public function getPurchaseId(): int{
		$nameOrId = $this->getAttribute('purchase_id');
		return $nameOrId;
	}
	public function getPurchaseButton(): QMButton{
		$purchase = $this->getPurchase();
		if($purchase){
			return $purchase->getButton();
		}
		return Purchase::generateShowButton($this->getPurchaseId());
	}
	/**
	 * @return Purchase
	 */
	public function getPurchase(): Purchase{
		if($this instanceof BaseProperty && $this->parentModel instanceof Purchase){
			return $this->parentModel;
		}
		/** @var BaseModel|DBModel $this */
		if($l = $this->getRelationIfLoaded('purchase')){
			return $l;
		}
		$id = $this->getPurchaseId();
		$purchase = Purchase::findInMemoryOrDB($id);
		if(property_exists($this, 'relations')){
			$this->relations['purchase'] = $purchase;
		}
		if(property_exists($this, 'purchase')){
			$this->purchase = $purchase;
		}
		return $purchase;
	}
}
