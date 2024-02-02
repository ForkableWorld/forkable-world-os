<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Intents;
use App\Cards\QMListCard;
use App\Exceptions\ExceptionHandler;
use App\Files\FileHelper;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\DBModel;
use App\Slim\Model\Reminders\QMTrackingReminder;
use App\Slim\Model\Reminders\QMTrackingReminderNotification;
use App\Slim\Model\User\QMUser;
use App\Slim\View\Request\QMRequest;
use App\Types\QMArr;
use App\Types\QMStr;
use App\VariableCategories\EmotionsVariableCategory;
use App\VariableCategories\FoodsVariableCategory;
use App\VariableCategories\SymptomsVariableCategory;
use App\VariableCategories\TreatmentsVariableCategory;
use App\Variables\QMVariableCategory;
use Dialogflow\Action\Conversation;
use Dialogflow\Action\Dialogflow\Action\Surface;
use Dialogflow\Action\Responses\SimpleResponse;
use Dialogflow\Context;
use Dialogflow\RichMessage\Text;
use Dialogflow\WebhookClient;
use Error;
abstract class QMIntent extends DBModel {
    public const CONTEXT_CREATE_REMINDER = 'create_reminder';
    public const CONTEXT_tracking_reminder_notification = 'tracking_reminder_notification';
    private $newNotification;
    private $webhookClient;
    protected $commonVariableFromQuery;
    protected $submittedNotification;
    public $triggerPhrases;
    public $outgoingMessage = '';
    public function __construct(){
        $this->fulfillIntent();
        $this->handleResponse();
    }
    /**
     * @return mixed
     */
    abstract public function fulfillIntent();
    /**
     * @param array|string $paramNames
     * @param bool $lowercase
     * @return mixed
     */
    public function getParam(array|string $paramNames, bool $lowercase = true){
        if(is_string($paramNames)){
            $paramNames = [$paramNames];
        }
        $params = $this->getWebhookClient()->getParameters();
        $value = QMArr::getValue($params, $paramNames);
        if(is_string($value) && $lowercase){
            $value = strtolower($value);
        }
        return $value;
    }
    /**
     * @return QMTrackingReminderNotification|bool
     */
    public function getNewNotification(){
        if($this->newNotification === null){
            $this->setNewNotification();
        }
        return $this->newNotification;
    }
    /**
     * @param QMTrackingReminderNotification|null $notification
     * @return bool|QMTrackingReminderNotification
     */
    public function setNewNotification(QMTrackingReminderNotification $notification = null){
        if($notification){
            return $this->newNotification = $notification;
        }
        $u = QMAuth::getQMUser();
        $notification = $u->getMostRecentPendingNotification($this->getSetSubmittedNotificationVariableId());
        if(!$notification){
            return $this->newNotification = false;
        }
        return $this->newNotification = $notification;
    }
    /**
     * @param string $appendMe
     * @return string
     */
    public function appendToOutgoingMessage(string $appendMe): string{
        if(stripos($this->outgoingMessage, $appendMe) !== false){
            return $this->outgoingMessage;
        }
        return $this->outgoingMessage .= " ".$appendMe;
    }
    /**
     * @return bool|int
     */
    private function getSetSubmittedNotificationVariableId(){
        if($n = $this->getSubmittedNotification()){
            return $n->getVariableIdAttribute();
        }
        return false;
    }
    /**
     * @return WebhookClient
     */
    public function getWebhookClient(): WebhookClient{
        return $this->webhookClient ?: $this->setWebhookClient();
    }
    /**
     * @return WebhookClient
     */
    public function setWebhookClient(): WebhookClient{
        return $this->webhookClient = QMRequest::getWebHookClient();
    }
    /**
     * @param string $name
     * @param bool $lowerCase
     * @return null|string
     */
    public function getWebhookParamFromAnyContext(string $name, bool $lowerCase = true): ?string{
        $parameters = $this->getWebhookClient()->getParameters();
        $value = QMArr::getValueForSnakeOrCamelCaseKey($parameters, $name);
        if($value === null){
            $parametersForAllContexts = $this->getWebhookParamsForAllContexts();
            $value = QMArr::getValueForSnakeOrCamelCaseKey($parametersForAllContexts, $name);
        }
        if(is_string($value) && $lowerCase){
            $value = strtolower($value);
        }
        return $value;
    }
    /**
     * @return null
     */
    public function getWebhookParamsForAllContexts(){
        $parameters = $this->getWebhookClient()->getParameters();
        $contexts = $this->getContexts();
        if(!$contexts){
            return $parameters;
        }
        foreach($contexts as $context){
	        foreach($context->getParameters() as $key => $value){
                if($value !== null && $value !== ""){
                    $parameters[$key] = $value;
                }
            }
        }
        if(isset($parameters['OPTION']) && !isset($parameters['action'])){
            $parameters['action'] = $parameters['OPTION'];
        }
        $parameters['query'] = $this->getQuery(true);
        return $parameters;
    }
    /**
     * @param bool $lowerCase
     * @return string
     */
    protected function getQuery(bool $lowerCase = true): string{
        $agent = $this->getWebhookClient();
        if('Get Option' == $agent->getIntent()){  // For Google Assistant
            $conv = $agent->getActionConversation();
            if($conv && $conv->getArguments()){
                $option = $conv->getArguments()->get('OPTION');
            }
            if(isset($agent->getOriginalRequest()["payload"]["inputs"][0]["rawInputs"][0]["query"])){
                $query = $agent->getOriginalRequest()["payload"]["inputs"][0]["rawInputs"][0]["query"];
            }
        }
        if(!isset($query)){
            $query = $agent->getQuery();
        }
        if(is_string($query) && $lowerCase){
            $query = strtolower($query);
        }
        return $query;
    }
    /**
     * @return Conversation
     */
    public function getConversation(): ?Conversation{
        $agent = $this->getWebhookClient();
        $conversation = null;
        if($agent->getRequestSource() == 'google'){
            $conversation = $agent->getActionConversation();
        }
        return $conversation;
    }
    protected function respondWithNotification(){
        $n = $this->getNewNotification();
        if(!$n){return;}
        $when = $n->getTrackingReminderNotificationTimeLocalHumanString();
        $message = $n->getLongQuestion($this->getSurface());
        $buttons = $n->getVoiceOptionButtons();
        $listCard = $n->getOptionsListCard()->getOptionsListCard($buttons);
        $suggestions = $n->getSuggestions();
        $this->addMessage($message, $listCard, $suggestions);
        $this->setOutgoingContext(self::CONTEXT_tracking_reminder_notification, [
            'variableName'                   => $n->getVariableName(),
            'trackingReminderNotificationId' => $n->id,
            'unitName'                       => $n->getUserUnit()->name
        ]);
    }
    /**
     * @return Surface|null
     */
    private function getSurface(){
        $conversation = $this->getConversation();
        if($conversation){
            return $conversation->getSurface();
        }
        return null;
    }
    protected function respondWithInstructions(){
        $message = "To add a new treatment or symptom, just say something like Add Aspirin or Add Overall Mood.  ".
            "Then I'll ask you about it each day and analyze the data to discover hidden causes and solutions! ".
            "Additionally, there's a lot more you can do at app.quantimo.do.  ";
        $card = QMAuth::getQMUser()->getBasicCardWithLinkedButtons();
        $this->addMessage($message, $card);
    }
    /**
     * @param string $variableCategoryName
     */
    protected function askToCreateReminder(string $variableCategoryName){
        $category = QMVariableCategory::find($variableCategoryName);
        $message = "Aside from any you've already added, ".strtolower($category->getSetupQuestion()).
            " If you don't have any more to add, just say Done with $variableCategoryName.  "
            //."You can add another in the future by saying Add a ".$category->variableCategoryNameSingular.".  "
        ;
        $this->addMessage($message);
        $this->setOutgoingContext(self::CONTEXT_CREATE_REMINDER, [
            'variableCategoryName' => $variableCategoryName
        ]);
    }
    /**
     * @param string $message
     * @param QMListCard $card
     * @param null $suggestions
     */
    protected function addMessage(string $message, $card = null, $suggestions = null){
        $agent = $this->getWebhookClient();
        $conversation = $this->getConversation();
        $source = $agent->getRequestSource();
        if($conversation){
            $surface = $conversation->getSurface();
            $device = $conversation->getDevice();
            $message = $this->appendToOutgoingMessage($message);  // Google can't handle multiple messages for some reason
            $conversation->add(SimpleResponse::create()->displayText($message)->ssml('<speak>'.$message.'</speak>'));
            if($card){
                $conversation->ask($card);
            }
            $agent->reply($conversation);
        }else{
            $message = " ".$message." ";
            $text = Text::create()->text($message)->ssml('<speak>'.$message.'</speak>');
            $agent->reply($text);
            if($suggestions){
                $agent->reply($suggestions);
            }
        }
    }
    /**
     * @return QMTrackingReminderNotification|QMTrackingReminder
     */
    public function getSubmittedNotification(){
        return $this->submittedNotification;
    }
    /**
     * @return string
     */
    protected function getContextName(): string {
        $className = $this->getShortClassName();
        $contextName = QMStr::snakize(str_replace("Intent", "", $className));
        return $contextName;
    }
    /**
     * @return bool|Context
     */
    protected function getContext(){
        $contexts = $this->getContexts();
        if(!$contexts){
            return false;
        }
        $contextImLookingFor = $this->getContextName();
        foreach($contexts as $context){
            $contextNameToCheck = $context->getName();
            $contextNameToCheck = QMStr::after('contexts/', $contextNameToCheck, $contextNameToCheck);
            if($contextNameToCheck === $contextImLookingFor){
                return $context;
            }
        }
        return false;
    }
    /**
     * @return array|null
     */
    protected function getContextParams(): ?array{
        $context = $this->getContext();
        if(!$context){
            return null;
        }
        return $context->getParameters();
    }
    /**
     * @param string $name
     * @return mixed
     */
    protected function getContextParam(string $name){
        $params = $this->getContextParams();
        if(!$params){
            return null;
        }
        return QMArr::getValue($params, [$name]);
    }
    /**
     * @return null|QMUser
     */
    protected function getUser(): ?QMUser{
        return QMAuth::getQMUser();
    }
    /**
     * @param string $name
     * @param array $parameters
     */
    protected function setOutgoingContext(string $name, array $parameters = []){
        $context = [
            'lifespan'   => 5,
            'name'       => $name,
            'parameters' => $parameters
        ];
        $this->getWebhookClient()->setOutgoingContext($context);
    }
    /**
     * @return Context[]
     */
    protected function getContexts(): array{
        return $this->getWebhookClient()->getContexts();
    }
    protected function handleResponse(){
        if($this->getNewNotification()){
            $this->respondWithNotification();
        }elseif($this->weShouldAskToCreateForCategory(EmotionsVariableCategory::NAME)){
            $this->askToCreateReminder(EmotionsVariableCategory::NAME);
        }elseif($this->weShouldAskToCreateForCategory(FoodsVariableCategory::NAME)){
            $this->askToCreateReminder(FoodsVariableCategory::NAME);
        }elseif($this->weShouldAskToCreateForCategory(SymptomsVariableCategory::NAME)){
            $this->askToCreateReminder(SymptomsVariableCategory::NAME);
        }elseif($this->weShouldAskToCreateForCategory(TreatmentsVariableCategory::NAME)){
            $this->askToCreateReminder(TreatmentsVariableCategory::NAME);
        }else{
            $this->respondWithInstructions();
        }
    }
    /**
     * @param string $variableCategoryName
     * @return bool
     */
    private function weShouldAskToCreateForCategory(string $variableCategoryName): bool{
        $done = $this->saidDoneWithCategory($variableCategoryName);
        $u = $this->getUser();
        return !$done && !$u->getDoneAddingCategory($variableCategoryName);
    }
    /**
     * @param string $variableCategoryName
     * @return bool
     */
    protected function saidDoneWithCategory(string $variableCategoryName): bool{
        $category = QMVariableCategory::findByNameOrSynonym($variableCategoryName);
        if(!$category){
            return false;
        }
        if(stripos($this->getQuery(), 'done') !== false && stripos($this->getQuery(),
                $category->getNameSingular()) !== false){
            $this->getUser()->setDoneAddingCategory($category->name);
            return true;
        }
        return false;
    }
    /**
     * @param WebhookClient $webhookClient
     * @return QMIntent
     */
    public static function fulfillAndResponseToIntent(WebhookClient $webhookClient){
        $className = $webhookClient->getIntent();
        $short = str_replace(' ', '', $className);
        $ns = FileHelper::classToNamespace(QMIntent::class);
        $className = $ns."\\".$short;
        try {
            /** @var QMIntent $intent */
            $intent = new $className;
        } catch (Error $e) {
            ExceptionHandler::logExceptionOrThrowIfLocalOrPHPUnitTest($e);
            return new DefaultFallbackIntent();
        }
        return $intent;
    }
    /**
     * @return object
     * @noinspection PhpDocMissingThrowsInspection
     */
    public static function getAgent(): object {
        /** @noinspection PhpUnhandledExceptionInspection */
        $agent = FileHelper::readJsonFile('data/agents/dr-modo-agent.json');
        return $agent;
    }
}
