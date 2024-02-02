<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataTableServices;
use App\Models\Subscription;
class SubscriptionDataTableService extends BaseDataTableService
{
    protected $with = ['user'];
    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return BaseEloquentDataTable
     */
    public function getEloquentDataTable($query = null): \App\DataTableServices\BaseEloquentDataTable{
        $dataTable = parent::getEloquentDataTable($query);
        return $dataTable
            ->addMiddleColumns([
            'name' => [
                'title'      => 'Name',
                'data'       => 'name',
                'name'       => 'name',
                'searchable' => true
            ],
            'stripe_id' => [
                'title'      => 'Stripe Id',
                'data'       => 'stripe_id',
                'name'       => 'stripe_id',
                'searchable' => true
            ],
            'stripe_plan' => [
                'title'      => 'Stripe Plan',
                'data'       => 'stripe_plan',
                'name'       => 'stripe_plan',
                'searchable' => true
            ],
            'quantity' => [
                'title'      => 'Quantity',
                'data'       => 'quantity',
                'name'       => 'quantity',
                'searchable' => false
            ],
            'trial_ends_at' => [
                'title'      => 'Trial Ends At',
                'data'       => 'trial_ends_at',
                'name'       => 'trial_ends_at',
                'searchable' => false
            ],
            'ends_at' => [
                'title'      => 'Ends At',
                'data'       => 'ends_at',
                'name'       => 'ends_at',
                'searchable' => false
            ]
        ]);
    }
    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\Subscription $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Subscription $model)
    {
        return $this->buildDataTableQueryFromRequest($model);
    }
}
