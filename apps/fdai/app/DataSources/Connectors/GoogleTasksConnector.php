<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataSources\Connectors;
use App\DataSources\GoogleBaseConnector;
use App\Exceptions\CredentialsNotFoundException;
use App\Slim\Model\Measurement\AdditionalMetaData;
use App\Units\CountUnit;
use App\VariableCategories\GoalsVariableCategory;
use App\Variables\QMUserVariable;
use Google_Service_Tasks;
use Google_Service_Tasks_Task;
class GoogleTasksConnector extends GoogleBaseConnector {
    protected const BACKGROUND_COLOR = '#2c6efc';
	protected const DEFAULT_VARIABLE_CATEGORY_NAME = 'Activities';
	public const DISPLAY_NAME = 'Google Tasks';
    protected const ENABLED = 0;
	protected const GET_IT_URL = 'https://calendar.google.com';
	protected const LOGO_COLOR = '#d34836';
	protected const LONG_DESCRIPTION = 'Use Google Tasks to automatically track repeated events like treatments.';
	protected const SHORT_DESCRIPTION = 'Automate your tracking by creating calendar events with a title containing the value, followed by the unit, followed by the variable name. To track an apple every day, create a repeating event called "1 serving Apples."';
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
    public $providesUserProfileForLogin = false;
    public $shortDescription = self::SHORT_DESCRIPTION;
    public const ID = 88;
    public const IMAGE = 'https://www.gstatic.com/images/branding/product/1x/tasks_96dp.png?dcb_=0.21458668271868886';
    public const NAME = 'google-tasks';
    public static array $SCOPES = [Google_Service_Tasks::TASKS_READONLY];
    /**
     * @return int|QMUserVariable[]
     * @throws CredentialsNotFoundException
     */
    public function importData(): void {
        $listIds = $this->getListIds();
        foreach($listIds as $listId){
            $this->saveTasks($listId);
        }
        $this->saveMeasurements();
    }
    /**
     * @return array
     * @throws CredentialsNotFoundException
     */
    private function getListIds(): array {
        $service = $this->getTaskService();
        $results = $service->tasklists->listTasklists([
            'maxResults' => 100,
        ]);
        $ids = [];
        foreach ($results->getItems() as $list){
            $ids[] = $list->getId();
        }
        return $ids;
    }
    /**
     * @param $listId
     * @throws CredentialsNotFoundException
     */
    private function saveTasks(string $listId){
        $tasks = $this->getTasks($listId);
        foreach($tasks as $task){
            $v = $this->getQMUserVariable("Google Task Created or Updated",
                                          CountUnit::NAME, GoalsVariableCategory::NAME);
            $this->addTaskMeasurementItem($task, $v, $task->getUpdated());
            if($task->getCompleted()){
                $v = $this->getQMUserVariable("Google Task Completed",
                                              CountUnit::NAME, GoalsVariableCategory::NAME);
                $this->addTaskMeasurementItem($task, $v, $task->getCompleted());
            }
        }
    }
	/**
	 * @return Google_Service_Tasks
	 * @throws CredentialsNotFoundException
	 * @throws \OAuth\Common\Storage\Exception\TokenNotFoundException
	 */
    private function getTaskService(): Google_Service_Tasks{
        // Get the API client and construct the service object.
        $client = $this->getGoogleClient();
        $service = new Google_Service_Tasks($client);
        return $service;
    }
	/**
	 * @param Google_Service_Tasks_Task $task
	 * @param QMUserVariable $v
	 * @param $startTime
	 * @throws \App\Exceptions\InvalidVariableValueAttributeException
	 */
    private function addTaskMeasurementItem(Google_Service_Tasks_Task $task, QMUserVariable $v, $startTime): void{
        $m = $this->generateMeasurement($v, $startTime, 1, CountUnit::NAME);
        $note = new AdditionalMetaData();
        $note->setUrl($task->getSelfLink());
        $message = $task->getTitleAttribute();
        if(!empty($task->getNotes())){$message .= " Notes: ".$task->getNotes();}
        $note->setMessage($message);
        $m->setAdditionalMetaData($note);
        $v->addToMeasurementQueueIfNoneExist($m);
    }
    /**
     * @param string $listId
     * @return Google_Service_Tasks_Task
     * @throws CredentialsNotFoundException
     */
    private function getTasks(string $listId): Google_Service_Tasks_Task{
        $service = $this->getTaskService();
        $tasksList = $service->tasks->listTasks($listId);
        $tasks = $tasksList->getItems();
        return $tasks;
    }
}
