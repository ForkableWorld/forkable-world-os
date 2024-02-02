<?php namespace App\Services;
use App\Models\GlobalVariableRelationship;
use App\Storage\QueryBuilderHelper;
use Illuminate\Database\Eloquent\Collection;
class AggregatedCorrelationService extends BaseService {
    /**
     * @param array $filters
     * @return GlobalVariableRelationship[]|Collection
     */
    public function all($filters = []){
        $query = GlobalVariableRelationship::with([
            'cause',
            'effect'
        ]);
        QueryBuilderHelper::applyFilters($query->getQuery(), $filters);
        QueryBuilderHelper::applyOffsetLimitSort($query->getQuery(), $filters);
        return $query->get();
    }
    /**
     * @param array $input
     * @return GlobalVariableRelationship
     */
    public function create(array $input){
        return GlobalVariableRelationship::create($input);
    }
    /**
     * @param int $id
     * @param array $columns
     * @return null|GlobalVariableRelationship
     */
    public function find($id, $columns = ['*']){
        return GlobalVariableRelationship::find($id, $columns);
    }
    /**
     * @param int $id
     * @return null|GlobalVariableRelationship
     */
    public function getWithRelations($id){
        return GlobalVariableRelationship::whereId($id)->with([
            'cause',
            'effect'
        ])->first();
    }
    /**
     * @param array $data
     * @param int $id
     * @return bool
     */
    public function updateRich(array $data, $id){
        if(!($aggregatedCorrelation = $this->find($id))){
            return false;
        }
        return $aggregatedCorrelation->fill($data)->save();
    }
    /**
     * @param int $id
     * @return bool|null
     */
    public function delete($id){
        if(!($aggregatedCorrelation = $this->find($id))){
            return false;
        }
        return $aggregatedCorrelation->delete();
    }
}/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */


