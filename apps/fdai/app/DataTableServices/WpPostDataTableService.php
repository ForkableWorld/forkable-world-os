<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataTableServices;
use App\Models\WpPost;
use App\Sorts\StringLengthSort;
use App\Types\TimeHelper;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;
class WpPostDataTableService extends BaseDataTableService
{
    protected $with = ['user'];
    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return BaseEloquentDataTable
     */
    public function getEloquentDataTable($query = null): BaseEloquentDataTable
    {
        $dataTable = parent::getEloquentDataTable($query);
        return $dataTable
            ->addColumn('wp_link', function(WpPost $v) {
                return $v->getWpLink("View in WP");
            })
            ->addColumn(WpPost::FIELD_POST_TITLE, function(WpPost $v) {
                return $v->getDataLabDisplayNameLink([], 140);
            })
            ->addColumn('size', function(WpPost $v) {
                return (round(strlen($v->post_content)/1024))." kb";
            })
            ->addColumn(WpPost::FIELD_POST_MODIFIED, function(WpPost $v) {
                return TimeHelper::timeSinceHumanString($v->post_modified);
            });
    }
    /**
     * Get query source of dataTable.
     *
     * @param WpPost $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(WpPost $model){
        $qb = $this->buildDataTableQueryFromRequest($model);
        $qb = QueryBuilder::for($qb)
            ->allowedSorts([
                AllowedSort::custom('length', new StringLengthSort(), 'size'),
                AllowedSort::custom('size', new StringLengthSort(), 'size'),
            ]);
        return $qb;
    }
}
