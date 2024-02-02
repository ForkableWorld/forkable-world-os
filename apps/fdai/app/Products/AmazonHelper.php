<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Products;
use ApaiIO\ApaiIO;
use ApaiIO\Configuration\Country;
use ApaiIO\Configuration\GenericConfiguration;
use ApaiIO\Operations\Lookup;
use ApaiIO\Operations\Search;
use ApaiIO\Request\GuzzleRequest;
use ApaiIO\ResponseTransformer\XmlToArray;
use App\Properties\Variable\VariableNameProperty;
use App\Traits\HasConstants;
use Exception;
use GuzzleHttp\Client;
use LogicException;
use App\Logging\QMLog;
use App\Types\QMStr;
use App\VariableCategories\FoodsVariableCategory;
use App\VariableCategories\MiscellaneousVariableCategory;
use App\VariableCategories\PaymentsVariableCategory;
use App\VariableCategories\TreatmentsVariableCategory;
use App\Variables\QMCommonVariable;
use Tests\UnitTests\Products\AmazonTest;

/** Class AmazonHelper
 * @package App\Utils
 */
class AmazonHelper extends ProductHelper {
    use HasConstants;
    // Currently using Canadian Product API until we get approved at https://affiliate-program.amazon.com/assoc_credentials/home
	
    // US: https://affiliate-program.amazon.com/assoc_credentials/home
    // CANADA: https://associates.amazon.ca/assoc_credentials/home

