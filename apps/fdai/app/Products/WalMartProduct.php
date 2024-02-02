<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Products;
use App\Logging\QMLog;
use App\Types\QMStr;
use App\VariableCategories\PaymentsVariableCategory;
use App\Variables\QMVariableCategory;
use WalmartApiClient\Entity\AbstractEntityInterface;
use WalmartApiClient\Entity\ProductInterface;

/** Class WalMartProduct
 * @package App\Products
 */
class WalMartProduct extends Product {
    /**
     * AmazonProduct constructor.
     * @param AbstractEntityInterface|ProductInterface $productItem
     * @param null $originalSearchTerm
     * @param array $providedVariableParameters
     */
    public function __construct($productItem, $originalSearchTerm = null, $providedVariableParameters = []){
        $this->clientId = 'wal-mart';
        parent::__construct($productItem, $originalSearchTerm = null, $providedVariableParameters = []);
    }
    /**
     * @return string
     */
    public function getVariableDescription(){
        if($this->getVariableName() !== $this->getItemTitle()){
            return $this->getItemTitle();
        }
        $productItem = $this->getProductItem();
        if($productItem->getLongDescription()){
            return $productItem->getLongDescription();
        }
    }
    /**
     * @return mixed
     */
    public function getProductUrl(){
        $productItem = $this->getProductItem();
        $this->productUrl = $productItem->getAffiliateAddToCartUrl();
        return $this->productUrl;
    }
    /**
     * @return string
     */
    public function setImageUrl(){
        $productItem = $this->getProductItem();
        if(!$productItem->getMediumImage()){
            QMLog::info("MediumImage not set!", ['amazon_item' => $this->productItem]);
            return false;
        }
        return $this->imageUrl = $productItem->getMediumImage();
    }
    /**
     * @return float
     */
    public function getPrice(){
        $this->price = $this->getProductItem()->getSalePrice();
        return $this->price;
    }
    /**
     * @param array $amazonItem
     * @return string
     */
    protected function setVariableCategoryName($amazonItem = null){
        if(parent::setVariableCategoryName()){
            return parent::setVariableCategoryName();
        }
        $retailerCategory = QMStr::before('/', $this->getProductItem()->getCategoryPath());
        $variableCategoryObject = QMVariableCategory::findByNameOrSynonym($retailerCategory, false);
        if($variableCategoryObject){
            $this->variableCategoryName = $variableCategoryObject->name;
        }
        if(!$this->variableCategoryName){
            QMLog::error("No variable category name for $retailerCategory. Using Payments...");
            $this->variableCategoryName = PaymentsVariableCategory::NAME;
        }
        return $this->variableCategoryName;
    }
    /**
     * @return string[]
     */
    public function getFeatures(){
        $features[0] = $this->getProductItem()->getLongDescription();
        $features[1] = $this->getProductItem()->getShortDescription();
        return $features;
    }
    /**
     * @return string
     */
    public function getItemTitle(){
        $name = $this->getProductItem()->getName();
        return $name;
    }
    public function createTagVariables(){
        $path = $this->getProductItem()->getCategoryPath();
        $path = QMStr::after('/', $path);
        $nodes = explode('/', $path);
        foreach($nodes as $node){
            $this->addParentCommonTag($node);
        }
    }
    /**
     * @return mixed
     */
    public function setBrand(){
        $productItem = $this->getProductItem();
        return $this->brand = $productItem->getBrandName();
    }
    /**
     * @return string
     */
    public function getBarcode(){
        $productItem = $this->getProductItem();
        return $productItem->getUpc();
    }
}
