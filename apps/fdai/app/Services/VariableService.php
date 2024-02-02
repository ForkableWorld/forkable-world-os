<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Services;
use App\Models\Variable;
use App\Properties\Base\BaseClientIdProperty;
use App\Storage\QueryBuilderHelper;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class VariableService extends BaseService {
    /**
     * @param array $filters
     * @return Variable[]
     */
    public function all(array $filters = []){
//        $query = Variable::with([
//            'category',
//            'defaultUnit',
//            'mostCommonUnit'
//        ]);
        $query = Variable::queryFromRequest();
        ///$query->orderBy(Variable::FIELD_NUMBER_OF_USER_VARIABLES, 'desc');
        $variables = $query->get();
        /** @var Variable[]|Collection $variables */
        return $variables;
    }
    /**
     * @param array $input
     * @return Variable
     */
    public function create(array $input){
        if(!isset($input[Variable::FIELD_CLIENT_ID])){
            $input[Variable::FIELD_CLIENT_ID] = BaseClientIdProperty::fromRequest(true);
        }
        return Variable::create($input);
    }
    /**
     * @param int $id
     * @param array $columns
     * @return null|Variable
     */
    public function find($id, $columns = ['*']){
        return Variable::find($id, $columns);
    }
    /**
     * @param int $id
     * @return null|Variable
     */
    public function getWithRelations($id){
        $variable = Variable::whereId($id)->with([
            'category',
            'defaultUnit',
            'mostCommonUnit'
        ])->first();
        return $variable;
    }
    /**
     * @param array $data
     * @param int $id
     * @return bool
     */
    public function updateRich(array $data, $id){
        if(!($variable = $this->find($id))){
            return false;
        }
        return $variable->fill($data)->save();
    }
    /**
     * @param int $id
     * @return bool|null
     * @throws \Exception
     */
    public function delete($id){
        if(!($variable = $this->find($id))){
            return false;
        }
        return $variable->delete();
    }
    /**
     * @param $term
     * @return array|\Illuminate\Support\Collection
     */
    public function autocompleteSearch($term){
        $partialMatches = [];
        $qb = Db::table('variables as v')->select('v.id', 'v.name as value');
        $qb->join('variable_categories AS cats', 'v.variable_category_id', '=', 'cats.id');
        $qb->whereRaw('COALESCE(v.`is_public`, cats.is_public)= 1');
        $qb->where('v.name', \App\Storage\DB\ReadonlyDB::like(), '%'.$term.'%');
        $qb->orderBy('v.number_of_measurements', 'DESC');
        $variables = $qb->take(5)->get();
        if(count($variables) === 5){
            foreach($variables as $variable){
                if(strtolower($variable->value) === strtolower($term)){
                    $exactMatch[] = $variable;
                }else{
                    $partialMatches[] = $variable;
                }
            }
            if(!isset($exactMatch)){
                $qb->where('v.name', $term);
                $exactMatch = $qb->take(1)->get()->all();
            }
            if($exactMatch){
                $variables = array_merge($exactMatch, $partialMatches);
            }else{
                return $partialMatches;
            }
        }
        return $variables;
    }
}
