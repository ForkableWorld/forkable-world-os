<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Products;
use App\DataSources\Connectors\AmazonConnector;
use App\Logging\QMLog;
use App\VariableCategories\PaymentsVariableCategory;
use App\Variables\QMVariableCategory;
/** Class AmazonProduct
 * @package App\Slim\Model\Amazon
 */
class AmazonProduct extends Product {
    /**
     * AmazonProduct constructor.
     * @param array $productItem
     * @param null $originalSearchTerm
     * @param array $providedVariableParameters
     */
    public function __construct($productItem, $originalSearchTerm = null, $providedVariableParameters = []){
        $this->clientId = AmazonConnector::NAME;
        parent::__construct($productItem, $originalSearchTerm, $providedVariableParameters);
    }
    /**
     * @return string
     */
    public function getVariableDescription(): string{
        if($this->getVariableName() !== $this->getItemTitle()){
            return $this->getItemTitle();
        }
        if(isset($this->productItem['ItemAttributes']['Feature'][0])){
            return $this->productItem['ItemAttributes']['Feature'][0];
        }
        $this->logError("No ItemAttributes']['Feature for description!");
        le("No ItemAttributes']['Feature for description!", $this);throw new \LogicException();
    }
    /**
     * @return mixed
     */
    public function getProductUrl(){
        $this->productUrl = $this->getProductItem()['DetailPageURL'];
        return $this->productUrl;
    }
    /**
     * @return string
     */
    public function setImageUrl(){
        if(!isset($this->productItem['MediumImage'])){
            QMLog::info("MediumImage not set!", ['amazon_item' => $this->productItem]);
            return false;
        }
        return $this->imageUrl = $this->productItem['MediumImage']['URL'];
    }
    /**
     * @return float
     */
    public function getPrice(){
        $amazonItem = $this->getProductItem();
        if(isset($amazonItem['ItemAttributes']['ListPrice']['Amount'])){
            $this->price = (int)$amazonItem['ItemAttributes']['ListPrice']['Amount'] / 100;
        }elseif(isset($amazonItem['OfferSummary']['LowestNewPrice']['Amount'])){
            $this->price = (int)$amazonItem['OfferSummary']['LowestNewPrice']['Amount'] / 100;
        }
        return $this->price;
    }
    /**
     * @return string
     */
    protected function setVariableCategoryName(): string{
        $amazonItem = $this->getProductItem();
        $amazonCategory = null;
        if(isset($amazonItem['ItemAttributes']['ProductGroup'])){
            $amazonCategory = $amazonItem['ItemAttributes']['ProductGroup'];
            $variableCategoryObject = QMVariableCategory::findByNameOrSynonym($amazonCategory, false);
            if($variableCategoryObject){
                $this->variableCategoryName = $variableCategoryObject->name;
            }
        }
        if(isset($amazonItem['ItemAttributes']['Binding'])){
            $amazonCategory = $amazonItem['ItemAttributes']['Binding'];
            $variableCategoryObject = QMVariableCategory::findByNameOrSynonym($amazonCategory, false);
            if($variableCategoryObject){
                $this->variableCategoryName = $variableCategoryObject->name;
            }
        }
        if(!$this->variableCategoryName && parent::setVariableCategoryName()){
            return parent::setVariableCategoryName();
        }
        if(!$this->variableCategoryName){
            QMLog::error("No variable category name for $amazonCategory. Using Payments...");
            $this->variableCategoryName = PaymentsVariableCategory::NAME;
        }
        return $this->variableCategoryName;
    }
    /**
     * @return string[]
     */
    public function getFeatures(): array{
        if(isset($this->productItem['ItemAttributes']['Feature'])){
            $features = $this->productItem['ItemAttributes']['Feature'];
            if(!is_array($features)){
                $features = [$features];
            }
            return $features;
        }
        return [];
    }
    /**
     * @return string
     */
    public function getItemTitle(): string{
        if(isset($this->getProductItem()['ItemAttributes']['Title'])){
            return $this->getProductItem()['ItemAttributes']['Title'];
        }
        le("could not get item title", $this);throw new \LogicException();
    }
    /**
     */
    public function addNodeParentsAsParentTagVariables(){
        $amazonItem = $this->getProductItem();
        if(isset($amazonItem['BrowseNodes']['BrowseNode'])){
            $this->addParentIfNodeIsNotRoot($amazonItem['BrowseNodes']['BrowseNode']);
        }
        if(isset($amazonItem['BrowseNodes']['BrowseNode']['Ancestors']['BrowseNode']['Ancestors']['BrowseNode'])){
            $this->addParentIfNodeIsNotRoot($amazonItem['BrowseNodes']['BrowseNode']['Ancestors']['BrowseNode']['Ancestors']['BrowseNode']);
        }
        if(isset($amazonItem['BrowseNodes']['BrowseNode']['Ancestors']['BrowseNode']['Ancestors']['BrowseNode']['Ancestors']['BrowseNode'])){
            $this->addParentIfNodeIsNotRoot($amazonItem['BrowseNodes']['BrowseNode']['Ancestors']['BrowseNode']['Ancestors']['BrowseNode']['Ancestors']['BrowseNode']);
        }
    }
    /**
     * @param array $nodes
     */
    private function addParentIfNodeIsNotRoot($nodes){
        if(!isset($nodes[0])){
            $nodes = [$nodes];
        }
        foreach($nodes as $node){
            if(!isset($node['IsCategoryRoot'])){
                if(!isset($node['Name'])){
                    QMLog::error("No node name!");
                    return;
                }
                $this->addParentCommonTag($node['Name']);
            }
        }
    }
    public function createTagVariables(){
        $this->addNodeParentsAsParentTagVariables();
        // $this->addNodeChildrenAsParentTagVariables();  // Don't add children because they're just sibling categories that are unrelated
        // Tricycles, Scooters & Wagons is a child of Spy Guy 10 Piece Toy Secret Mission Set With Look Around Camera for instance
    }
    /**
     */
    private function addNodeChildrenAsParentTagVariables(){
        // Don't add children because they're just sibling categories that are unrelated
        // Tricycles, Scooters & Wagons is a child of Spy Guy 10 Piece Toy Secret Mission Set With Look Around Camera for instance
        $amazonItem = $this->getProductItem();
        if(isset($amazonItem['BrowseNodes']['BrowseNode']['Children']['BrowseNode'])){
            foreach($amazonItem['BrowseNodes']['BrowseNode']['Children']['BrowseNode'] as $child){
                if(!isset($child['Name'])){
                    QMLog::error("No child name in " . json_encode($child));
                    continue;
                }
                $this->addParentCommonTag($child['Name']);
            }
        }
    }
    /**
     * @return mixed
     */
    public function setBrand(){
        $productItem = $this->getProductItem();
        if(isset($productItem['ItemAttributes']['Brand'])){
            $this->brand = $productItem['ItemAttributes']['Brand'];
        }
        return $this->brand;
    }
    /**
     * @return string
     */
    public function getBarcode(){
        if(!isset($this->getProductItem()['ItemAttributes']['UPC'])){
            if(isset($this->getProductItem()["ASIN"])){
                QMLog::error("No barcode from Amazon for " . $this->getVariableName() . ". Falling back to ASIN");
                return $this->getProductItem()["ASIN"];
            }
            QMLog::error("No barcode from Amazon for " . $this->getVariableName());
            return false;
        }
        return $this->getProductItem()['ItemAttributes']['UPC'];
    }
	public function __toString(){
		return $this->getItemTitle();
	}
}
