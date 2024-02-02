<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DevOps\Jenkins;
use App\Logging\QMLog;
use RuntimeException;
use stdClass;
class JenkinsQueue {
	private static $queuedItems;
	/**
	 * @var JenkinsAPI
	 */
	public $jenkins;
	public $items;
	/**
	 * @param stdClass $response
	 * @param JenkinsAPI $jenkins
	 */
	public function __construct(stdClass $response, JenkinsAPI $jenkins){
		foreach($response as $key => $value){
			$this->$key = $value;
		}
		$this->setJenkins($jenkins);
	}
	/**
	 * @paramJenkinsQueueItem $queue
	 * @return void
	 * @throws RuntimeException
	 */
	public static function cancelQueue(JenkinsQueueItem $queue){
		$url = sprintf('%s/queue/item/%s/cancelQueue', JenkinsAPI::$baseUrl, $queue->getId());
		$curl = Jenkins::post($url);
		JenkinsAPI::validateCurl($curl, sprintf('Error during stopping job queue #%s', $queue->getId()), $url);
	}
	public static function getQueuedItemsByJobName(string $jobName = null): array{
		if(!$jobName){
			$jobName = JenkinsJob::getCurrentJobName();
		}
		$all = self::getQueuedItems();
		$matches = collect($all)->filter(function($item) use ($jobName){
			return $item->task->name === $jobName;
		})->all();
		QMLog::infoWithoutContext(count($matches) . " queued $jobName jobs");
		//from: ".\App\Logging\QMLog::print_r($all, true));
		return $matches;
	}
	public static function getQueuedItems(): array{
		if(self::$queuedItems){
			return self::$queuedItems;
		}
		$items = self::getQueue()->items;
		//QMLog::print($items, "Queued Jobs");
		QMLog::infoWithoutContext(count($items) . " Queued Jobs");
		return self::$queuedItems = $items;
	}
	/**
	 * @returnJenkinsQueue
	 * @throws RuntimeException
	 */
	public static function getQueue(): JenkinsQueue{
		$url = sprintf('%s/queue/api/json', JenkinsAPI::$baseUrl);
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$ret = curl_exec($curl);
		JenkinsAPI::validateCurl($curl,
			sprintf('Error during getting information for queue on %s', JenkinsAPI::$baseUrl), $url);
		$infos = json_decode($ret);
		$className = "Queue";
		JenkinsAPI::generateStaticModelFromResponse($className, $infos);
		if(!$infos instanceof stdClass){
			throw new RuntimeException('Error during json_decode');
		}
		return new JenkinsQueue($infos, JenkinsAPI::getInstance());
	}
	public static function abortJobsLike(string $pattern): array{
		return Build::abortBuildsForJobsLike($pattern);
	}
	/**
	 * @return array
	 */
	public function getJobQueues(): array{
		$jobs = [];
		foreach($this->items as $item){
			$jobs[] = new JenkinsQueueItem($item, $this->getJenkins());
		}
		return $jobs;
	}
	/**
	 * @return JenkinsAPI
	 */
	public function getJenkins(): JenkinsAPI{
		return $this->jenkins;
	}
	/**
	 * @param JenkinsAPI $jenkins
	 * @return JenkinsQueue
	 */
	public function setJenkins(JenkinsAPI $jenkins): JenkinsQueue{
		$this->jenkins = $jenkins;
		return $this;
	}
}
