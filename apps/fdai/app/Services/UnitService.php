<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Services;
use App\Models\Unit;
use App\Storage\QueryBuilderHelper;
class UnitService extends BaseService {
    /**
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Collection|Unit[]
     */
    public function all($filters = []){
        $query = Unit::with([
            'conversionSteps',
            'category',
            'defaultUnit'
        ]);
        QueryBuilderHelper::addParams($query->getQuery(), $filters);
        return $query->get();
    }
    /**
     * @param array $input
     * @return Unit
     */
    public function create(array $input){
        return Unit::create($input);
    }
    /**
     * @param array $data
     * @param int $id
     * @return bool
     */
    public function updateRich(array $data, $id){
        if(!($unit = $this->find($id))){
            return false;
        }
        return $unit->fill($data)->save();
    }
    /**
     * @param int $id
     * @param array $columns
     * @return null|Unit
     */
    public function find($id, $columns = ['*']){
        return Unit::find($id, $columns);
    }
    /**
     * @param int $id
     * @return null|Unit
     */
    public function getWithRelations($id){
        return Unit::whereId($id)->with([
            'conversionSteps',
            'category',
            'defaultUnit'
        ])->first();
    }
    /**
     * @param int $id
     * @return bool|null
     */
    public function delete($id){
        if(!($unit = $this->find($id))){
            return false;
        }
        return $unit->delete();
    }
}
