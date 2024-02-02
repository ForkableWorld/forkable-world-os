<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Services;
use App\Models\UserVariable;
use App\Storage\QueryBuilderHelper;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
class UserVariableService extends BaseService {
    /**
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Collection|UserVariable[]
     * @throws \App\Exceptions\BadRequestException
     */
    public function all($filters = []){
        $query = UserVariable::with([
                'variable',
                'defaultUnit',
                'category',
                'lastUnit',
                'lastOriginalUnit',
                'mostCommonUnit',
                'lastSource'
            ]);
        QueryBuilderHelper::addParams($query->getQuery(), $filters);
        return $query->get();
    }
    /**
     * @param array $input
     * @return UserVariable
     */
    public function create(array $input): UserVariable{
        return UserVariable::create($input);
    }
    /**
     * @param int $userId
     * @param $variableId
     * @param array $columns
     * @return UserVariable[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Query\Builder[]|\Illuminate\Support\Collection
     */
    public function find($userId, $variableId, $columns = ['*']){
        return UserVariable::whereUserId($userId)->whereVariableId($variableId)->get($columns);
    }
    /**
     * @param int $userId
     * @param $variableId
     * @return UserVariable|null
     */
    public function getWithRelations($userId, $variableId): ?UserVariable{
        return UserVariable::whereUserId($userId)->whereVariableId($variableId)->with([
                    'variable',
                    'defaultUnit',
                    'category',
                    'lastUnit',
                    'lastOriginalUnit',
                    'mostCommonUnit',
                    'lastSource'
                ])->first();
    }
    /**
     * @param $userId
     * @return array
     */
    public function getUserInterests($userId): array{
        $interests = [];
        $userVariables = UserVariable::with('variable')->orWhere(function($query) use ($userId){
            /** @var Builder $query */
            $query->where('outcome_of_interest', true)->where('user_id', $userId);
            })->orWhere(function($query) use ($userId){
                /** @var Builder $query */
                $query->where('predictor_of_interest', true)->where('user_id', $userId);
            })->get();
        if($userVariables){
            foreach($userVariables as $uv){
                if($uv->outcome_of_interest){
                    $interests['outcome'] = $uv->getVariable()->name;
                }else{
                    $interests['predictor'] = $uv->getVariable()->name;
                }
            }
        }
        return $interests;
    }
    /**
     * @param $userId
     * @param $term
     * @return array|\Illuminate\Support\Collection
     */
    public function autocompleteSearch($userId, $term){
        $partialMatches = [];
        /**
         * http://laravel.com/docs/5.1/eloquent-relationships#querying-relations
         * Can't see a problem with the following query but it doesn't work currently
         */
        //        $userVariables = UserVariable::with(['variable' => function ($query) use ($term) {
        //            $query->where('name', \App\Storage\DB\ReadonlyDB::getSearchOperator(), '%'.$term.'%');
        //        }])->take(5)->get();
        $qb = Db::table('user_variables as uv')->select('v.id', 'v.name as value');
        $qb->join('variables as v', function(JoinClause $join){
            $join->on('v.id', '=', 'uv.variable_id');
        });
        $qb->where('uv.user_id', $userId);
        $qb->where('v.name', \App\Storage\DB\ReadonlyDB::like(), '%'.$term.'%');
        $variables = $qb->take(5)->get();
        $exactMatchVariables = [];
        if(count($variables) === 5){
            foreach($variables as $variable){
                if(strtolower($variable->value) === strtolower($term)){
                    $exactMatchVariables[] = $variable;
                }else{
                    $partialMatches[] = $variable;
                }
            }
            if(!isset($exactMatchVariables)){
                $qb->where('v.name', $term);
                $exactMatchVariables = $qb->take(1)->get();
            }
            if($exactMatchVariables){
                $variables = array_merge($exactMatchVariables, $partialMatches);
            }else{
                return $partialMatches;
            }
        }
        return $variables;
    }
    /**
     * @param $userId
     * @param $outcomeId
     */
    public function setOutcomeOfInterest($userId, $outcomeId){
        Db::table('user_variables')->where('user_id', $userId)->update(['outcome_of_interest' => null]);
        Db::table('user_variables')
            ->where('user_id', $userId)
            ->where('variable_id', $outcomeId)
            ->update(['outcome_of_interest' => true]);
    }
    /**
     * @param $userId
     * @param $predictorId
     */
    public function setPredictorOfInterest($userId, $predictorId){
        Db::table('user_variables')->where('user_id', $userId)->update(['predictor_of_interest' => null]);
        Db::table('user_variables')
            ->where('user_id', $userId)
            ->where('variable_id', $predictorId)
            ->update(['predictor_of_interest' => true]);
    }
}