    public const ALL = 'All';
    public const APPLIANCES = 'Appliances';
    public const ARTS_AND_CRAFTS = 'ArtsAndCrafts';
    public const AUTOMOTIVE = 'Automotive';
    public const BABY = 'Baby';
    public const BEAUTY = 'Beauty';
    public const BLENDED = 'Blended';
    public const BOOKS = 'Books';
    public const CLASSICAL = 'Classical';
    public const COLLECTIBLES = 'Collectibles';
    public const DIGITAL_MUSIC = 'DigitalMusic';
    public const DVD = 'DVD';
    public const ELECTRONICS = 'Electronics';
    public const GOURMET_FOOD = 'GourmetFood';
    public const GROCERY = 'Grocery';
    public const HEALTH_PERSONAL_CARE = 'HealthPersonalCare';
    public const HOME_GARDEN = 'HomeGarden';
    public const INDUSTRIAL = 'Industrial';
    public const JEWELRY = 'Jewelry';
    public const KINDLE_STORE = 'KindleStore';
    public const KITCHEN = 'Kitchen';
    public const LAWN_GARDEN = 'LawnAndGarden';
    public const MAGAZINES = 'Magazines';
    public const MARKETPLACE = 'Marketplace';
    public const MISCELLANEOUS = 'Miscellaneous';
    public const MOBILE_APPS = 'MobileApps';
    public const MP3_DOWNLOADS = 'Mp3Downloads';
    public const MUSIC = 'Music';
    public const MUSICAL_INSTRUMENTS = 'MusicalInstruments';
    public const MUSIC_TRACKS = 'MusicTracks';
    public const OFFICE_PRODUCTS = 'OfficeProducts';
    public const OUTDOOR_LIVING = 'OutdoorLiving';
    public const PC_HARDWARE = 'PCHardware';
    public const PET_SUPPLIES = 'PetSupplies';
    public const PHOTO = 'Photo';
    public const SHOES = 'Shoes';
    public const SOFTWARE = 'Software';
    public const SPORTING_GOODS = 'SportingGoods';
    public const TOOLS = 'Tools';
    public const TOYS = 'Toys';
    public const VIDEO = 'Video';
    public const VIDEO_GAMES = 'VideoGames';
    public const WATCHES = 'Watches';
    public const WIRELESS = 'Wireless';
    public const WIRELESS_ACCESSORIES = 'WirelessAccessories';
    public static $oldAffiliateIds = [
        'quant08-20',
        'quantimodo-20'
    ];
    /**
     * @param string $productCode
     * @return string
     */
    public static function getAmazonAffiliateUrlByProductCode($productCode){
        return 'https://www.amazon.com/dp/'.$productCode.'/?tag='.getenv('AMAZON_AFFILIATE_ID');
    }
    /**
     * @return GenericConfiguration
     */
    private static function getAmazonConfiguration(){
        $conf = new GenericConfiguration();
        $client = new Client();
        $request = new GuzzleRequest($client);
        $conf->setCountry(getenv('AMAZON_COUNTRY'))
            ->setAccessKey(getenv('AMAZON_ACCESS_KEY'))
            ->setSecretKey(getenv('AMAZON_SECRET_KEY'))
            ->setAssociateTag(getenv('AMAZON_AFFILIATE_ID'))
            ->setRequest($request)
            ->setResponseTransformer(new XmlToArray());
        return $conf;
    }
    /**
     * @param string $id
     * @param string $idType
     * @return bool|mixed
     */
    private static function searchByProductId($id, $idType = 'UPC'){
        $conf = self::getAmazonConfiguration();
        $apaiIO = new ApaiIO($conf);
        $lookup = new Lookup();
        if($idType === 'UPC' && strlen($id) !== 12){
            le("UPC should have 12 characters!");
        }
        if($idType === 'EAN' && strlen($id) !== 13){
            le("EAN should have 13 characters!");
        }
        $lookup->setIdType($idType);
        if($idType !== 'ASIN'){
            $lookup->setSearchIndex('All');
        }
        $lookup->setItemId($id);
        $lookup->setResponseGroup(['Large']); // More detailed information
        $response = $apaiIO->runOperation($lookup);
        if(isset($response['Items']['Request']['Errors']['Error']['Message'])){
            QMLog::error($response['Items']['Request']['Errors']['Error']['Message']);
        }
        return $response;
    }
    /**
     * @param string $keywords
     * @param string $category
     * @return bool|string
     */
    public static function getAmazonBestSellerProductUrlByKeyword($keywords, $category = null){
        $amazonProduct = self::getByKeyword($keywords, $category);
        if($amazonProduct){
            return $amazonProduct->getProductUrl();
        }
        return false;
    }
    /**
     * @param string $originalKeywords
     * @param string $variableCategoryName
     * @param array $providedVariableParameters
     * @return bool|AmazonProduct
     */
    public static function getByKeyword(string $originalKeywords, string $variableCategoryName = null, $providedVariableParameters = []){
        if(time() < strtotime(AmazonTest::DISABLED_UNTIL)){
            QMLog::info('Need to re-apply for amazon associates...');
            return false;
        }
        if(false && !$variableCategoryName){
            $result = self::getByKeyword($originalKeywords, self::GOURMET_FOOD, $providedVariableParameters);
            if(!$result){
                $result = self::getByKeyword($originalKeywords, self::HEALTH_PERSONAL_CARE, $providedVariableParameters);
            }
            if($result){
                return $result;
            }
        }
        if($variableCategoryName){
            $providedVariableParameters[QMCommonVariable::variableCategoryName] = $variableCategoryName;
        }
        if(isset($providedVariableParameters[QMCommonVariable::variableCategoryName])){
            $variableCategoryName = $providedVariableParameters[QMCommonVariable::variableCategoryName];
        }
        $modifiedKeywords = $originalKeywords;
        QMLog::info("getByKeyword for $modifiedKeywords");
        $conf = self::getAmazonConfiguration();
        $apaiIO = new ApaiIO($conf);
        $search = new Search();
        $amazonCategory = self::getMostSimilarAmazonCategory($variableCategoryName);
        if($amazonCategory){
            $search->setCategory($amazonCategory);
        }
        $search->setResponseGroup(['Large']); // More detailed information
        // removeNonAlphabeticalCharactersFromString removes 240GB from Silicon Power 240GB SSD 3D
        //$keywords = StringHelper::removeNonAlphabeticalCharactersFromString($keywords, " ");
        // removeCommonWords changes Digiroot (1Piece) 2-in-1 to Digiroot (1Piece) 2--1
        // $keywords = StringHelper::removeCommonWords($keywords);
        $modifiedKeywords = str_replace(',', ' ', $modifiedKeywords); // Needed for Car Phone Mount,Key
        $modifiedKeywords = str_replace(' - ', ' ', $modifiedKeywords);
        $modifiedKeywords = VariableNameProperty::sanitizeSlow($modifiedKeywords);
        $bestMatchFromAmazonResults = false;
        while(!$bestMatchFromAmazonResults && QMStr::getWordCount($modifiedKeywords)){
            sleep(1);
            QMLog::info("Searching Amazon for $modifiedKeywords (Amazon Category: $amazonCategory, QM category: $variableCategoryName)");
            $search->setKeywords($modifiedKeywords);
            try {
                $response = $apaiIO->runOperation($search);
            } catch (Exception $e) {
                QMLog::error(__METHOD__.": ".$e->getMessage());
                return false;
            }
            if(isset($response['Items']['Request']['Errors']['Error']['Message'])){
                QMLog::error($response['Items']['Request']['Errors']['Error']['Message'] . ": " . $modifiedKeywords . " (Amazon Category: $amazonCategory)");
            }
            $bestMatchFromAmazonResults = self::getFirstItemFromAmazonResponse($response, $modifiedKeywords, $originalKeywords, $providedVariableParameters);
            if(!$bestMatchFromAmazonResults){
                $wordCount = QMStr::getWordCount($modifiedKeywords);
                if($wordCount < 2){ // Yellow Bananas becomes Bananas so don't break before searching one word
                    break;
                }
                $modifiedKeywords = QMStr::removeFirstWord($modifiedKeywords);  // Brand names are at the beginning
            }
        }
        if(!$bestMatchFromAmazonResults){
            QMLog::error("No Amazon products found for keyword $modifiedKeywords");
        }
        return $bestMatchFromAmazonResults;
    }
    /**
     * @param array $response
     * @param null $potentiallyAbbreviatedKeywords
     * @param null $originalKeywords
     * @param array $providedVariableParameters
     * @return bool|AmazonProduct
     */
    private static function getFirstItemFromAmazonResponse($response, $potentiallyAbbreviatedKeywords = null, $originalKeywords = null, $providedVariableParameters = []){
        if(!isset($response['Items']['Item'])){
            return false;
        }
        if(!isset($response['Items']['Item'][0])){
            $bestMatch = $response['Items']['Item'];
        }else{
            $bestMatch = $response['Items']['Item'][0];
            $itemsBySimilarity = [];
            $bestScore = 0;
            foreach($response['Items']['Item'] as $item){
                $title = $item['ItemAttributes']['Title'];
                $score = similar_text($potentiallyAbbreviatedKeywords, $title, $percent);
                $itemsBySimilarity[$score] = [
                    'item'    => $item,
                    'percent' => $percent
                ];
                if($score > $bestScore){
                    $bestScore = $score;
                    $bestMatch = $item;
                }
            }
        }
        //        if($potentiallyAbbreviatedKeywords && !StringHelper::stringContainsAllWordsInAnotherString($potentiallyAbbreviatedKeywords, $title)){
        //            // Prevents getting any results for "Snore Reducing Aids"
        //            return false;
        //        }
        return new AmazonProduct($bestMatch, $originalKeywords, $providedVariableParameters);
    }
    /**
     * @param array $response
     * @return AmazonProduct|bool
     */
    private static function getBestSellerFromAmazonResults($response){
        if(!isset($response['Items']['Item'])){
            return false;
        }
        if(!isset($response['Items']['Item'][0])){
            $bestSeller = new AmazonProduct($response['Items']['Item']);
            return $bestSeller;
        }
        $lowestSalesRank = "999999999999999";
        $bestSeller = $response['Items']['Item'][0];
        foreach($response['Items']['Item'] as $item){
            if(isset($item['SalesRank']) && $item['SalesRank'] < $lowestSalesRank){
                $bestSeller = $item;
            }
        }
        $bestSeller = new AmazonProduct($bestSeller);
        return $bestSeller;
    }
    /**
     * @param $asin
     * @param $itemName
     * @return Product
     */
    public static function getByAsinOrKeyword($asin, $itemName){
        if(stripos($asin, 'product') !== false){
            $asin = QMStr::after('product/', $asin);
            $asin = QMStr::before('/', $asin);
        }
        $product = self::getByProductId($asin, 'ASIN');
        if(!$product){
            $product = ProductHelper::getByKeyword($itemName);
        }
        return $product;
    }
    /**
     * @param string $upc
     * @return AmazonProduct
     */
    public static function getByUpc($upc){
        return self::getByProductId($upc, 'UPC');
    }
    /**
     * @param string $upc
     * @param string $idType
     * @return AmazonProduct
     */
    private static function getByProductId($upc, $idType = 'UPC'){
        $bestSeller = $response = false;
        try {
            $response = self::searchByProductId($upc, $idType);
        } catch (Exception $e) {
            QMLog::error("UPC check failed: " . $e->getMessage(), []);
        }
        if(isset($response['Items']['Request']['Errors']['Error']['Message'])){
            QMLog::error($response['Items']['Request']['Errors']['Error']['Message']);
        }
        if($response){
            $bestSeller = self::getBestSellerFromAmazonResults($response);
        }
        if(!$bestSeller){
            QMLog::error("Could not find Amazon product matching UPC $upc", ['amazon response' => $response]);
        }
        return $bestSeller;
    }
    /**
     * @param string $variableCategoryName
     * @param string $defaultAmazonCategory
     * @return string
     */
    public static function getMostSimilarAmazonCategory($variableCategoryName, $defaultAmazonCategory = self::ALL){
        if(empty($variableCategoryName)){
            QMLog::debug("No variable category name provided to ".__METHOD__);
            return $defaultAmazonCategory;
        }
        if($variableCategoryName === TreatmentsVariableCategory::NAME){
            return self::HEALTH_PERSONAL_CARE;
        }
        if($variableCategoryName === FoodsVariableCategory::NAME){
            return self::GROCERY;
        }
        if($variableCategoryName === MiscellaneousVariableCategory::NAME || $variableCategoryName === PaymentsVariableCategory::NAME){
            return $defaultAmazonCategory;
        }
        $variableCategoryName = strtolower($variableCategoryName);
        foreach(self::getConstants() as $amazonCategoryName){
            if(strtolower($amazonCategoryName) === $variableCategoryName){
                return $amazonCategoryName;
            }
        }
        $firstWord = QMStr::getFirstWordOfString($variableCategoryName);
        foreach(self::getConstants() as $amazonCategoryName){
            if(strpos(strtolower($amazonCategoryName), $firstWord) !== false){
                return $amazonCategoryName;
            }
        }
        QMLog::info("Could not find Amazon category for $variableCategoryName");
        return $defaultAmazonCategory;
    }
    /**
     * @param string $string
     * @return string
     */
    public static function replaceOldAffiliateId($string): ?string{
        if(!$string){
            return $string;
        }
        foreach(self::$oldAffiliateIds as $affiliateId){
            $string = str_replace($affiliateId, getenv('AMAZON_AFFILIATE_ID'), $string);
        }
        return $string;
    }
}
