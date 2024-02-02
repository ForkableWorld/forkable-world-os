<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Charts\ChartGroup;
use App\Charts\QMChart;
use App\Models\BaseModel;
use App\Traits\HasCharts;
use App\Traits\PropertyTraits\IsJsonEncoded;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
use App\Types\ObjectHelper;
use App\Models\GlobalVariableRelationship;
use App\Fields\Field;
use App\Fields\Text;
use OpenApi\Generator;
class BaseChartsProperty extends BaseProperty{
    use IsJsonEncoded;
	public $dbInput = 'text:nullable';
    public $dbType = 'text';
	public $default = Generator::UNDEFINED;
	public $description = "Pre-generated Highchart configuration objects";
	public $fieldType = 'text';
	public $fontAwesome = FontAwesome::CHARTS;
	public $htmlInput = 'textarea';
	public $htmlType = 'textarea';
	public $image = ImageUrls::CHARTS;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $maxLength = 200000;
    public $name = self::NAME;
    public $phpType = ChartGroup::class;
	public $title = 'Charts';
	public $type = PhpTypes::ARRAY;
	public const NAME = GlobalVariableRelationship::FIELD_CHARTS;
	public static function handleTooLong(int $id): BaseModel{
        /** @var HasCharts|BaseModel $model */
        $model = static::findParent($id);
        $charts = $model->getChartGroup();
        /** @var QMChart $chart */
        foreach($charts as $key => $chart){
            ObjectHelper::logPropertySizes($chart->getTitleAttribute(), $chart);
        }
        return $model;
    }
    public function validate(): void {
        if(!$this->shouldValidate()){return;}
        parent::validate();
        $value = $this->getAccessorValue();
        if(!$value){return;}
		if(!is_object($value) && !is_array($value)){le('!is_object($value) && !is_array($value)');}
		if(isset($value->unpairedOverTimeLineChart)){le('isset($value->unpairedOverTimeLineChart)');}
    }
    public function getExample(): ChartGroup{
        /** @var HasCharts $analyzable */
        $analyzable = $this->getParentModel();
        return $analyzable->getChartGroup();
    }
    public function getDetailsField($resolveCallback = null, string $name = null): Field{
        return $this->getChartsLink();
    }
    /**
     * @return \App\Fields\Text
     */
    protected function getChartsLink(): Text{
        return $this->getLinkField($this->getParentModel()->getUrl(), "View Charts");
    }
    public function showOnIndex(): bool{return false;}
    public function showOnUpdate(): bool{return false;}
    public function showOnDetail(): bool{return false;}
    public function showOnCreate(): bool{return false;}
}
