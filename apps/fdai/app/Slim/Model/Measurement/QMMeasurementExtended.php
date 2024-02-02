<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\Model\Measurement;
use App\Buttons\QMButton;
use App\Buttons\States\MeasurementAddStateButton;
use App\DataSources\Connectors\QuantiModoConnector;
use App\DataSources\Connectors\TigerViewConnector;
use App\DataSources\QMDataSource;
use App\Logging\QMLog;
use App\Models\BaseModel;
use App\Slim\Model\QMUnit;
use App\UI\ImageHelper;
use App\Units\DollarsUnit;
use App\Units\OneToFiveRatingUnit;
use App\Utils\Stats;
use App\VariableCategories\PaymentsVariableCategory;
use App\Variables\QMUserVariable;
use App\Variables\QMVariableCategory;
use Illuminate\Database\Eloquent\Collection;
/** Class RawMeasurementWithExtendedProperties
 * @package App\Slim\Model\Measurement\Measurement
 */
class QMMeasurementExtended extends QMMeasurement {
	public $displayValueAndUnitString;
	public $icon;
	public $imageUrl;
	public $noteHtml;
	public $pngPath;
	public $productUrl;
	public $startDate;
	public $unitAbbreviatedName;
	public $unitName;
	public $originalStartAt;
	public $url;
	public $userId;
	public $valence;
	public $variableCategoryName;
	public $variableDescription;
	public $variableName;
	/**
	 * RawMeasurementWithExtendedProperties constructor.
	 * @param QMMeasurement $rawMeasurement
	 * @param QMUserVariable|null $v
	 * @param QMUnit|null $unit
	 * @noinspection DuplicatedCode
	 */
	public function __construct($rawMeasurement = null, QMUserVariable $v = null, QMUnit $unit = null){
		if(!$rawMeasurement){
			return;
		}
		if($v){
			$this->userVariable = $v;
		}
		parent::__construct(null, null, null, $rawMeasurement);
		if($v){
			if(!$this->imageUrl){
				$this->imageUrl = $v->imageUrl;
			}
			if(!$this->productUrl){
				$this->productUrl = $v->productUrl;
			}
			if(!$this->unitAbbreviatedName){
				$this->unitAbbreviatedName = $v->getCommonUnit()->abbreviatedName;
			}
			if(!$this->valence){
				$this->valence = $v->valence;
			}
			if(!$this->variableCategoryId){
				$this->variableCategoryId = $v->variableCategoryId;
			}
			if(!$this->variableCategoryName){
				$this->variableCategoryName = $v->variableCategoryName;
			}
			if(!$this->variableDescription){
				$this->variableDescription = $v->description;
			}
			if(!$this->variableId){
				$this->variableId = $v->variableId;
			}
			if(!$this->variableName){
				$this->variableName = $v->variableName;
			}
		}
		if(!$this->additionalMetaData && $rawMeasurement->note && $rawMeasurement->note !== "{}"){
			$this->setNoteAndAdditionalMetaData($rawMeasurement->note);
		}
		$this->setVariableCategoryProperties();
		if(!$this->variableName){
			$this->variableName = $rawMeasurement->variableName ?? null;
		}
		$this->getStartAt();
		$this->setUnitProperties($unit);
		$this->setValueFromRawMeasurement($rawMeasurement);
		$this->setSourceNameFromRawMeasurement($rawMeasurement);
		$this->populateDefaultFields();
	}
	/**
	 * @param QMVariableCategory|int|string $cat
	 * @return QMVariableCategory
	 */
	public function setVariableCategory($cat): QMVariableCategory{
		$cat = parent::setVariableCategory($cat);
		$this->variableCategoryName = $cat->name;
		return $cat;
	}
	private function setVariableCategoryProperties(){
		QMVariableCategory::addVariableCategoryNamesToObject($this);
	}
	private function setUnitProperties(?QMUnit $unit){
		if($unit){ // Uses less memory than Unit::addUnitProperties($this);
			$this->unitAbbreviatedName = $unit->abbreviatedName;
			$this->unitName = $unit->name;
			$this->unitId = $unit->id;
		} else{
			QMUnit::addUnitProperties($this);
		}
	}
	/**
	 * @param QMMeasurement|object $rawMeasurement
	 */
	private function setValueFromRawMeasurement($rawMeasurement){
		$this->value = Stats::roundToSignificantFiguresIfGreater($rawMeasurement->value);
	}
	/**
	 * @param QMMeasurement|object $rawMeasurement
	 */
	private function setSourceNameFromRawMeasurement($rawMeasurement){
		if(!isset($this->sourceName) && isset($rawMeasurement->clientId)){
			$this->sourceName = $rawMeasurement->clientId;
		}
	}
	/**
	 * @return string
	 */
	public function setPngPath(): string{
		$pngPath = null;
		$valence = $this->getValence();
		$value = $this->value;
		$unitId = $this->unitId;
		$name = $this->variableName;
		$dataSourceNameOrId = $this->connectorId ?? $this->sourceName;
		$obj = $this->getAdditionalMetaData();
		$variableImage = ($this->userVariable) ? $this->getQMUserVariable()->getImage() : null;
		$variableCategoryId = $this->variableCategoryId;
		$pngPath = self::generatePngPath($value, $variableCategoryId, $unitId, $name, $valence, $dataSourceNameOrId,
			$variableImage, $obj);
		$this->imageUrl = ImageHelper::getImageUrl($pngPath);
		return $this->pngPath = $pngPath;
	}
	/**
	 * @param $value
	 * @param $variableCategoryId
	 * @param $unitId
	 * @param $name
	 * @param string $valence
	 * @param $dataSourceNameOrId
	 * @param string|null $variableImage
	 * @param AdditionalMetaData $obj
	 * @return string|null
	 */
	public static function generatePngPath($value, $variableCategoryId, $unitId, $name, string $valence,
		$dataSourceNameOrId, ?string $variableImage, AdditionalMetaData $obj): ?string{
		$pngPath = $obj->icon ?? $obj->image ?? null;
		if(!$pngPath){
			if($unitId === OneToFiveRatingUnit::ID){
				if(!$valence){
					QMLog::error("No valence for OneToFiveRating!");
				} else{
					$pngPath = ImageHelper::getRatingImagePath($valence, (int)$value);
					if(!$pngPath){
						le("No rating png for value $value with valence " . $valence);
					}
				}
			}
		}
		if(!$pngPath && $unitId === DollarsUnit::ID){
			$pngPath = PaymentsVariableCategory::IMAGE_URL;
		}
		if(!$pngPath && stripos($name, TigerViewConnector::CURRENT_AVERAGE_GRADE_PREFIX) !== false ||
			stripos($name, TigerViewConnector::CLASS_DAILY_AVERAGE_GRADE_SUFFIX) !== false){
			$pngPath = ImageHelper::gradeToFace($value);
		}
		if(!$pngPath){
			if($dataSourceNameOrId){
				$connector = QMDataSource::getDataSourceWithoutDBQuery($dataSourceNameOrId);
				if($connector && $connector->name !== QuantiModoConnector::NAME){
					$pngPath = $connector->image;
				}
			}
		}
		if(!$pngPath){
			$pngPath = $variableImage;
		}
		if(!$pngPath){
			$pngPath = QMVariableCategory::find($variableCategoryId)->getPngUrl();
		}
		return $pngPath;
	}
	/**
	 * @param string $imageUrl
	 */
	public function setImageUrl(string $imageUrl): void{
		if(stripos($imageUrl, 'https') !== 0){
			$imageUrl = ImageHelper::getImageUrl($imageUrl);
		}
		$this->imageUrl = $imageUrl;
	}
	/**
	 * @return string
	 */
	public function getProductUrl(): ?string{
		return $this->productUrl;
	}
	/**
	 * @return string
	 */
	public function setNoteHtml(): ?string{
		if($url = $this->getUrlFromProductOrNote()){
			$this->noteHtml =
				'<a href="' . $url . '" target="_blank">' . $this->getAdditionalMetaData()->toHumanString() . '</a>';
		}
		return $this->noteHtml;
	}
	/**
	 * @return string
	 */
	public function getUrlFromProductOrNote(): ?string{
		if($this->getProductUrl()){
			return $this->url = $this->getProductUrl();
		}
		if(isset($this->getAdditionalMetaData()->url)){
			return $this->url = $this->getAdditionalMetaData()->url;
		}
		return null;
	}
	public function getEditUrl(): string{
		return MeasurementAddStateButton::url(['id' => $this->id]);
	}
	/**
	 * @return string
	 */
	public function setDisplayValueAndUnitString(): string{
		$unit = $this->getQMUnit();
		if(!$unit){
			$this->logError("Could not get unit!");
			$str = $this->value;
		} else{
			$str = $unit->getValueAndUnitString($this->value, true);
		}
		return $this->displayValueAndUnitString = $str;
	}
	/**
	 * @return string
	 */
	public function getImage(): string{
		$url = $this->imageUrl;
		if($url){
			return $url;
		}
		$url = $this->setPngPath();
		$this->setImageUrl($url);
		return $this->imageUrl = $url;
	}
	/**
	 * @return string
	 */
	public function getValence(): string{
		if(!$this->valence && $this->userVariable){
			$this->valence = $this->getQMUserVariable()->getValence();
		}
		if(!$this->valence){
			$this->valence = $this->getQMVariableCategory()->getValence();
		}
		return $this->valence;
	}
	/**
	 * @param array $params
	 * @return string
	 */
	public function getUrl(array $params = []): string{
		if($url = $this->url){
			return $url;
		}
		$note = $this->getAdditionalMetaData();
		if($note && isset($note->url)){
			return $this->url = $note->url;
		}
		return $this->url = MeasurementAddStateButton::url(['id' => $this->getId()]);
	}
	/**
	 * @param int $unitId
	 */
	public function setUnitId(int $unitId){
		if($this->unitId === $unitId){
			return;
		}
		parent::setUnitId($unitId);
		$this->setPngPath();
		$this->setDisplayValueAndUnitString();
	}
	/**
	 * @param array $params
	 * @return QMButton
	 */
	public function getButton(array $params = []): QMButton{
		$b = $this->getAdditionalMetaData()->getButton();
		if(!$b->getUrl()){
			$b->setUrl($this->getUrl());
		}
		if(!$b->image){
			$b->setImage($this->getImage());
		}
		return $b;
	}
	public function populateDefaultFields(): void{
		$this->setPngPath();
		$this->setNoteHtml();
		$this->setDisplayValueAndUnitString();
		$this->setUrl($this->getUrlFromProductOrNote() ?? $this->getEditUrl());
	}
	/**
	 * @param BaseModel[]|Collection $baseModels
	 * @return array
	 */
	public static function toDBModels($baseModels): array{
		$arr = parent::toDBModels($baseModels);
		foreach($arr as $one){
			if(!$one->originalStartAt){
				$arr = parent::toDBModels($baseModels);
				le("Whaa happen!?!?");
			}
		}
		return $arr;
	}
	/**
	 * @param mixed $url
	 */
	public function setUrl($url): void{
		$this->url = $url;
	}
}
