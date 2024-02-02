<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn 
 */

namespace App\DataTableServices;
use App\Models\Purchase;
class PurchaseDataTableService extends BaseDataTableService
{
    protected $with = [];
    /**
     * Build DataTable class.
     * @param mixed $query Results from query() method.
     * @return BaseEloquentDataTable
     */
    public function getEloquentDataTable($query = null): \App\DataTableServices\BaseEloquentDataTable{
        $dataTable = (new BaseEloquentDataTable(static::getModelInstance(), $query))
            ->addMiddleColumns([
                'subscriber_user_id' => [
                    'title'      => 'Subscriber User Id',
                    'data'       => 'subscriber_user_id',
                    'name'       => 'subscriber_user_id',
                    'searchable' => false
                ],
                'referrer_user_id' => [
                    'title'      => 'Referrer User Id',
                    'data'       => 'referrer_user_id',
                    'name'       => 'referrer_user_id',
                    'searchable' => false
                ],
                'subscription_provider' => [
                    'title'      => 'Subscription Provider',
                    'data'       => 'subscription_provider',
                    'name'       => 'subscription_provider',
                    'searchable' => true
                ],
                'last_four' => [
                    'title'      => 'Last Four',
                    'data'       => 'last_four',
                    'name'       => 'last_four',
                    'searchable' => true
                ],
                'product_id' => [
                    'title'      => 'Product Id',
                    'data'       => 'product_id',
                    'name'       => 'product_id',
                    'searchable' => true
                ],
                'subscription_provider_transaction_id' => [
                    'title'      => 'Subscription Provider Transaction Id',
                    'data'       => 'subscription_provider_transaction_id',
                    'name'       => 'subscription_provider_transaction_id',
                    'searchable' => true
                ],
                'coupon' => [
                    'title'      => 'Coupon',
                    'data'       => 'coupon',
                    'name'       => 'coupon',
                    'searchable' => true
                ],
                'refunded_at' => [
                    'title'      => 'Refunded At',
                    'data'       => 'refunded_at',
                    'name'       => 'refunded_at',
                    'searchable' => false
                ]
            ]);
        return $dataTable;
    }
    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\Purchase $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Purchase $model){
        return $this->buildDataTableQueryFromRequest($model);
    }
}
