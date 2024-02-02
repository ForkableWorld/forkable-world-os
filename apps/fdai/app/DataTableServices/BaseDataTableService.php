<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataTableServices;
use App\Exceptions\AccessTokenExpiredException;
use App\Http\Parameters\SortParam;
use App\Models\BaseModel;
use App\Slim\Middleware\QMAuth;
use App\Slim\View\Request\QMRequest;
use App\Storage\QueryBuilderHelper;
use App\Types\QMStr;
use App\Utils\AppMode;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Yajra\DataTables\DataTableAbstract;
use Yajra\DataTables\Services\DataTable;
abstract class BaseDataTableService extends DataTable
{
    public $columns;
    protected $defaultOrderColumnName = null;   // If defaultOrderColumn appears to be ignored, click reset button because this is kept in localstorage due to "remember" option
    protected $defaultOrderDirection = null;
    protected $addColumnFilterBoxes = false;
    protected $includeErrorsColumn = false;
    protected $with = [];
    protected $model;
    /**
     * BaseDataTableService constructor.
     * @param BaseModel|null $model
     * DO NOT TYPE HINT OR IT ADDS A BaseModel instance
     */
    public function __construct(BaseModel $model = null){
        $this->model = $model;
    }
    protected function getDefaultSortDirection(): string {
        $defaultDirection = SortParam::getSortDirection();
        if(!$defaultDirection){
            $model = QMRequest::getModelInstance();
            $defaultDirection = $this->defaultOrderDirection ?? $model->getDefaultSortOrderDirection();
        }
        return $defaultDirection;
    }
    protected function getDefaultSortField(): string {
        $field = SortParam::getSortWithoutDirection();
        if(!$field){
            $model = QMRequest::getModelInstance();
            $field = $this->defaultOrderColumnName ?? $model->getDefaultSortField();
        }
        return $field;
    }
    protected function getDefaultOrderColumnNumber(): int{
        $htmlColumns = $this->getColumns();
        $defaultColumn = $this->getDefaultSortField();
        if(QMRequest::getParam('deleted')){
            $defaultColumn = BaseModel::FIELD_DELETED_AT;
        }
        $i = 0;
	    $columnNames = array_keys($htmlColumns);
        foreach($htmlColumns as $key => $value){
            if($value === $defaultColumn || $key === $defaultColumn){
                return $i;
            }
            $i++;
        }
        $table = $this->getEloquentDataTable();
        le("Sort column for $defaultColumn for ".QMRequest::getTableName()." not found! Columns: ".
	        implode(", ", $columnNames));
    }
    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html(): \Yajra\DataTables\Html\Builder{
        $params = [
            'dom'       => 'Bfrtip',
            'stateSave' => true,
            'responsive' => true,
            // If defaultOrderColumn appears to be ignored, click reset button because this is kept in localstorage due to "remember" option
            'order'     => [[$this->getDefaultOrderColumnNumber(), $this->getDefaultSortDirection()]],
            'buttons'   => [
                //['extend' => 'create', 'className' => 'btn btn-default btn-sm no-corner',],
                ['extend' => 'export', 'className' => 'btn btn-default btn-sm no-corner',],
                //['extend' => 'print', 'className' => 'btn btn-default btn-sm no-corner',],
                ['extend' => 'reset', 'className' => 'btn btn-default btn-sm no-corner',],
                ['extend' => 'reload', 'className' => 'btn btn-default btn-sm no-corner',],
            ],
        ];
        if($this->addColumnFilterBoxes){
            $params['initComplete'] = 'function () {
              this.api().columns().every(function () {
                var column = this;
                var input = document.createElement("input");
                $(input).
                  appendTo($(column.footer()).empty()).
                  on(\'change\', function () {
                    column.search($(this).val(), false, false, true).draw();
                  });
              });
            }';
        }
        return $this->builder()
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->addAction(['width' => '120px', 'printable' => false])
            ->parameters($params);
    }
	/**
	 * @param array $htmlColumns
	 * @param string $defaultColumn
	 * @return void
	 */
	public function getColumnIndex(array $htmlColumns, string $defaultColumn): ? int {
		$i = 0;
		$columnIndex = null;
		$columnNames = array_keys($htmlColumns);
		foreach($htmlColumns as $key => $value){
			if($value === $defaultColumn || $key === $defaultColumn){
				$columnIndex = $i;
				break;
			}
			$i++;
		}
		return $columnIndex;
	}
	/**
	 * @param BaseModel $model
	 * @return Builder
	 * @throws AccessTokenExpiredException
	 */
    protected function buildDataTableQueryFromRequest(BaseModel $model): Builder{
	    if(QMAuth::isAdmin() && 
	       !in_array('user', $this->with) &&
	       $model->hasColumn('user_id')){
		    $this->with[] = 'user';
	    }
        $eqb = $model->newQuery()->with($this->with);
        $defaultOrderColumn = $this->getDefaultSortField();
        $defaultDirection = $this->getDefaultSortDirection();
        // If defaultOrderColumn appears to be ignored, click reset button because this is kept in localstorage due to "remember" option
        QueryBuilderHelper::addOrderByFromRequestIfNecessary($eqb, $defaultOrderColumn, $defaultDirection);
        QueryBuilderHelper::addFilterParamsFromReferrerIfNecessary($eqb);
        $dbqb = $eqb->getQuery();
        QueryBuilderHelper::restrictQueryBasedOnPermissions($dbqb);
        QueryBuilderHelper::applyTablePrefixedParamsFromRequest($eqb);
        return $eqb;
    }
    /**
     * @return array
     */
    public function getColumns(): array{
        $table = $this->getEloquentDataTable();

        return $table->getColumns();
    }
    /**
     * Get filename for export.
     * @return string
     */
    protected function filename(): string{
        $table = QMRequest::getTableName();
        return $table."_datatable_" . time();
    }
    /**
     * Build DataTable class.
     * @param mixed $query Results from query() method.
     * @return DataTableAbstract
     */
    public function dataTable($query){
        $dataTable = $this->getEloquentDataTable($query);
        return $dataTable;
    }
	/**
	 * @param null $query
	 * @return BaseEloquentDataTable
	 */
	public function getEloquentDataTable($query = null): BaseEloquentDataTable{
        $model = $this->getModel();
        if($query && $this->with){
            $query->with($this->with);
        }
        $dataTable = new BaseEloquentDataTable($model, $query);
        if(AppMode::isApiRequest()){
            $fields = QueryBuilderHelper::getColumnsInRequest();
	        $defaultSort = $this->getDefaultSortField();
			if(!in_array($defaultSort, $fields)){
				$fields[] = $defaultSort;
			}
            foreach($fields as $field){
                $dataTable->addPrefixColumn($field);
            }
        }
        return $dataTable;
    }
    /**
     * @param string $view
     * @param array $data
     * @param array $mergeData
     * @return JsonResponse|View
     */
    public function render($view, $data = [], $mergeData = []){
        if(QMRequest::getParam('draw')){ // For ajax for debugging when opening table request in new tab
            return $this->ajax();
        }
        return parent::render($view, $data, $mergeData);
    }
    public function getModel(): BaseModel {
        if(!$this->model){
			$class = static::getFullClass();
            $this->model = new $class;
        }
        return $this->model;
    }
    public static function getFullClass():string {
        $thisClass = QMStr::toShortClassName(static::class);
        $short = QMStr::before("DataTable", $thisClass);
        return QMStr::toFullClassName($short);
    }
	/**
	 * @return BaseModel
	 */
	protected static function getModelInstance(): BaseModel{
		$class = static::getFullClass();
		return new $class;
	}
}
