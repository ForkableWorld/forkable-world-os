<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller;
use App\VariableRelationships\QMGlobalVariableRelationship;
use App\Exceptions\NotEnoughMeasurementsException;
use App\Files\FileHelper;
use App\Files\MimeContentTypeHelper;
use App\Http\Parameters\IncludeChartsParam;
use App\Http\Parameters\IncludeTagsParam;
use App\Properties\Study\StudyIdProperty;
use App\Properties\Study\StudyTypeProperty;
use App\Slim\Middleware\QMAuth;
use App\Slim\QMSlim;
use App\Slim\View\Request\QMRequest;
use App\Types\ObjectHelper;
use App\Utils\APIHelper;
use App\Variables\QMUserVariable;
use App\Variables\QMVariable;
use App\Variables\QMVariableCategory;
use App\Variables\VariableSearchResult;
use Spatie\MediaLibrary\MediaCollections\Exceptions\MimeTypeNotAllowed;
/** Class GetController
 * @package App\Slim\Controller
 */
abstract class GetController extends Controller {
	public const DEFAULT_LIMIT = 100;
	/**
	 * Initialize the get request and call the abstract function 'get' to continue processing.
	 * This function should be called through the Slim framework.
	 * @noinspection PhpUnused
	 */
	final public function initGet(){
		$this->get();
	}
	/**
	 * Handle the GET request.
	 */
	abstract public function get();
	/**
	 * @return bool
	 */
	public static function getCommonOnly(): ?bool{
		$studyId = StudyIdProperty::fromRequest();
		if($studyId && StudyTypeProperty::fromIdOrUrl($studyId) === StudyTypeProperty::TYPE_POPULATION){
			return true;
		}
		if(!QMAuth::getQMUser()){
			return true;
		}
		if(QMRequest::getParam('open')){
			return true;
		}
		return QMRequest::getParam('commonOnly');
	}
	/**
	 * @return bool
	 */
	public function getUserOnly(): bool{
		return (bool)$this->getApp()->request()->get('userOnly');
	}
	/**
	 * @return string|null
	 */
	public function getVariableName(): ?string{
		$variableName = static::getRequestParam('variableName', static::getRequestParam('name'));
		if($variableName && strpos($variableName, "%") === false){
			return $variableName;
		}
		return null;
	}
	/**
	 * @return int|null
	 */
	public function getVariableId(): ?int{
		return $this->getParamInt('variableId');
	}
	/**
	 * @return int|string
	 */
	public function getVariableNameOrId(){
		if($this->getVariableName()){
			return $this->getVariableName();
		}
		$variableId = $this->getVariableId();
		return $variableId;
	}
	/**
	 * @return QMUserVariable|bool
	 */
	public function setUserVariable(){
		$nameOrId = $this->getVariableNameOrId();
		if(!$nameOrId){
			return $this->userVariable = false;
		}
		$params = QMRequest::getQueryParams();
		$uv = QMUserVariable::findOrCreateByNameOrId(QMAuth::id(true), $nameOrId, $params);
		if($uv){
			if(IncludeTagsParam::includeTags()){
				$uv->getAllCommonAndUserTagVariableTypes();
			}
			if(IncludeChartsParam::includeCharts()){
				$uv->getAllCommonAndUserTagVariableTypes();
				try {
					$cg = $uv->getChartGroup();
					$cg->getOrSetHighchartConfigs();
				} catch (NotEnoughMeasurementsException $e) {
					$this->logError(__METHOD__.": ".$e->getMessage());
				}
			}
		}
		return $this->userVariable = $uv;
	}
	/**
	 * @return QMUserVariable|bool
	 */
	public function getUserVariable(){
		if($this->userVariable !== null){
			return $this->userVariable;
		}
		return $this->setUserVariable();
	}
	/**
	 * @param array $params
	 * @return QMGlobalVariableRelationship[]
	 */
	public function getAggregatedCorrelations(array $params): array{
		$params['numberOfUsers'] = '(gt)1';
		$correlations = QMGlobalVariableRelationship::getGlobalVariableRelationships($params);
		if(!count($correlations)){
			unset($params['numberOfUsers']);
			$correlations = QMGlobalVariableRelationship::getGlobalVariableRelationships($params);
		}
		if(!QMRequest::urlContains('/studies')){ // This will be done in the study instantiation
			foreach($correlations as $correlation){
				$correlation->addStudyHtmlChartsImages();
			}
		}
		return $correlations;
	}
	/**
	 * @param QMVariable[] $variables
	 */
	protected function returnVariables(array $variables){
		if(APIHelper::apiVersionIsBelow(3)){
			ObjectHelper::addLegacyPropertiesToObjectsInArray($variables);
		}
		if(QMRequest::getParam('concise') && $variables){
			$variables = VariableSearchResult::convertVariablesToSearchResults($variables);
		}
		return $this->writeJsonWithoutGlobalFields(200, $variables);
	}
	/**
	 * @return null|QMVariableCategory
	 */
	public function getQMVariableCategory(): ?QMVariableCategory{
		return QMRequest::getQMVariableCategory();
	}
	/**
	 * @return null|string
	 */
	public function getSearchPhrase(): ?string{
		$search = QMRequest::getSearchPhrase();
		return $search;
	}
	/**
	 * @param $data
	 */
	public function write($data){
		$this->getApp()->writeJsonWithGlobalFields(200, $data);
	}
	/**
	 * @param string $filename
	 * @param $data
	 * @throws MimeTypeNotAllowed
	 */
	public static function outputToBrowser(string $filename, $data){
		$type = MimeContentTypeHelper::guessMimeContentTypeBasedOnFileName($filename);
        header('Content-Type: '.$type);
		echo $data;
	}
	/**
	 * @param string $filename
	 * @param $data
	 * @throws MimeTypeNotAllowed
	 */
	public static function outputToBrowserOrDownload(string $filename, $data){
		$ext = FileHelper::getExtension($filename);
		$typesToEcho = [
			"html",
			"htm",
			"svg",
			"jpg",
			"png",
			"txt",
			"gif",
			"pdf",
		];
		if(in_array($ext, $typesToEcho)){
			GetController::outputToBrowser($filename, $data);
		} else{
			GetController::downloadFile($filename, $data);
		}
	}
	/**
	 * @param string $filepath
	 * @param $content
	 * @throws MimeTypeNotAllowed
	 */
	public static function downloadFile(string $filepath, $content){
		$type = MimeContentTypeHelper::guessMimeContentTypeBasedOnFileName($filepath);
		header('Content-type: application/force-download');
		header('Content-type: application/octet-stream');
		header('Content-Description: application/download');
		header('Content-type: application/force-download');
		header('Content-type: ' . $type);
		header('Content-Description: File Transfer');
		header('Content-Disposition: attachment; filename="' . basename($filepath) . '"');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		echo $content;
	}
	protected function getLimit(): int{
		$limit = $this->getParamInt('limit');
		if($limit === null){
			$limit = static::DEFAULT_LIMIT;
		}
		return $limit;
	}
	protected function getOffset(): int{
		$offset = $this->getParamInt('offset');
		if($offset === null){
			$offset = 0;
		}
		return $offset;
	}
}
