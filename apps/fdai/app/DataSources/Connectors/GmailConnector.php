<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataSources\Connectors;
use App\Exceptions\InvalidVariableNameException;
use Exception;
use Google\Service\PeopleService;
use Google_Service_Gmail;
use Google_Service_Gmail_ListMessagesResponse;
use Google_Service_Gmail_Message;
use Google_Service_Oauth2;
use Guzzle\Http\Exception\ClientErrorResponseException;
use OAuth\Common\Storage\Exception\TokenNotFoundException;
use App\Exceptions\CredentialsNotFoundException;
use App\Utils\AppMode;
use App\DataSources\ConnectInstructions;
use App\DataSources\GoogleBaseConnector;
use App\Types\TimeHelper;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Slim\Model\Measurement\AdditionalMetaData;
use App\Slim\Model\Measurement\MeasurementSet;
use App\Products\ProductHelper;
use App\Logging\QMLog;
use App\Types\QMStr;
use App\Units\CountUnit;
use App\Units\DollarsUnit;
use App\VariableCategories\FoodsVariableCategory;
use App\VariableCategories\MiscellaneousVariableCategory;
use App\VariableCategories\PaymentsVariableCategory;
use App\Variables\QMUserVariable;
use App\Variables\QMVariableCategory;
use App\Slim\View\Request\Variable\GetUserVariableRequest;
use Soundasleep\Html2Text;
/** Class GmailConnector
 * @package App\DataSources\Connectors
 */
