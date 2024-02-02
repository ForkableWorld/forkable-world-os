<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Products;
use App\Exceptions\ExceptionHandler;
use App\Models\Variable;
use App\Properties\Base\BaseCombinationOperationProperty;
use App\Properties\Variable\VariableNameProperty;
use App\Slim\Model\QMUnit;
use App\Traits\LoggerTrait;
use App\Types\QMStr;
use App\Units\CountUnit;
use App\VariableCategories\FoodsVariableCategory;
use App\VariableCategories\MiscellaneousVariableCategory;
use App\VariableCategories\PaymentsVariableCategory;
use App\Variables\QMCommonVariable;
use App\Variables\QMUserVariable;
use App\Variables\QMVariableCategory;
use Exception;
use WalmartApiClient\Entity\AbstractEntityInterface;
use WalmartApiClient\Entity\ProductInterface;
/** Class AmazonProduct
 * @package App\Slim\Model\Amazon
 */
abstract class Product {
    use LoggerTrait;
    protected $imageUrl;
    protected $productUrl;
    protected $price;
    protected $variableName;
    protected $brand;
    protected $variableCategoryName;
    protected $unitId;
    protected $commonVariableWithActualProductName;
    protected $commonVariableWithOriginalSearchTermAsName;
    protected $productItem;
    protected $variableCategory;
    protected $unit;
    protected $originalSearchTerm;
    protected $providedVariableParameters;
    protected $clientId;
    /**
     * AmazonProduct constructor.
     * @param array|AbstractEntityInterface|ProductInterface $productItem
     * @param null $originalSearchTerm
     * @param array $providedVariableParameters
     */
    public function __construct($productItem, $originalSearchTerm = null, $providedVariableParameters = []){
        $this->providedVariableParameters = $providedVariableParameters;
        $this->originalSearchTerm = $originalSearchTerm;
        $this->productItem = $productItem;
        $this->setImageUrl();
        $this->getPrice();
        $this->setBrand();
        $this->logInfo("Instantiating Product: ".$this->getVariableName());
        $this->setVariableCategoryName();
        if(!$this->getQMCommonVariableWithActualProductName()){
            return false;
        }
        $this->createTagVariables();
    }
    /**
     * @return string
     */
    abstract public function getBarcode();
    /**
     * @return mixed
     */
    abstract public function setBrand();
    abstract public function createTagVariables();
    abstract public function getVariableDescription();
    /**
     * @return mixed
     */
    abstract public function getProductUrl();
    /**
     * @return string
     */
    abstract public function setImageUrl();
    /**
     * @return float
     */
    abstract public function getPrice();
    /**
     * @return string[]
     */
    abstract public function getFeatures();
    /**
     * @return string
     */
    abstract public function getItemTitle();
    /**
     * @return string
     */
    public function getImageUrl(){
        return $this->imageUrl ?: $this->setImageUrl();
    }
    /**
     * @return string
     */
    public function getVariableName(){
        if($this->variableName){
            return $this->variableName;
        }
        $variableName = $this->getItemTitle();
        //$variableName = VariableNameProperty::sanitizeVariableName($variableName, $this->getUnit()); // We should do this in the common variable function
        return $this->variableName = $variableName;
    }
    /**
     * @return string
     */
    protected function setVariableCategoryName(){
        if(isset($this->providedVariableParameters['variableCategoryName'])){
            $name = $this->providedVariableParameters['variableCategoryName'];
            if($name !== PaymentsVariableCategory::NAME && $name !== MiscellaneousVariableCategory::NAME){
                $this->variableCategoryName = $name;
            }
        }
        return $this->variableCategoryName;
    }
    /**
     * @return QMVariableCategory
     */
    protected function getQMVariableCategory(): ?QMVariableCategory {
        return $this->variableCategory = QMVariableCategory::findByNameOrSynonym($this->getVariableCategoryName());
    }
    /**
     * @return array|\WalmartApiClient\Entity\Product
     */
    public function getProductItem(){
        return $this->productItem;
    }
    /**
     * @return mixed
     */
    public function getVariableCategoryName(){
        return $this->variableCategoryName ?: $this->setVariableCategoryName();
    }
    /**
     * @return float
     */
    public function getDefaultValue(){
        $lowerCaseTitle = strtolower($this->getItemTitle());
        $defaultValue = $this->getUnit()->getNumberBeforeUnitNameOrAbbreviatedName($lowerCaseTitle);
        if($defaultValue && strpos($lowerCaseTitle, 'serving')){
            return $defaultValue;
        }
        $features = $this->getFeatures();
        foreach($features as $feature){
            if(strpos(strtolower($feature), 'serving size') !== false){
                $defaultValueNextToUnitName = $this->getUnit()
                    ->getNumberBeforeUnitNameOrAbbreviatedName($feature);  // Necessary for Serving Size: 1 5g Scoop (Included)
                if($defaultValueNextToUnitName){
                    return $defaultValueNextToUnitName;
                }
                $defaultValue = QMStr::getNumberFromStringWithLeadingSpaceOrAtBeginning($feature);  // Keep going in cause another feature has value next to unit name
            }
        }
        return $defaultValue;
    }
    /**
     * @return null|QMUnit
     */
    public function getUnit(){
        if($this->unit){
            return $this->unit;
        }
        return $this->unit = QMUnit::getUnitById($this->getUnitId());
    }
    /**
     * @return int
     */
    protected function getUnitId(){
        if($this->unitId){
            return $this->unitId;
        }
        if($this->getVariableCategoryName() === FoodsVariableCategory::NAME){
            return $this->unitId = $this->getQMVariableCategory()->defaultUnitId;
        }
        //        if($this->getItemTitle()){
        //            $countUnitFromTitle = Unit::getCountUnitFromString($this->getItemTitle());
        //            if($countUnitFromTitle){
        //                $this->unitId = $countUnitFromTitle->id;
        //                $this->logInfo("Using unit from title: ".$countUnitFromTitle->abbreviatedName);
        //                return $this->unitId;
        //            }
        //        }
        if(!$this->unitId){
            $this->unitId = $this->getQMVariableCategory()->getDefaultUnitId();
            if($this->unitId){
                $unit = QMUnit::getByNameOrId($this->unitId);
                $this->logInfo("Using category default unit: ".$unit->abbreviatedName);
            }
        }
        if(!$this->unitId){
            $this->logError("Unit id not set!");
            $this->unitId = QMUnit::getByNameOrId(CountUnit::NAME)->id;
        }
        return $this->unitId;
    }
    /**
     * @return bool|QMCommonVariable
     */
    public function setCommonVariableWithOriginalSearchTermAsName(){
        return $this->commonVariableWithOriginalSearchTermAsName = $this->setCommonVariable($this->originalSearchTerm);
    }
    /**
     * @return bool|QMCommonVariable
     */
    public function setCommonVariableWithActualProductName(){
        return $this->commonVariableWithActualProductName = $this->setCommonVariable($this->getVariableName());
    }
    /**
     * @param $name
     * @return bool|QMCommonVariable
     */
    public function setCommonVariable($name){
        $this->logInfo("Product setCommonVariable named ".$this->getVariableName());
        $params = $this->getNewVariableParametersForExactMatch();
        $commonVariable = QMCommonVariable::findOrCreateByName($name, $params);
        if(!$commonVariable){
            $this->logError("Could not create common variable for Amazon Product: ".$name);
            return $commonVariable = false;
        }
        $commonVariable->updatePriceIfNecessary($this->getPrice());
        $commonVariable->updateImageUrlIfNecessary($this->imageUrl, true);
        $commonVariable->updateProductUrlIfNecessary($this->getProductUrl());
        $commonVariable->updateUpcIfNecessary($this->getBarcode());
        //$this->commonVariable->updateDefaultValueIfNecessary($this->getDefaultValue());
        return $commonVariable;
    }
    /**
     * @return array
     */
    protected function getNewVariableParametersForParents(){
        $params = [
            'variableCategoryName'                      => $this->getVariableCategoryName(),
            Variable::FIELD_COMBINATION_OPERATION => BaseCombinationOperationProperty::COMBINATION_SUM,
            Variable::FIELD_DEFAULT_UNIT_ID       => $this->getUnitId(),
            Variable::FIELD_IS_PUBLIC                => 1,
            //Common\App\Models\Variable::FIELD_UPC_14 => $this->getBarcode()  // Don't want this for parents
        ];
        $params[Variable::FIELD_PRODUCT_URL] = $this->getProductUrl();
        $params[Variable::FIELD_CLIENT_ID] = $this->clientId;
        return $params;
    }
    /**
     * @return array
     */
    public function getNewVariableParametersForExactMatch(){
        $params = $this->getNewVariableParametersForParents();
        $params[Variable::FIELD_UPC_14] = $this->getBarcode();
        //if($this->getDefaultValue()){$params[CommonVariable::FIELD_DEFAULT_VALUE] = $this->getDefaultValue();}
        if($this->getVariableDescription()){
            $params[Variable::FIELD_DESCRIPTION] = $this->getVariableDescription();
        }
        if($this->originalSearchTerm){
            $params[Variable::FIELD_SYNONYMS] = [$this->originalSearchTerm];
            if($this->getVariableName() !== $this->getItemTitle()){
                $params[Variable::FIELD_SYNONYMS][] = $this->getItemTitle();
            }
        }
        $params[Variable::FIELD_PRODUCT_URL] = $this->getProductUrl();
        $params[Variable::FIELD_IMAGE_URL] = $this->getImageUrl();
        $params[Variable::FIELD_PRICE] = $this->getPrice();
        foreach($this->providedVariableParameters as $key => $value){
            if($value && $value !== ""){
                $params[$key] = $value;
            }
        }
        return $params;
    }
    /**
     * @return QMCommonVariable
     */
    public function getQMCommonVariableWithActualProductName(){
        return $this->commonVariableWithActualProductName ?: $this->setCommonVariableWithActualProductName();
    }
    /**
     * @return QMCommonVariable
     */
    public function getCommonVariableWithOriginalSearchTermAsName(){
        return $this->commonVariableWithOriginalSearchTermAsName ?: $this->setCommonVariableWithOriginalSearchTermAsName();
    }
    /**
     * @param int $userId
     * @return QMUserVariable
     */
    public function getUserVariable($userId){
        return $this->getQMCommonVariableWithActualProductName()->findQMUserVariable($userId);
    }
    /**
     * @param string $parentVariableName
     * @return bool|int
     */
    protected function addParentCommonTag($parentVariableName){
        $this->addParentCommonTagForSpendingVariable($parentVariableName);
        try {
            return $this->getQMCommonVariableWithActualProductName()
                ->addParentCommonTag($parentVariableName, $this->getNewVariableParametersForParents());
        } catch (Exception $e) {
            ExceptionHandler::logExceptionOrThrowIfLocalOrPHPUnitTest($e);
            return false;
        }
    }
    /**
     * @param string $parentVariableName
     * @return bool|int
     */
    protected function addParentCommonTagForSpendingVariable($parentVariableName){
        try {
            $parentVariableName = VariableNameProperty::toSpending($parentVariableName);
            $newParams = $this->getNewVariableParametersForParents();
            $newParams[Variable::FIELD_DEFAULT_UNIT_ID] = QMUnit::getDollars()->id;
            $newParams[Variable::FIELD_IS_PUBLIC] = 0;
            $paymentVariable = $this->getCommonPaymentVariable();
            return $paymentVariable->addParentCommonTag($parentVariableName, $newParams);
        } catch (Exception $e) {
            ExceptionHandler::logExceptionOrThrowIfLocalOrPHPUnitTest($e);
            return false;
        }
    }
    /**
     * @return bool|QMCommonVariable
     */
    public function getCommonPaymentVariable(){
        $paymentVariable = $this->getQMCommonVariableWithActualProductName()->getSpendingVariable();
        return $paymentVariable;
    }
	/**
	 * @return string
	 */
	public function __toString(){
		return $this->variableName;
	}
}
