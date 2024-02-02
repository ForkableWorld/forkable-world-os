<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Products;
use App\Properties\Variable\VariableNameProperty;
use App\Types\QMStr;
/** Class ProductHelper
 * @package App\Products
 */
class ProductHelper {
    /**
     * @param string $originalKeywords
     * @param string $variableCategoryName
     * @param array $providedVariableParameters
     * @return bool|AmazonProduct|WalMartProduct
     */
    public static function getByKeyword(string $originalKeywords, string $variableCategoryName = null, $providedVariableParameters = []){
        $product = AmazonHelper::getByKeyword($originalKeywords, $variableCategoryName, $providedVariableParameters);
        if($product){
            return $product;
        }
        // TODO:  Enable when we get approved
        //$product = WalMartHelper::getByKeyword($originalKeywords, $variableCategoryName, $providedVariableParameters);
        return $product;
    }
    /**
     * @param string $upc
     * @return AmazonProduct
     */
    public static function getByUpc($upc){
        $product = AmazonHelper::getByUpc($upc);
        if(!$product){
            //$product = APIHelper::getRequest('http://www.upcitemdb.com/upc/'.$upc);
            //$product = APIHelper::getRequest('http://www.upcitemdb.com/norob/ajax?action=search_amz_ebay&refer_id=311917110189&search='.$product['title']);
        }
        if(!$product){
            // TODO:  Enable when we get approved
            //$product = WalMartHelper::getByUpc($upc);
        }
        return $product;
    }
    /**
     * @param string $modifiedKeywords
     * @return bool|string
     */
    protected static function shortenSearchTerm(string $modifiedKeywords){
        $wordCountBefore = QMStr::getWordCount($modifiedKeywords);
        $modifiedKeywords = VariableNameProperty::sanitizeSlow($modifiedKeywords);
        $wordCountAfter = QMStr::getWordCount($modifiedKeywords);
        if($wordCountAfter < $wordCountBefore){
            return $modifiedKeywords;
        }
        $wordCount = QMStr::getWordCount($modifiedKeywords);
        if($wordCount < 2){ // Yellow Bananas becomes Bananas so don't break before searching one word
            return false;
        }
        $modifiedKeywords = QMStr::removeFirstWord($modifiedKeywords);  // Brand names are at the beginning
        return $modifiedKeywords;
    }
    /**
     * @param $modifiedKeywords
     * @return mixed|string
     */
    protected static function formatInitialSearchPhrase($modifiedKeywords){
        // removeNonAlphabeticalCharactersFromString removes 240GB from Silicon Power 240GB SSD 3D
        //$keywords = StringHelper::removeNonAlphabeticalCharactersFromString($keywords, " ");
        // removeCommonWords changes Digiroot (1Piece) 2-in-1 to Digiroot (1Piece) 2--1
        // $keywords = StringHelper::removeCommonWords($keywords);
        $modifiedKeywords = str_replace(',', ' ', $modifiedKeywords); // Needed for Car Phone Mount,Key
        $modifiedKeywords = str_replace(' - ', ' ', $modifiedKeywords);
        $modifiedKeywords = VariableNameProperty::sanitizeSlow($modifiedKeywords);
        return $modifiedKeywords;
    }
}
