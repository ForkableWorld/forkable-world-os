<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Products;
use GuzzleHttp\Client;
use App\Logging\QMLog;
use App\VariableCategories\FoodsVariableCategory;
use App\VariableCategories\TreatmentsVariableCategory;
use WalmartApiClient\Exception\Handler\ApiExceptionHandler;
use WalmartApiClient\Factory\CollectionFactory;
use WalmartApiClient\Factory\EntityFactory;
use WalmartApiClient\Http\TransportService;
use WalmartApiClient\Service\ProductService;
use WalmartApiClient\Service\TaxonomyService;
/** Class AmazonHelper
 * @package App\Utils
 */
class WalMartHelper {
    public const Arts_Crafts_and_Sewing = 1334134;
    public const Auto_and_Tires = 91083;
    public const Baby = 5427;
    public const Beauty = 1085666;
    public const Books = 3920;
    public const Cell_Phones = 1105910;
    public const Clothing = 5438;
    public const Electronics = 3944;
    public const Food = 976759;
    public const Gifts_and_Registry = 1094765;
    public const Health = 976760;
    public const Home = 4044;
    public const Home_Improvement = 1072864;
    public const Household_Essentials = 1115193;
    public const Industrial_and_Scientific = 6197502;
    public const Jewelry = 3891;
    public const Movies_and_TV_Shows = 4096;
    public const Music_on_CD_or_Vinyl = 4104;
    public const Musical_Instruments = 7796869;
    public const Office = 1229749;
    public const Party_and_Occasions = 2637;
    public const Patio_and_Garden = 5428;
    public const Personal_Care = 1005862;
    public const Pets = 5440;
    public const Photo_Center = 5426;
    public const Seasonal = 1085632;
    public const Sports_and_Outdoors = 4125;
    public const Toys = 4171;
    public const Video_Games = 2636;
    public const Walmart_for_Business = 6735581;
    // https://developer.walmartlabs.com/member/join/mikesinn/e82bbfee29feafaa81efbd699cdb980f/1520623382
    /**
     * @return ProductService
     */
    private static function getProductService(){
        $httpClient = new Client();
        $errorHandler = new ApiExceptionHandler();
        $transportService = new TransportService($httpClient, $errorHandler, self::$apiKey);
        $entityFactory = new EntityFactory();
        $collectionFactory = new CollectionFactory();
        $productService = new ProductService($transportService, $entityFactory, $collectionFactory);
        return $productService;
    }
    /**
     * @return TaxonomyService
     */
    private static function getTaxonomyService(){
        $httpClient = new Client();
        $errorHandler = new ApiExceptionHandler();
        $transportService = new TransportService($httpClient, $errorHandler, getenv('WALMART_API_KEY'));
        $entityFactory = new EntityFactory();
        $collectionFactory = new CollectionFactory();
        $taxonomyService = new TaxonomyService($transportService, $entityFactory, $collectionFactory);
        return $taxonomyService;
    }
    /**
     * @param string $keyword
     * @param string|null $variableCategoryName
     * @param array $providedVariableParameters
     * @return WalMartProduct
     */
    public static function getByKeyword(string $keyword, string $variableCategoryName = null, $providedVariableParameters = []){
        $categoryId = self::getMostSimilarCategoryId($variableCategoryName);
        $results = self::getProductService()->getBySearch($keyword, $categoryId);
        $first = $results->getFirst();
        $productItem = new WalMartProduct($first, null, $providedVariableParameters);
        return $productItem;
    }
    /**
     * @param string $upc
     * @param array $providedVariableParameters
     * @return WalMartProduct
     */
    public static function getByUpc(string $upc, $providedVariableParameters = []){
        $results = self::getProductService()->getByUpc($upc);
        $productItem = new WalMartProduct($results, null, $providedVariableParameters);
        return $productItem;
    }
    /**
     * @return array
     */
    private static function getCategories(){
        $categories = self::getTaxonomyService()->getCategories();
        return $categories->getAll();
    }
    /**
     * @param $variableCategoryName
     * @return null
     */
    private static function getMostSimilarCategoryId(string $variableCategoryName = null){
        if(!$variableCategoryName){
            return null;
        }
        if($variableCategoryName === FoodsVariableCategory::NAME){
            return self::Food;
        }
        if($variableCategoryName === TreatmentsVariableCategory::NAME){
            return self::Health;
        }
        return null;
    }
    private static function outputCategoryConstants(){
        $categories = self::getCategories();
        foreach($categories as $category){
            $constName = str_replace("&", "and",
                str_replace(" ", "_", $category->getName()));
            $constVal = $category->getId();
            QMLog::info("const ".$constName." = ".$constVal.";");
        }
    }
}
