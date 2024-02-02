<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataTableServices;
use App\Models\BaseModel;
use App\Models\VariableCategory;
class VariableCategoryDataTableService extends BaseDataTableService
{
    protected $with = [];
	public function __construct(BaseModel $model = null){
		parent::__construct($model, false);
	}
	/**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return BaseEloquentDataTable
     */
    public function getEloquentDataTable($query = null): \App\DataTableServices\BaseEloquentDataTable{
        $dataTable = parent::getEloquentDataTable($query);
        $dataTable
            ->addImageLink()
            ->addNameLink(VariableCategory::FIELD_NAME);
        return $dataTable;
    }
    /**
     * Get query source of dataTable.
     *
     * @param VariableCategory $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(VariableCategory $model){
        return $this->buildDataTableQueryFromRequest($model);
    }
}
