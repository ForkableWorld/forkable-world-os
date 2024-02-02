<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Scrapers\FederalReserve;
use App\Models\Variable;
use App\Properties\Base\BaseFillingTypeProperty;
use App\Scrapers\BasePath;
use App\Scrapers\BaseScraper;
use App\VariableCategories\EconomicIndicatorsVariableCategory;
use App\Variables\QMVariableCategory;
abstract class SeriesPath extends BasePath
{
    /**
     * @var string
     */
    public $seriesId;
    public function getPath(): string{
        return "series";
    }
    public function getParams(): array{
        return [
            'series_id' => $this->getSeriesId(),
            'api_key' => FederalReserveScraper::API_KEY,
            'file_type' => 'json',
        ];
    }
    /**
     * @return FederalReserveScraper
     */
    public function getScraper(): BaseScraper{
        return parent::getScraper();
    }
    public function getSeriesData(){
        $s = $this->getScraper();
        $r = $s->getRequest($s->getUrlForPath("series"), $this->getParams());
        return $r->seriess[0];
    }
    public function fillingType():string{
        return BaseFillingTypeProperty::FILLING_TYPE_NONE;
    }
    protected function variableCategoryId(): int{
        return EconomicIndicatorsVariableCategory::ID;
    }
    public function getQMVariableCategory():QMVariableCategory{
        return QMVariableCategory::find($this->variableCategoryId());
    }
    public function variableCategory():QMVariableCategory{
        return $this->getQMVariableCategory();
    }
    public function getAllVariablesData(): array{
        if($variablesData = $this->variableDataByName ?? []){return $variablesData;}
        $seriesVariableData = $this->getSeriesVariableData();
        foreach($seriesVariableData as $id => $data){
            $this->setSeriesId($id);
            if(!isset($data[Variable::FIELD_INFORMATIONAL_URL])){
                $data[Variable::FIELD_INFORMATIONAL_URL] = $this->getWebUrl();
            }
            if(!isset($data[Variable::FIELD_DESCRIPTION])){
                $data[Variable::FIELD_DESCRIPTION] = $this->getSubtitleAttribute();
            }
            if(!isset($data[Variable::FIELD_FILLING_TYPE])){
                $data[Variable::FIELD_FILLING_TYPE] = $this->fillingType();
            }
            if(!isset($data[Variable::FIELD_DEFAULT_UNIT_ID])){
                $data[Variable::FIELD_DEFAULT_UNIT_ID] = $this->unitId();
            }
            $variablesData[$this->variableName()] = $data;
        }
        return $this->variableDataByName = $variablesData;
    }
    public function getSubtitleAttribute():string{
        $data = $this->getSeriesData();
        return $data->notes;
    }
    public function variableName():string{
        $data = $this->getSeriesData();
        return $data->name;
    }
    abstract public function unitId(): int;
    protected function getSeriesVariableData():array{
        return [
            "AAA10Y" => [],
            "CSUSHPISA" => [],
            "DTWEXAFEGS" => [],
            "USEPUINDXD" => [],
            "VIXCLS" => [],
        ];
    }
    protected function getSeriesIds():array{
        return array_keys($this->getSeriesVariableData());
    }
    /**
     * @return string
     */
    public function getSeriesId(): string{
        return $this->seriesId;
    }
    /**
     * @param string $seriesId
     */
    public function setSeriesId(string $seriesId): void{
        $this->seriesId = $seriesId;
    }
    /**
     * @return string
     */
    protected function getWebUrl(): string{
        return 'https://fred.stlouisfed.org/series/'.$this->getSeriesId();
    }
}
