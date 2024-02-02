<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn 
 */

namespace App\Repos;
use App\AppSettings\AppSettings;
use App\Exceptions\InvalidStringException;
use App\Logging\QMLog;
use App\Models\Application;
use App\Models\Variable;
use App\Models\Vote;
use App\Properties\Variable\VariableNameProperty;
use App\Utils\IonicHelper;
use App\Utils\UrlHelper;
use Illuminate\View\View;
class CCStudiesRepo extends GitRepo {
	public const USERNAME = 'crowdsourcing-cures';
	public static $REPO_NAME = 'crowdsourcing-cures-studies';
	public const DEFAULT_BRANCH = 'main';
	const APP_URL = IonicHelper::APP_CROWDSOURCINGCURES_ORG;
	public static function publishUpVotedStudies(){
		$votes = Vote::whereValue(1)->groupBy([Vote::FIELD_CAUSE_VARIABLE_ID, Vote::FIELD_EFFECT_VARIABLE_ID])->get();
		foreach($votes as $v){
			$v->publishToCC();
		}
	}
	/**
	 * @param int|string $nameOrId
	 * @throws InvalidStringException
	 */
	public static function publishVariable($nameOrId){
		$variable = Variable::findByNameOrId($nameOrId);
		$variable->logInfo("Publishing $variable->name");
		$predictors = $variable->getPublicPredictors();
		$outcomes = $variable->getPublicOutcomes();
		if(!$predictors->count() && !$outcomes->count()){
			le("No predictors or outcomes!");
		}
		static::writeHtml($variable->getShowPath() . "/index.html", $variable->getShowPageHtml(['inline' => false]));
		static::writeToFile($variable->getShowPath() . "/data.js", $variable->getShowJs());
	}
	protected static function getBlackListedStrings(array $repoSpecific = []): array{
		$arr = array_merge(VariableNameProperty::PRIVATE_NAMES_LIKE, [
				'/datalab/',
				// Have to allow this because it's a data source name 'QuantiModo',
				'/variables/variables/',
				UrlHelper::LOCAL_QM_HOST,
				UrlHelper::STAGING_QM_HOST,
				UrlHelper::APP_QM_HOST,
			]);
		return parent::getBlackListedStrings($arr);
	}
	/**
	 * @param string $str
	 * @return string
	 */
	public static function replaceUrls(string $str): string{
		$arr = [
			UrlHelper::LOCAL_QM_HOST => UrlHelper::STUDIES_CROWDSOURCING_CURES_HOST,
			UrlHelper::STAGING_QM_HOST => UrlHelper::STUDIES_CROWDSOURCING_CURES_HOST,
			UrlHelper::APP_QM_HOST => UrlHelper::STUDIES_CROWDSOURCING_CURES_HOST,
			UrlHelper::TESTING_QM_HOST => UrlHelper::STUDIES_CROWDSOURCING_CURES_HOST,
			IonicHelper::IONIC_BASE_URL => self::APP_URL,
		];
		foreach($arr as $search => $replace){
			$str = str_replace($search, $replace, $str);
		}
		$str =
			str_replace("static." . UrlHelper::CROWDSOURCING_CURES_HOSTNAME, "static." . UrlHelper::QM_APEX_HOST, $str);
		return $str;
	}
	/**
	 * @param string $filepath
	 * @param mixed $content
	 * @return string
	 * @throws InvalidStringException
	 */
	public static function writeToFile(string $filepath, $content): string{
		$content = static::replaceQMWithCC($content);
		self::validateString($content, $filepath);
		return parent::writeToFile($filepath, $content);
	}
	/**
	 * @param string $path
	 * @param View|string $html
	 * @throws InvalidStringException
	 */
	public static function writeHtml(string $path, $html){
		$html = static::replaceQMWithCC($html);
		self::validateString($html, $path);
		parent::writeHtml($path, $html);
	}
	/**
	 * @throws InvalidStringException
	 */
	public static function publishVariablesIndex(){
		$indexHtml = Variable::generateIndexHtml();
		static::writeHtml("index.html", $indexHtml);
	}
	/**
	 * @throws InvalidStringException
	 */
	public static function publishVariables(){
		$models = Variable::getIndexModels();
		foreach($models as $model){
			$indexHtmlPath = $model->getShowPath() . "/index.html";
			if(static::fileExists($indexHtmlPath)){
				QMLog::info("$indexHtmlPath already exists.  Skipping $model->name");
				continue;
			}
			static::publishVariable($model->getId());
		}
	}
	/**
	 * @param string $str
	 * @return string
	 */
	public static function replaceQMWithCC(string $str): string{
		$cc = Application::crowdsourcingCures();
		$qm = Application::qm();
		$str = str_replace($qm->getHomepageUrl(), $cc->getHomepageUrl(), $str);
		$str = str_replace($qm->getSubtitleAttribute(), $cc->getSubtitleAttribute(), $str);
		$str = str_replace($qm->getTitleAttribute(), $cc->getTitleAttribute(), $str);
		$str = self::replaceUrls($str);
		return $str;
	}
}