class GmailConnector extends GoogleBaseConnector {
	protected const BACKGROUND_COLOR = '#23448b';
	protected const CONNECTOR_ID = 77;
	protected const DEFAULT_VARIABLE_CATEGORY_NAME = 'Payments';
	public const DISPLAY_NAME = 'Gmail';
	protected const ENABLED = false; // Disabled until implementation of "Experimental Connectors" section with warning and creation of mail parsers models for each sender
	protected const GET_IT_URL = 'https://gmail.com';
	protected const LOGO_COLOR = '#d34836';
	protected const LONG_DESCRIPTION = 'Automatically see how supplements and foods might be improving or exacerbating your symptom severity by connecting GMail and importing your email receipts from Amazon, Instacart, etc.';
	protected const SHORT_DESCRIPTION = 'Automate your tracking by importing your email receipts from Amazon, Instacart, etc.';
	protected $allMessages;
    public $backgroundColor = self::BACKGROUND_COLOR;
	public $defaultVariableCategoryName = self::DEFAULT_VARIABLE_CATEGORY_NAME;
	public $displayName = self::DISPLAY_NAME;
	public $enabled = self::ENABLED;
	public $getItUrl = self::GET_IT_URL;
	public $id = self::ID;
	public $image = self::IMAGE;
	public $logoColor = self::LOGO_COLOR;
	public $longDescription = self::LONG_DESCRIPTION;
	public $name = self::NAME;
	public $shortDescription = self::SHORT_DESCRIPTION;
	public const ID = 77;
    public const IMAGE = 'https://upload.wikimedia.org/wikipedia/commons/4/4e/Gmail_Icon.png';
    public const NAME = 'gmail';
    public const TEST_VARIABLE = 'Maldon Sea Salt Flakes';
    public static array $SCOPES = [
        Google_Service_Gmail::GMAIL_READONLY,
        Google_Service_Oauth2::USERINFO_EMAIL
    ];
    public static $SCOPE_USAGE_EXPLANATIONS = [
        Google_Service_Gmail::GMAIL_READONLY => 'Automatically see how supplements and foods might be improving or '.
            'exacerbating your symptom severity by connecting GMail and importing your email receipts from Amazon, Instacart, etc.',
        Google_Service_Oauth2::USERINFO_EMAIL => 'Allow for password-less registration and login',
        PeopleService::CONTACTS => 'Easily share charts and other data with your friends, family, or healthcare provider.',
    ];
    protected $quantityMeasurementItems;
    protected $gmailUserId = "me";
    protected $subjectLine;
    protected $fromLine;
    protected $dateLine;
    protected $plainTextMessage;
    protected $decodedRawMessageWithHtml;
    protected $paymentMeasurementItems;
    public $providesUserProfileForLogin = true;
    protected $requiredText;
    protected $messagesResponse;
	/**
	 * @return int
	 * @throws InvalidVariableNameException
	 * @throws TokenNotFoundException
	 */
    public function importData(): void {
        //$this->getMessagesAndConvertToMeasurementItems('Review Your Monthly Subscription Delivery');
        //$this->getMessagesAndConvertToMeasurementItems('Order Receipt');
        try {
            $this->getOrRefreshToken();
        } catch (ClientErrorResponseException $e){
            $m = "QuantiModo is longer authorized to access Gmail data so disconnecting Gmail connector. ERROR RESPONSE: ".$e->getMessage();
            throw new CredentialsNotFoundException($this, $m);
        }
        $this->getMessagesAndSaveMeasurements('Review Your Monthly Subscription Delivery', null);
        $this->getMessagesAndSaveMeasurements('Review Your Monthly Delivery', null);
        if(AppMode::isTestingOrStaging()){

        }
        $this->getMessagesAndSaveMeasurements('support@shipt.com', FoodsVariableCategory::NAME);
        $this->getMessagesAndSaveMeasurements('auto-confirm@amazon.com', null);
        $this->getMessagesAndSaveMeasurements('ebay@ebay.com', null, 'Order Delivered');  // TODO: Figure out how to get categories

    }
    /**
     * @param $itemName
     * @return bool
     */
    protected static function containsBlackListedString($itemName): bool{
        if(empty($itemName)){
            return true;
        }
        $blackListedStrings = [
            '>',
            ';',
            'tax',
            '$',
            '=',
            ' credit',
            ' receipt',
            ' price',
            '{',
            '}',
            'jquery',
            '//',
            "date: ",
            " 201",
            " charged",
            " delivery",
            " order",
            "sign up"
        ];
        $containsBlackList = false;
        foreach($blackListedStrings as $blackListedString){
            if(strpos(strtolower($itemName), strtolower($blackListedString)) !== false){
                $containsBlackList = true;
            }
        }
        return $containsBlackList;
    }
    /**
     * @param $itemsArray
     * @return array
     */
    protected static function setFallBackQuantityInItemsArray($itemsArray){
        $withQuantity = [];
        foreach($itemsArray as $itemName => $itemArray){
            if(!isset($itemArray['quantity'])){
                $itemArray['quantity'] = 1;
                if(!empty($itemArray['individual'])){
                    $itemArray['quantity'] = round($itemArray['totalSpent'] / $itemArray['individual']);
                }
            }
            $withQuantity[$itemName] = $itemArray;
        }
        return $withQuantity;
    }
    /**
     * @param $removeEmptyLines
     * @return array
     */
    protected static function convertMessageLinesArrayToItemsArray($removeEmptyLines): array{
        $items = [];
        foreach($removeEmptyLines as $key => $value){
            if(strpos($value, '$') !== false){
                $priceArray = explode(" ", $value);
                foreach($priceArray as $priceArrayValue){
                    if(strpos($priceArrayValue, "$") !== false){
                        $itemNameIndex = $key - 1;
                        if(strpos($removeEmptyLines[$key - 1], "Quantity: ") !== false){
                            $itemNameIndex = $key - 2;
                            $quantityIndex = $key - 1;
                        }
                        $itemName = $removeEmptyLines[$itemNameIndex];
                        if(self::containsBlackListedString($itemName)){
                            continue;
                        }
                        if(isset($quantityIndex)){
                            $items[$itemName]['quantity'] = (int)str_replace("Quantity: ", '', $removeEmptyLines[$quantityIndex]);
                        }
                        $priceFloat = (float)str_replace("$", '', $priceArrayValue);
                        if(!$priceFloat){
                            QMLog::error("$itemName price from email is $priceFloat.  Maybe it wasn't purchased?");
                            continue;
                        }
                        if(strlen($itemName) < 3){
                            QMLog::error("item name is $itemName!");
                        }
                        if(!isset($items[$itemName]['totalSpent']) || $priceFloat > $items[$itemName]['totalSpent']){
                            $items[$itemName]['totalSpent'] = $priceFloat;
                        }
                        if(!isset($items[$itemName]['individual']) || $priceFloat < $items[$itemName]['individual']){
                            $items[$itemName]['individual'] = $priceFloat;
                        }
                    }
                }
            }
        }
        return $items;
    }
    /**
     * @param string $decodedMessage
     * @return array
     */
    protected static function convertMessageToLinesArray(string $decodedMessage): array{
        $byLinesArray = explode("\n", $decodedMessage);
        $removeEmptyLines = [];
        $previousLine = null;
        \App\Logging\ConsoleLog::info("Trying to extract prices from ".count($byLinesArray)." lines");
        foreach($byLinesArray as $line){
            $line = trim($line);
            if(strpos($line, "Sold by") !== false){
                continue;
            }
            if(strpos($line, "Subscribe & Save price") !== false){
                continue;
            }
            $line = str_replace("Current price: ", "", $line);
            if($previousLine && substr($previousLine, -1) === "="){
                $line = str_replace("=", "", $previousLine).$line; // Amazon delimits multi-line item names with =
            }
            if(!empty($line)){
                $removeEmptyLines[] = $line;
            }
            $previousLine = $line;
        }
        return $removeEmptyLines;
    }
    /**
     * @return ConnectInstructions
     */
    public function getConnectInstructions(): ?ConnectInstructions{
        return $this->getConnectInstructions();
    }
    /**
     * Get list of Messages in user's mailbox.
     * @param $query
     */
    public function getMessagesAndConvertToMeasurementItems($query){
        $opt_param = [];
        $fromString = date('Y/m/d', $this->fromTime);
        $opt_param['q'] = $query." after:".$fromString;
        $allMessages = $this->getAllMessages($opt_param);
        $items = $this->convertMessagesToMeasurementItems($allMessages);
        $this->logInfo("Got measurements for ".count($items)." variables from query $query");
    }
    /**
     * @param Google_Service_Gmail_Message[] $messages
     */
    public function convertMessagesToMeasurementItems(array $messages){
        $i = 0;
        foreach($messages as $message){
            $i++;
            $this->setDecodedRawMessageAndStuff($message);
            if(!$this->bodyContainsString("$")){
                $this->logInfo("Skipping $this->subjectLine email because body does not contain $");
                continue;
            }
            if(!$this->bodyOrSubjectContainRequiredText()){
                continue;
            }
            //if(strpos($this->subjectLine, "Price Alert")){continue;}
            $this->addMeasurementItems();
        }
    }
    /**
     * @return bool
     */
    public function bodyOrSubjectContainRequiredText(){
        if(!$this->requiredText){
            return true;
        }
        if($this->bodyContainsString($this->requiredText)){
            return true;
        }
        if($this->subjectContainsString($this->requiredText)){
            return true;
        }
        return false;
    }
    /**
     * @param $string
     * @return bool
     */
    public function subjectContainsString($string){
        return stripos($this->subjectLine, $string) !== false;
    }
    /**
     * @param $string
     * @return bool
     */
    public function bodyContainsString(string $string): bool {
        $html = $this->getDecodedRawMessageWithHtml();
        return stripos($html, $string) !== false;
    }
    /**
     * @param Google_Service_Gmail_Message $message
     * @return bool|string
     */
    public function setDecodedRawMessageAndStuff(Google_Service_Gmail_Message $message): ?string {
        $html = $this->setDecodedRawMessageWithHtml($message);
        $this->setSubjectLine();
        $this->setFromLine();
        $this->setDateLine();
        $this->logInfo("$this->subjectLine from $this->fromLine on $this->dateLine");
        if(!$this->bodyOrSubjectContainRequiredText()){
            return null;
        }
        $text = QMStr::between($html, "Content-Type: text/plain", "Content-Type: text/html");
        if(empty($text)){
            //$html = StringHelper::getStringAfterSubString('</style>', $this->decodedRawMessageWithHtml);
            try {
                $text = Html2Text::convert($html);
            } catch (Exception $e) {
                $text = null;
                QMLog::info("Could not convert HTML message to plain text because: ".$e->getMessage());
            }
        }
        return $this->plainTextMessage = $text;
    }
    /**
     * @return string
     */
    public function setFromLine(){
        $html = $this->getDecodedRawMessageWithHtml();
        return $this->fromLine = QMStr::between($html, "From: ", "\r");
    }
    /**
     * @return string
     */
    public function setDateLine(){
        $html = $this->getDecodedRawMessageWithHtml();
        return $this->dateLine = QMStr::between($html, "Date: ", "\r");
    }
    /**
     * @return string
     */
    public function setSubjectLine(){
        $html = $this->getDecodedRawMessageWithHtml();
        $subjectLine = QMStr::between($html, "Subject: ", "\r");
        $subjectLine = str_replace('_', ' ', $subjectLine);
        if(stripos(QMStr::getFirstWordOfString($subjectLine), 'UTF') !== false){
            $subjectLine = QMStr::removeFirstWord($subjectLine);
        }
        $subjectLine = str_replace('"', '', $subjectLine);
        $subjectLine = str_replace('_', ' ', $subjectLine);
        $subjectLine = str_replace('...?=', '', $subjectLine);
        return $this->subjectLine = $subjectLine;
    }
    /**
     * @param Google_Service_Gmail_Message $message
     * @return bool|string
     */
    public function setDecodedRawMessageWithHtml(Google_Service_Gmail_Message $message): string {
        $gmailMessage = $this->getGmailMessageById($message->id);
        $replacedRawMessage = strtr($gmailMessage->raw, '-_', '+/');
        return $this->decodedRawMessageWithHtml = base64_decode($replacedRawMessage);
    }
    /**
     * @param string $id
     * @return Google_Service_Gmail_Message
     */
    public function getGmailMessageById(string $id): Google_Service_Gmail_Message{
        return $this->getGmailService()->users_messages->get($this->gmailUserId, $id, ['format' => 'raw']);
    }
    /**
     * @param string $decodedMessage
     * @return string
     */
    protected static function getItemName(string $decodedMessage): string{
        $itemName = QMStr::between($decodedMessage, 'order of ', "...");
        if(empty($itemName)){
            $itemName = QMStr::between($decodedMessage, 'You ordered   "', "...");
        }
        $itemName = QMStr::before("=", $itemName, $itemName);
        $itemName = str_replace('"', '', $itemName);
        $itemName = QMStr::after(' x ', $itemName, $itemName);
        $itemName = QMStr::before("\r", $itemName, $itemName);
        //
        if(strpos($itemName, '(') === 0){
            $itemName = QMStr::after(')', $itemName);
        }
        if(strpos($itemName, "\r") !== false){
            QMLog::error("name has line breaks");
        }
        return $itemName;
    }
    /**
     * @return string
     */
    public function getOrderId(){
        $body = $this->getTextOrHtmlBody();
        $orderId = QMStr::between($body, 'orderId=', "&");
        if(empty($orderId)){
            $orderId = QMStr::between($body, 'Order #', "\n"); // Have to user double quotes
        }
        if(empty($orderId)){
            $orderId = QMStr::between($body, 'orderId%3D', "%2");
        }
        $orderId = str_replace("\r", '', $orderId);  // Have to user double quotes
        $orderId = str_replace("3D", '', $orderId);
        return $orderId;
    }
    /**
     * @return string
     */
    public function getPlainTextMessage(): ?string {
        return $this->plainTextMessage;
    }
    /**
     * @param string $decodedMessage
     * @return array
     */
    public static function getItemsArrayFromDecodedMessage(string $decodedMessage){
        $removeEmptyLines = self::convertMessageToLinesArray($decodedMessage);
        $itemsArray = self::convertMessageLinesArrayToItemsArray($removeEmptyLines);
        $itemsArray = self::setFallBackQuantityInItemsArray($itemsArray);
        return $itemsArray;
    }
    /**
     * @return string
     */
    public function getDecodedRawMessageWithHtml(): string {
        return $this->decodedRawMessageWithHtml;
    }
    /**
     * @param string $itemName
     * @param string $variableCategoryName
     * @return QMUserVariable
     */
    public function getUserVariable(string $itemName, string $variableCategoryName = null): QMUserVariable{
        $userVariable = GetUserVariableRequest::getWithNameLike($itemName, $this->userId);
        if($userVariable){
            return $userVariable;
        }
        $amazonProduct = ProductHelper::getByKeyword($itemName, $variableCategoryName);
        if($amazonProduct){
            $userVariable = $amazonProduct->findUserVariable($this->userId);
        }else{
            QMLog::error("Could not get Amazon product for $itemName");
            $newVariableParams = [
                'variableCategoryName'       => MiscellaneousVariableCategory::NAME,
                'defaultUnitAbbreviatedName' => CountUnit::NAME
            ];
            if($variableCategoryName){
                $newVariableParams['variableCategoryName'] = $variableCategoryName;
                $category = QMVariableCategory::findByNameOrSynonym($variableCategoryName);
                $newVariableParams['defaultUnitAbbreviatedName'] = $category->defaultUnitAbbreviatedName;
            }
            $userVariable = QMUserVariable::findOrCreateByNameOrId($this->userId, $itemName, [], $newVariableParams);
        }
        return $userVariable;
    }
    /**
     * @param $errorMessage
     */
    public function logMessageError(string $errorMessage){
        $this->logInfo("$errorMessage in ".$this->getMessageSubjectFromString());
    }
    /**
     * @return string
     */
    public function getMessageSubjectFromString(): string{
        return "$this->subjectLine from $this->fromLine on $this->dateLine";
    }
	/**
	 * @return array
	 * @throws \App\Exceptions\InvalidVariableValueAttributeException
	 */
    public function addMeasurementItems(){
        $orderId = $this->getOrderId();
        $startTimeString = $this->getStartAt();
        if(empty($orderId)){
            $orderId = $startTimeString;
        }
        $unixTime = TimeHelper::universalConversionToUnixTimestamp($startTimeString);
        $note = $this->getMeasurementNote();
        $itemsArray = $this->getItemsArray();
        foreach($itemsArray as $itemName => $itemArray){
            if(AppMode::isTestingOrStaging() && stripos($itemName, self::TEST_VARIABLE) === false){
                continue;
            }
            if(isset($itemArray['totalSpent'])){
                $note->setMessage("Total: $".number_format($itemArray['totalSpent'], 2)." ".$this->getMessageSubjectFromString()." ($itemName)");
            }else{
                $note->setMessage($this->getMessageSubjectFromString()." ($itemName)");
            }
            if(!isset($itemArray['quantity'])){
                $this->logMessageError("$itemName quantity not set!");
                continue;
            }
            if(empty($itemName)){
                $this->logMessageError("item name is empty");
                continue;
            }
            if(self::containsExcludedItemName($itemName)){continue;}
            if(isset($itemArray['quantity'])){
                $unitName = CountUnit::NAME;
                $m = new QMMeasurement($unixTime, $itemArray['quantity'], $note);
                $m->setOriginalUnitByNameOrId($unitName);
                $uv = QMUserVariable::findOrCreateWithReminderFromAmazon($itemName, $this->userId);
                $this->setUserVariableByName($uv);
                $uv->addToMeasurementQueueIfNoneExist($m);
            }
            if(isset($itemArray['totalSpent'])){
                $unitName = DollarsUnit::NAME;
                $m = new QMMeasurement($unixTime, $itemArray['totalSpent'], $note);
                $m->setOriginalUnitByNameOrId($unitName);
                $this->paymentMeasurementItems[$itemName][$orderId] = $m;
            }
            $this->logDebug("$startTimeString $itemName");
            if($this->stringEqualsTestVariableName($itemName)){
                break;
            }
        }
        return $this->quantityMeasurementItems;
    }
    /**
     * @return string
     */
    public function getStartAt(): string{
        $text = $this->getTextOrHtmlBody();
        $startAt = QMStr::between($text, "delivery date:", "\r");
        if(empty($startAt)){
            $startAt = QMStr::between($text, "Arriving: ", "\r");
        }
        if(empty($startAt)){
            return $this->dateLine;
        }
        return $startAt;
    }
    /**
     * @return AdditionalMetaData
     */
    public function getMeasurementNote(): AdditionalMetaData {
        // Let's not do this to make sure we link using Amazon affiliate url
        //$hostName = $this->getHostNameFromEmail();
        //$url = StringHelper::getFirstUrlFromString($this->getDecodedRawMessageWithHtml(), $hostName);
        $note = new AdditionalMetaData();
        //$note->setUrl($url);
        return $note;
    }
    /**
     * @return array
     */
    public function getItemsArray(): array{
        $subject = $this->subjectLine;
        $html = $this->getDecodedRawMessageWithHtml();
        if(strpos($subject, ' order of ') !== false){
            $itemName = self::getItemName($html);
            $itemsArray[$itemName] = ['quantity' => 1];
        }else if(stripos($subject, 'ORDER DELIVERED:') !== false){
            $itemName = str_replace('ORDER DELIVERED: ', '', $subject);
            $itemName = QMStr::before(' - ', $itemName, $itemName);
            //$itemsArray[$itemName] = ['quantity' => 1, 'totalSpent' => ];
            $spent = QMStr::after('$', $html);
            $spent = QMStr::before(' ', $spent);
            $itemsArray[$itemName] = [
                'quantity'   => 1,
                'totalSpent' => (float)$spent
            ];
        }else{
            if($text = $this->plainTextMessage){
                $itemsArray = self::getItemsArrayFromDecodedMessage($text);
            } else {
                $this->logError("No plain text so could not getItemsArrayFromDecodedMessage.  Might want to implement a parser for $this->subjectLine");
                return [];
            }
        }
        return $itemsArray;
    }
    /**
     * @param string $query
     * @param string $variableCategoryName
     * @param null $requiredText
     * @throws InvalidVariableNameException
     */
    public function getMessagesAndSaveMeasurements(string $query, string $variableCategoryName = null, $requiredText = null){
        $this->requiredText = $requiredText;
        $this->getMessagesAndConvertToMeasurementItems($query);
        // TODO: Why don't we saveQuantityMeasurementSets?
        $this->saveQuantityMeasurementSets();
        $this->savePaymentMeasurementSets();
    }
    /**
     * @return Google_Service_Gmail
     */
    public function getGmailService(){
	    $client = $this->getGoogleClient();
	    $gmailService = new Google_Service_Gmail($client);
        // TODO: Google_Service_Gmail won't instantiate right in API requests but it seems to work in PHPUnit/cli
        return $gmailService;
    }
    /**
     */
    public function saveQuantityMeasurementSets(){
        foreach($this->quantityMeasurementItems as $itemName => $measurementItems){
            if($this->stringEqualsTestVariableName($itemName)){
                break;
            }
        }
    }
    /**
     * @param string $itemName
     * @return bool
     */
    protected static function containsExcludedItemName(string $itemName): bool {
        $excludedItemNames = [
            "ESTIMATED TOTAL"
        ];
        foreach($excludedItemNames as $excludedItemName){
            $contains = stripos($itemName, $excludedItemName) !== false;
            if($contains){
                return true;
            }
        }
        return false;
    }
    /**
     * @throws InvalidVariableNameException
     */
    public function savePaymentMeasurementSets(){
        $paymentMeasurementSets = [];
        if(empty($this->paymentMeasurementItems)){
            $this->logInfo("No Payment measurements");
            return;
        }
        foreach($this->paymentMeasurementItems as $itemName => $measurementItems){
            $paymentMeasurementSets[] = new MeasurementSet($itemName." (".PaymentsVariableCategory::NAME.")",
                $measurementItems, DollarsUnit::NAME, PaymentsVariableCategory::NAME);
            if($this->stringEqualsTestVariableName($itemName)){
                break;
            }
        }
        if(!empty($paymentMeasurementSets)){
            $this->saveMeasurementSets($paymentMeasurementSets);
        }
    }
    /**
     * @param string $string
     * @return bool
     */
    public function stringEqualsTestVariableName(string $string){
        if(!AppMode::isTestingOrStaging()){
            return false;
        }
        return self::TEST_VARIABLE === $string;
    }
    /**
     * @param $opt_param
     * @return Google_Service_Gmail_Message[]
     */
    public function getMessages($opt_param){
        $response = $this->setMessagesResponse($opt_param);
        /** @var Google_Service_Gmail_Message[] $currentMessages */
        $currentMessages = $response->getMessages();
        return $currentMessages;
    }
    /**
     * @param $opt_param
     * @return Google_Service_Gmail_ListMessagesResponse
     */
    public function getMessagesResponse($opt_param){
        return $this->messagesResponse ?: $this->setMessagesResponse($opt_param);
    }
    /**
     * @param $opt_param
     * @return Google_Service_Gmail_ListMessagesResponse
     */
    public function setMessagesResponse($opt_param){
        $service = $this->getGmailService();
        $messagesResponse = $service->users_messages->listUsersMessages($this->gmailUserId, $opt_param);
        return $this->messagesResponse = $messagesResponse;
    }
    /**
     * @param $opt_param
     * @return Google_Service_Gmail_Message[]
     */
    public function getAllMessages($opt_param): array{
        $allMessages = [];
        $pageToken = NULL;
        do{
            if($pageToken){
                $opt_param['pageToken'] = $pageToken;
            }
            $currentMessages = $this->getMessages($opt_param);
            if($currentMessages){
                /** @noinspection SlowArrayOperationsInLoopInspection */
                $allMessages = array_merge($allMessages, $currentMessages);
                $pageToken = $this->getMessagesResponse($opt_param)->getNextPageToken();
            }
        }while($pageToken);
        return $this->allMessages = $allMessages;
    }
    /**
     * @return string|null
     */
    public function getTextOrHtmlBody(){
        $text = $this->getPlainTextMessage();
        if(!$text){
            $text = $this->getDecodedRawMessageWithHtml();
        }
        return $text;
    }
}
