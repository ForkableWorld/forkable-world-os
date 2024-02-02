<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataTableServices;
use App\Logging\ConsoleLog;
use App\Logging\QMLog;
use App\Models\BaseModel;
use App\Models\Connection;
use App\Models\Connector;
use App\Models\UserVariableRelationship;
use App\Models\Unit;
use App\Models\User;
use App\Models\UserVariable;
use App\Models\Variable;
use App\Traits\HasModel\HasDataSource;
use App\Traits\HasModel\HasUser;
use App\Traits\HasModel\HasUserVariable;
use App\Traits\HasModel\HasVariable;
use App\Traits\HasModel\HasVariableCategory;
use App\Slim\View\Request\QMRequest;
use App\Exceptions\NotFoundException;
use App\Slim\Middleware\QMAuth;
use App\Types\TimeHelper;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Column;
class BaseEloquentDataTable extends EloquentDataTable
{
    const PREFIX = "prefix";
    const MIDDLE = "middle";
    const SUFFIX = "suffix";
    protected $model;
    protected $middleColumns = [];
    protected $prefixColumns = [];
    protected $suffixColumns = [];
    /**
     * BaseEloquentDataTable constructor.
     * @param BaseModel $model
     * @param null $query
     * @param bool $includeDateColumns
     */
    public function __construct($model, $query = null, bool $includeDateColumns = true){
        $model = $this->model = $model ?? QMRequest::getModelInstance();
        if(!$query && !$model){le('!$query && !$model');}
        parent::__construct($query ?? $model->newQuery());
        $this->escapeColumns([]);
        $this->addOpenImageTextLink(self::PREFIX);
        $this->addDropDownButton(self::MIDDLE);
        if(QMRequest::deletedFilter()){$this->addDeletedSince(self::PREFIX);}
        if(QMRequest::errorFilter()){
            $this->addInternalErrorMessage(self::PREFIX);
            $this->addUserErrorMessage(self::PREFIX);
        }
        $this->addRelatedDataDropDown(self::MIDDLE);
        if(QMAuth::isAdmin()){
            if(!QMRequest::userFilter() && $model->hasUserIdAttribute()){
                $this->addUserIdLink(self::MIDDLE);
            }
            $this->addIdLink(self::SUFFIX);
        }
        if($includeDateColumns){$this->addDateColumns();}
        if($model->isAnalyzable()){$this->addAnalyzableColumns();}
    }
    /**
     * @return Column[]
     */
    public function getMiddleColumns(): array {
        return $this->middleColumns;
    }
    /**
     * @return Column[]
     */
    public function getPrefixColumns(): array {
        return $this->prefixColumns;
    }
    public function getColumns(): array {
        $all = [];
        $pre = $this->getPrefixColumns();
        foreach($pre as $column){
            $name = $column->name;
            $all[$name] = $column;
        }
        $middle = $this->getMiddleColumns();
        foreach($middle as $column){
            $name = $column->name;
            if(!isset($all[$name])){$all[$name] = $column;}
        }
        $suffix = $this->getSuffixColumns();
        foreach($suffix as $column){
            $name = $column->name;
            if(!isset($all[$name])){$all[$name] = $column;}
        }
        return $all;
    }
    /**
     * @return Column[]
     */
    public function getSuffixColumns(): array {
        return $this->suffixColumns;
    }
    /**
     * @return BaseModel
     */
    public function getModel(): BaseModel{
        if(!$this->model){
            $this->model = QMRequest::getModelInstance();
        }
        return $this->model;
    }
    /**
     * Add column in collection.
     * @param string $name
     * @param string|callable $callback
     * @param string|null $title
     * @param string|null $relationNameForSearch
     * @return Column
     */
    public function generateColumn(string $name, $callback = null, string $title = null,
                                    string $relationNameForSearch = null): Column{
        $model = $this->getModel();
        $searchable = ($relationNameForSearch) || $model->isSearchable($name);
        $orderable = $model->isOrderable($name);
        $column = new Column([
            'title'      => $title ?? $model->attributeToTitle($name),
            'name'       => $relationNameForSearch ?? $name,
            'data'       => $name,
            'render'     => 'data',
            'searchable' => $searchable,
            'orderable' => $orderable,
        ]);
        if($callback){
            if($searchable){ // parent::addColumn adds it to the blacklist for some reason
                $this->extraColumns[] = $name;
                $this->columnDef['append'][] = ['name' => $name, 'content' => $callback, 'order' => $orderable];
            } else {
                parent::addColumn($name, $callback, $orderable);
            }
        }
        return $column;
    }
    /**
     * @param string $name
     * @param null $callback
     * @param string|null $title
     * @param string|null $relationNameForSearch
     * @return $this
     */
    public function addMiddleColumn(string $name, $callback = null, string $title = null,
                                    string $relationNameForSearch = null): BaseEloquentDataTable{
        $column = $this->generateColumn($name,  $callback, $title, $relationNameForSearch);
        return $this->addColumnByLocation($column, self::MIDDLE);
    }
    public function addColumnByLocation(Column $column, string $location = self::MIDDLE): BaseEloquentDataTable{
        if(!in_array($location, [self::PREFIX, self::MIDDLE, self::SUFFIX])){le("");}
	    $name = $column->name;
//	    if($this->hideOnIndex($name)){ // Can't do this or it hides the sort column sometimes
//		    $this->hideOnIndex($name);
//			return $this;
//		}
        if($location == self::MIDDLE){$this->middleColumns[] = $column;}
        if($location == self::SUFFIX){$this->suffixColumns[] = $column;}
        if($location == self::PREFIX){$this->prefixColumns[] = $column;}
        return $this;
    }
    /**
     * @param string $name
     * @param null $callback
     * @param string|null $title
     * @param string|null $relationNameForSearch
     * @return $this
     */
    public function addPrefixColumn(string $name, $callback = null, string $title = null, string $relationNameForSearch = null): BaseEloquentDataTable{
        $column = $this->generateColumn($name, $callback, $title, $relationNameForSearch);
        return $this->addColumnByLocation($column, self::PREFIX);
    }
    /**
     * @param string $name
     * @param null $callback
     * @param string|null $title
     * @param string|null $relationNameForSearch
     * @return $this
     */
    public function addSuffixColumn(string $name, $callback = null, string $title = null, string $relationNameForSearch = null): BaseEloquentDataTable{
        $column = $this->generateColumn($name, $callback, $title, $relationNameForSearch);
        return $this->addColumnByLocation($column, self::SUFFIX);
    }
    /**
     * @param array $columns
     * @return $this
     */
    public function addMiddleColumns(array $columns): BaseEloquentDataTable{
        foreach($columns as $attributes){
            if($attributes instanceof Column){
                $this->middleColumns[] = $attributes;
            } elseif(is_array($attributes)) {
                $this->middleColumns[] = new Column($attributes);
            } else {
                $this->middleColumns[] = $this->generateColumn($attributes);
            }
        }
        return $this;
    }
    /**
     * @param string $location
     * @return $this
     */
    public function addInternalErrorMessage(string $location = self::MIDDLE): BaseEloquentDataTable{
        $col = $this->generateColumn(UserVariableRelationship::FIELD_INTERNAL_ERROR_MESSAGE, function($m) {
            /** @var Variable $m */
            return $m->getInternalErrorMessageLink();
        }, "Errors");
        return $this->addColumnByLocation($col, $location);
    }
    /**
     * @param string $location
     * @return $this
     */
    public function addUserErrorMessage(string $location = self::MIDDLE): BaseEloquentDataTable{
        $col = $this->generateColumn(UserVariableRelationship::FIELD_INTERNAL_ERROR_MESSAGE, function($m) {
            /** @var Variable $m */
            return $m->getInternalErrorMessageLink();
        }, "Errors");
        return $this->addColumnByLocation($col, $location);
    }
    /**
     * @param string $location
     * @return $this
     */
    public function addDropDownButton(string $location = self::SUFFIX): BaseEloquentDataTable{
        $col = $this->generateColumn('drop_down_button',  function($m) {
            /** @var BaseModel $m */
            return $m->getDataLabModelDropDownButton();
        }, "Options");
        return $this->addColumnByLocation($col, $location);
    }
    /**
     * @param string $location
     * @return $this
     */
    public function addUserIdLink(string $location = self::MIDDLE): BaseEloquentDataTable{
        $col = $this->generateColumn('user_id_link',  function($m) {
            /** @var HasUser|User $m */
            return $m->getUserIdLink();
        });
        return $this->addColumnByLocation($col, $location);
    }
    /**
     * @param string $location
     * @return $this
     */
    public function addUserVariableLink(string $location = self::MIDDLE): BaseEloquentDataTable{
        $col = $this->generateColumn('user_variable_link', function($m) {
            /** @var HasUserVariable $m */
            $id = $m->getUserVariableId();
            if(!$id){
                throw new \LogicException("No id for UserVariable::generateShowLink");
            }
            return $m->getUserVariable()->getDataLabButton()->getLink();
        }, "Variable");
        return $this->addColumnByLocation($col, $location);
    }
    /**
     * @param string $location
     * @return $this
     * @noinspection PhpUnused
     */
    public function addIdLink(string $location = self::MIDDLE): BaseEloquentDataTable{
        $col = $this->generateColumn('id_link',  function($m) {
            /** @var Variable $m */
            return $m->getDataLabIdLink();
        });
        return $this->addColumnByLocation($col, $location);
    }
    /**
     * @param string $location
     * @return $this
     */
    public function addImageAndNameLink(string $location = self::MIDDLE): BaseEloquentDataTable{
        $col = $this->generateColumn('open_button', function($m) {
            /** @var BaseModel $m */
            $b = $m->getDataLabOpenButton();
            return $b->getImageTextLink();
        }, "Open");
        return $this->addColumnByLocation($col, $location);
    }
    /**
     * @param string $location
     * @return $this
     */
    public function addOpenImageTextLink(string $location = self::PREFIX): BaseEloquentDataTable{
        $col = $this->generateColumn('open_button', function($m) {
            /** @var BaseModel $m */
            return $m->getDataLabOpenButton()->getImageTextLink(
                "height: 32px; border-radius: 0; cursor: pointer; object-fit: scale-down; margin: auto;");
        }, "Open");
        return $this->addColumnByLocation($col, $location);
    }
    /**
     * @param string $relationNameForSearch
     * @param string $location
     * @return $this
     */
    public function addNameDropDownButton(string $relationNameForSearch = 'name', string $location = self::PREFIX): BaseEloquentDataTable{
        $col = $this->generateColumn('dropdown_button', function($m){
            /** @var Variable $m */
            return $m->getDataLabNameDropDownButton();
        }, "Name",
            $relationNameForSearch);
        return $this->addColumnByLocation($col, $location);
    }
    /**
     * @param string $location
     * @return $this
     */
    public function addCategoryLink(string $location = self::MIDDLE): BaseEloquentDataTable{
        $col = $this->generateColumn('category_link', function($m) {
            /** @var Variable|Unit $m */
            return $m->getCategoryLink();
        });
        return $this->addColumnByLocation($col, $location);
    }
    /**
     * @param string $location
     * @return $this
     */
    public function addUnitLink(string $location = self::MIDDLE): BaseEloquentDataTable{
        $col = $this->generateColumn('unit_link', function($m) {
            /** @var Variable $m */
            return $m->getUnitLink();
        });
        return $this->addColumnByLocation($col, $location);
    }
    /**
     * @param string $location
     * @return $this
     */
    public function addUnitAbbreviatedLink(string $location = self::MIDDLE): BaseEloquentDataTable{
        $col = $this->generateColumn('unit_link', function($m) {
            /** @var Variable $m */
            return $m->getUnitAbbreviatedLink();
        });
        return $this->addColumnByLocation($col, $location);
    }
    /**
     * @param string $location
     * @return $this
     */
    public function addVariableCategoryLink(string $location = self::MIDDLE): BaseEloquentDataTable{
        $col = $this->generateColumn('variable_category_link', function($m) {
            /** @var HasVariableCategory $m */
            return $m->getVariableCategoryLink();
        });
        return $this->addColumnByLocation($col, $location);
    }
    /**
     * @param string $location
     * @return $this
     */
    public function addDataSourceLink($location = self::MIDDLE): BaseEloquentDataTable{
        $col = $this->generateColumn('data_source_link', function($m) {
            try {
                /** @var HasDataSource $m */
                return $m->getDataSourceImageTextLink();
            } catch (NotFoundException $e){
                return "N/A";
            }
        });
        return $this->addColumnByLocation($col, $location);
    }
    /**
     * @param string $location
     * @return $this
     */
    public function addConnectorLink(string $location = self::MIDDLE): BaseEloquentDataTable{
        $col = $this->generateColumn('connector_link', function($m) {
            /** @var Connection $m */
            return $m->getConnectorLink();
        }, "Connector", "connector.name");
        return $this->addColumnByLocation($col, $location);
    }
    /**
     * @param string $location
     * @return $this
     */
    public function addPostLink(string $location = self::MIDDLE): BaseEloquentDataTable{
        $col = $this->generateColumn('post_link', function($m) {
            /** @var BaseModel $m */
            return $m->getPostLink();
        });
        return $this->addColumnByLocation($col, $location);
    }
    /**
     * @param string $location
     * @return $this
     */
    public function addAnalysisEndedAt(string $location = self::MIDDLE): BaseEloquentDataTable{
        return $this->addTimeSinceColumn(Variable::FIELD_ANALYSIS_ENDED_AT, $location);
    }
    /**
     * @param string $location
     * @return $this
     */
    public function addAnalysisStartedAt(string $location = self::MIDDLE): BaseEloquentDataTable{
        return $this->addTimeSinceColumn(Variable::FIELD_ANALYSIS_STARTED_AT, $location);
    }
    /**
     * @param string|null $relationNameForSearch
     * @param string $location
     * @return $this
     */
    public function addNameLink(string $relationNameForSearch, string $location = self::MIDDLE): BaseEloquentDataTable{
        $col = $this->generateColumn('name', function($m) {
            /** @var BaseModel $m */
            return $m->getDataLabDisplayNameLink();
        }, "Name", $relationNameForSearch);
        return $this->addColumnByLocation($col, $location);
    }
    /**
     * @param string $location
     * @return $this
     */
    public function addDisplayNameLink(string $location = self::PREFIX): BaseEloquentDataTable{
        $col = $this->generateColumn(Connector::FIELD_DISPLAY_NAME, function($m) {
            /** @var BaseModel $m */
            return $m->getDataLabDisplayNameLink();
        }, "Name");
        return $this->addColumnByLocation($col, $location);
    }
    /**
     * @param string $location
     * @return $this
     */
    public function addGaugeLink(string $location = self::PREFIX): BaseEloquentDataTable{
        $col = $this->generateColumn('gauge_link', function($m) {
            /** @var UserVariableRelationship $m */
            return $m->getGaugeLink();
        });
        return $this->addColumnByLocation($col, $location);
    }
    /**
     * @param string $location
     * @return $this
     */
    public function addGaugeNameDropDown(string $location = self::PREFIX): BaseEloquentDataTable{
        $col = $this->generateColumn('gauge_name_drop_down', function($m) {
            /** @var UserVariableRelationship $m */
            return $m->getGaugeNameDropDown();
        }, "Name");
        return $this->addColumnByLocation($col, $location);
    }
    /**
     * @return BaseModel
     */
    public function getClassForModel(): string {
        $model = $this->getModel();
        /** @var BaseModel $class */
        $class = get_class($model);
        return $class;
    }
    /**
     * @param string $location
     * @return $this
     */
    public function addVariableNameLink(string $location = self::MIDDLE): BaseEloquentDataTable{
        $col = $this->generateColumn('variable_name_link', function($m) {
            /** @var HasVariable $m */
            return $m->getVariableNameLink();
        }, "Variable", 'variable.name');
        return $this->addColumnByLocation($col, $location);
    }
    /**
     * @param string $location
     * @return $this
     */
    public function addVariableImageNameLink(string $location = self::MIDDLE): BaseEloquentDataTable{
        $col = $this->generateColumn('variable_image_name_link', function($m) {
            /** @var HasVariable $m */
            return $m->getVariableImageNameLink();
        }, "Variable", 'variable.name');
        return $this->addColumnByLocation($col, $location);
    }
    /**
     * @param string $location
     * @return $this
     */
    public function addDeletedSince(string $location = self::MIDDLE): BaseEloquentDataTable{
        return $this->addTimeSinceColumn(BaseModel::FIELD_DELETED_AT, $location);
    }
    /**
     * @param string $location
     * @return $this
     */
    public function addEffectImageNameDropDown(string $location = self::MIDDLE): BaseEloquentDataTable{
        $col = $this->generateColumn('effect_link', function($m) {
            /** @var UserVariableRelationship $m */
            return $m->getEffectVariable()->getDataLabImageNameDropDown();
        }, "Outcome", 'effect_variable.name');
        return $this->addColumnByLocation($col, $location);
    }
    /**
     * @param string $location
     * @return $this
     */
    public function addCauseImageNameDropDown(string $location = self::MIDDLE): BaseEloquentDataTable{
        $col = $this->generateColumn('cause_link', function($m) {
            /** @var UserVariableRelationship $m */
            return $m->getCauseVariable()->getDataLabImageNameDropDown();
            //return $m->getEffectNameLink();
        }, "Predictor", 'cause_variable.name');
        return $this->addColumnByLocation($col, $location);
    }
    /**
     * @param string $location
     * @return $this
     */
    public function addEffectSize(string $location = self::MIDDLE): BaseEloquentDataTable{
        $col = $this->generateColumn(UserVariableRelationship::FIELD_EFFECT_FOLLOW_UP_PERCENT_CHANGE_FROM_BASELINE,
            function($m) {
                /** @var UserVariableRelationship $m */
                return $m->getEffectSizeLinkToStudyWithExplanation();
            }, 'Effect Size');
        return $this->addColumnByLocation($col, $location);
    }
    public function addImageLink(string $location = self::MIDDLE): BaseEloquentDataTable{
        $col = $this->generateColumn('open_button', function($m) {
            /** @var BaseModel $m */
            $b = $m->getDataLabOpenButton();
            return $b->getImageLink();
        }, "Open");
        return $this->addColumnByLocation($col, $location);
    }
    public function addRelatedDataDropDown(string $location = self::SUFFIX): BaseEloquentDataTable{
	    $baseModel = $this->getModel();
		if(!$baseModel->getAttributes()){
			ConsoleLog::warning("Cannot add related data drop down to a model that has not been loaded");
			return $this;
		}
	    $buttons = $baseModel->getInterestingRelationshipButtons();
		if(!$buttons){
			QMLog::error("No relationships found for ".get_class($baseModel));
			return $this;
		}
        $col = $this->generateColumn('related_data', function($m) {
            /** @var BaseModel $m */
            $menu = $m->getDataLabRelationshipMenu();
            return $menu->getDropDownMenu();
        }, "Related Data");
        return $this->addColumnByLocation($col, $location);
    }
    public function getColumnDef(): array {
        return $this->columnDef;
    }
    public function getExtraColumns(): array {
        return $this->extraColumns;
    }
    protected function addAnalyzableColumns(): void{
        $this->addAnalysisEndedAt(self::SUFFIX);
        $this->addAnalysisStartedAt(self::SUFFIX);
        $this->addTimeSinceColumn(Variable::FIELD_ANALYSIS_REQUESTED_AT);
    }
    /**
     * @param string $fieldName
     * @param string $location
     * @return $this
     */
    public function addTimeSinceColumn(string $fieldName, string $location = self::MIDDLE): BaseEloquentDataTable{
        $col = $this->generateColumn($fieldName, function($m) use ($fieldName) {
            $html = TimeHelper::timeSinceHumanStringHtml($m->$fieldName);
            return $html;
        });
        return $this->addColumnByLocation($col, $location);
    }
    public function addDateColumns(): void{
        $model = $this->getModel();
        $timestampFields = $model->getDates();
        foreach($timestampFields as $field){
            $prop = $model->getPropertyModel($field);
            if(!$prop){
              QMLog::warning("No property model found for $field on ".get_class($model).". Skipping addDateColumns...");
              continue;
            }
            if(!$prop->showOnIndex()){
              continue;
            }
            $this->addTimeSinceColumn($field, self::SUFFIX);
        }
    }
	private function getPropertyModel(string $name): ?\App\Properties\BaseProperty{
		$model = $this->getModel();
		return $model->getPropertyModel($name);
	}
	private function hideOnIndex(string $name): bool{
		$prop = $this->getPropertyModel($name);
		if(!$prop){ // It's a button or something
			return false;
		}
		return $prop->hideOnIndex();
	}
}
