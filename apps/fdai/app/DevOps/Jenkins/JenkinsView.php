<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn 
 */

namespace App\DevOps\Jenkins;
use App\Utils\UrlHelper;
use RuntimeException;
use stdClass;
class JenkinsView {
	public $name;
	public $jobs;
	public $url;
	public $description;
	protected $jenkins;
	/**
	 * @param $response
	 * @param JenkinsAPI|null $jenkins
	 */
	public function __construct($response, JenkinsAPI $jenkins = null){
		foreach($response as $key => $value){
			$this->$key = $value;
		}
		if($jenkins){
			$this->jenkins = $jenkins;
		}
	}
	/**
	 * getUrlView
	 * @param string $viewName
	 * @return string
	 */
	public static function getUrlForView(string $viewName): string{
		return sprintf('%s/view/%s', JenkinsAPI::$baseUrl, $viewName);
	}
	/**
	 * @returnJenkinsView|null
	 */
	public static function getPrimaryView(): ?JenkinsView{
		$response = JenkinsAPI::getGeneralData();
		$primaryView = null;
		if(property_exists($response, 'primaryView')){
			$primaryView = self::getView($response->primaryView->name);
		}
		return $primaryView;
	}
	/**
	 * @param string $viewName
	 * @return JenkinsView
	 * @returnJenkinsView
	 */
	public static function getView(string $viewName): JenkinsView{
		$url = sprintf('%s/view/%s/api/json', JenkinsAPI::$baseUrl, rawurlencode($viewName));
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$ret = curl_exec($curl);
		JenkinsAPI::validateCurl($curl,
			sprintf('Error during getting information for view %s on %s', $viewName, JenkinsAPI::$baseUrl), $url);
		$infos = json_decode($ret);
		JenkinsAPI::generateStaticModelFromResponse("View", $infos);
		if(!$infos instanceof stdClass){
			throw new RuntimeException('Error during json_decode');
		}
		return new JenkinsView($infos, JenkinsAPI::getInstance());
	}
	/**
	 * @returnJenkinsView[]
	 */
	public static function getViews(): array{
		$response = JenkinsAPI::getGeneralData();
		$views = [];
		foreach($response->views as $view){
			$views[] = self::getView($view->name);
		}
		return $views;
	}
	/**
	 * @return string
	 */
	public function getNameAttribute(): string{
		return $this->name;
	}
	/**
	 * @return string
	 */
	public function getSubtitleAttribute(): string{
		return $this->description;
	}
	/**
	 * @param array $params
	 * @return string
	 */
	public function getUrl(array $params = []): string{
		return UrlHelper::addParams($this->url, $params);
	}
	public function cancelBuildsForBranch(string $branch): array{
		$builds = $this->getBuildsForBranch($branch);
		foreach($builds as $build){
			$build->cancel();
		}
		return $builds;
	}
	/**
	 * @param string $branch
	 * @return Build[]
	 */
	public function getBuildsForBranch(string $branch): array{
		$jobs = $this->getJobs();
		$builds = [];
		foreach($jobs as $job){
			$builds[$job->name] = $job->getBuildsForBranch($branch);
		}
		return $builds;
	}
	/**
	 * @return JenkinsJob[]
	 */
	public function getJobs(): array{
		$jobs = [];
		foreach($this->jobs as $job){
			$jobs[] = JenkinsJob::getCompleteJobInfoFromAPI($job->name);
		}
		return $jobs;
	}
	/**
	 * getColor
	 * @return string
	 */
	public function getColor(): string{
		$color = 'blue';
		foreach($this->jobs as $job){
			if($this->getColorPriority($job->color) > $this->getColorPriority($color)){
				$color = $job->color;
			}
		}
		return $color;
	}
	/**
	 * getColorPriority
	 * @param string $color
	 * @return int
	 */
	protected function getColorPriority(string $color): int{
		switch($color) {
			default:
				return 999;
			case 'red_anime':
				return 11;
			case 'red':
				return 10;
			case 'yellow_anime':
				return 6;
			case 'yellow':
				return 5;
			case 'blue_anime':
				return 2;
			case 'blue':
				return 1;
			case 'disabled':
				return 0;
		}
	}
}
