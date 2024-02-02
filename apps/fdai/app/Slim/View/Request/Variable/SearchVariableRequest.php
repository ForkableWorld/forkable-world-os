<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\View\Request\Variable;
use App\Logging\QMLog;
use App\Slim\Middleware\QMAuth;
use App\Slim\QMSlim;
use App\Slim\View\Request\Request;
use InvalidArgumentException;
class SearchVariableRequest extends Request {
	private $name;
	/**
	 * @var int
	 */
	private $userId;
	/**
	 * @var string
	 */
	private $search;
	/**
	 * What we a looking for - effect or cause
	 * @var string
	 */
	private $effectOrCause;
	/**
	 * What we a looking for - effect or cause
	 * @var string
	 */
	private $publicEffectOrCause;
	/**
	 * @var string
	 */
	private $categoryName;
	/**
	 * @var string
	 */
	private $category;
	/**
	 * @var string
	 */
	private $sourceName;
	/**
	 * List of ids of the sources which should be exclude from search results
	 * @var array
	 */
	private $excludedSources;
	/**
	 * @var int
	 */
	private $limit;
	/**
	 * @var int
	 */
	private $offset;
	/**
	 * @var int
	 */
	private $includePublic;
	/**
	 * @var bool
	 */
	private $manualTracking;
	/**
	 * @var bool
	 */
	private $withGlobalVariableRelationships;
	/**
	 * @param string $causeName
	 * @return string|string[]
	 */
	public static function stripAstrix(string $causeName){
		return str_replace('*', '', $causeName);
	}
	/**
	 * @param string $str
	 * @return string
	 */
	public static function addWildCardsIfNecessary(string $str): string{
		if(!str_contains($str, "%")){
			$str = "%" . $str . "%";
		}
		return $str;
	}
	/**
	 * Populate this request's properties from an Application instance.
	 * Note: We exclude the following sources:
	 * world weather online - 38, github - 7, moodimodo - 23, sleep as android - 19, facebook - 30,
	 * whatpulse - 25, rescuetime - 34
	 * @param QMSlim $app
	 */
	public function populate(QMSlim $app){
		$this->setApplication($app);
		$search = null;
		try {
			$router = $app->router();
			$route = $router->getCurrentRoute();
			if($route){
				try {
					$search = $route->getParam('search');
				} catch (InvalidArgumentException $e) {
				    QMLog::info("could not get search param from route: " . $e->getMessage());
				}
			}
		} catch (InvalidArgumentException $e) {
			QMLog::error("Could not get current route: " . $e->getMessage());
		}
		$this->name = $this->getParam('name', $this->getParam('variableName'));
		if($search){
			$this->setSearch($search);
		}
		if($val = $this->getParam('effectOrCause', null)){
			$this->setEffectOrCause($val);
		} else{
			$this->setEffectOrCause($this->getParam('causeOrEffect', null));
		}
		$this->setPublicEffectOrCause($this->getParam('publicEffectOrCause', null));
		$this->setCategoryName($this->getParam('categoryName', null));
		if($this->getVariableCategoryName() === null){
			$this->setCategoryName($this->getParam('category', null));
		}
		if($this->getVariableCategoryName() === null){
			$this->setCategoryName($this->getParam('variableCategoryName', null));
		}
		$this->setSourceName($this->getParam('sourceName', null));
		$this->setIncludePublic($this->getParam('includePublic', false));
		$this->setManualTracking($this->getParam('manualTracking', false));
		//        if($this->getParam('manualTracking', false)){
		//            $this->setExcludedSources(array(7, 19, 23, 25, 30, 34, 38));
		//        }
		$this->setLimit($this->getParamNumeric('limit', 200, 'Limit must be numeric'));
		$this->setOffset($this->getParamNumeric('offset', 0, 'Offset must be numeric'));
		if($user = \Auth::user()){
			$this->setUserId($user->getUserId());
		}
	}
	/**
	 * @return string
	 */
	public function getSearch(): ?string{
		return $this->search;
	}
	/**
	 * @return string
	 */
	public function getEffectOrCause(): ?string{
		return $this->effectOrCause;
	}
	/**
	 * @return string
	 */
	public function getPublicEffectOrCause(): ?string{
		return $this->publicEffectOrCause;
	}
	/**
	 * @return string
	 */
	public function getVariableCategoryName(): ?string{
		return $this->categoryName;
	}
	/**
	 * @return string
	 */
	public function getSourceName(): ?string{
		return $this->sourceName;
	}
	/**
	 * @return array
	 */
	public function getExcludedSources(): array{
		return $this->excludedSources;
	}
	/**
	 * @return int
	 */
	public function getLimit(): int{
		return $this->limit;
	}
	/**
	 * @return int
	 */
	public function getOffset(): int{
		return $this->offset;
	}
	/**
	 * @param int $userId
	 * @return self
	 */
	public function setUserId(int $userId): SearchVariableRequest{
		$this->userId = $userId;
		return $this;
	}
	/**
	 * @param string $search
	 * @return SearchVariableRequest
	 */
	public function setSearch(string $search): SearchVariableRequest{
		if(!empty($search)){
			//$search = urldecode($search); // 
//			if(str_ends_with($search, '%')){
//				throw new QMException(QMException::CODE_BAD_REQUEST, 'Please do not include % symbols in search term!');
//			}
			$search = self::stripAstrix($search);
			$this->search = self::addWildCardsIfNecessary($search);
		}
		return $this;
	}
	/**
	 * @param string|null $effectOrCause
	 * @return self
	 */
	public function setEffectOrCause(?string $effectOrCause): SearchVariableRequest{
		$this->effectOrCause = $effectOrCause;
		return $this;
	}
	/**
	 * @param $publicEffectOrCause
	 * @return SearchVariableRequest
	 */
	public function setPublicEffectOrCause($publicEffectOrCause): SearchVariableRequest{
		$this->publicEffectOrCause = $publicEffectOrCause;
		return $this;
	}
	/**
	 * @param string $categoryName
	 * @return self
	 */
	public function setCategoryName(?string $categoryName): SearchVariableRequest{
		$this->categoryName = $categoryName;
		$this->category = $categoryName;
		return $this;
	}
	/**
	 * @param string $sourceName
	 * @return self
	 */
	public function setSourceName(?string $sourceName): SearchVariableRequest{
		if($sourceName != null){
			$this->sourceName = str_replace('*', '%', $sourceName);
		}
		return $this;
	}
	/**
	 * @param int[] $excludeSources
	 * @return self
	 */
	public function setExcludedSources(array $excludeSources): SearchVariableRequest{
		$this->excludedSources = $excludeSources;
		return $this;
	}
	/**
	 * @param int $limit
	 * @return self
	 */
	public function setLimit(int $limit): SearchVariableRequest{
		$this->limit = $limit;
		return $this;
	}
	/**
	 * @param int $offset
	 * @return self
	 */
	public function setOffset(int $offset): SearchVariableRequest{
		$this->offset = $offset;
		return $this;
	}
	/**
	 * @return string
	 */
	public function getCategory(): ?string{
		return $this->category;
	}
	/**
	 * @param string $category
	 */
	public function setCategory(string $category){
		$this->categoryName = $category;
		$this->category = $category;
	}
	/**
	 * @return bool
	 */
	public function getIncludePublic(): bool{
		return filter_var((string)$this->includePublic, FILTER_VALIDATE_BOOLEAN);
	}
	/**
	 * @param bool $includePublic
	 */
	public function setIncludePublic(?bool $includePublic){
		$this->includePublic = $includePublic;
	}
	/**
	 * @return bool
	 */
	public function getManualTracking(): bool{
		return $this->manualTracking;
	}
	/**
	 * @param bool $manualTracking
	 */
	public function setManualTracking(?bool $manualTracking){
		$this->manualTracking = $manualTracking;
	}
}
