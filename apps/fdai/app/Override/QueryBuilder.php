<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Override;
use App\Models\Measurement;
use App\Storage\MemoryOrRedisCache;
use App\Types\QMStr;
use App\Utils\AppMode;
use Illuminate\Database\Query\Builder;
class QueryBuilder extends Builder {
    private $withCache = 0;
    public function withCache(): QueryBuilder{
        $this->withCache = 1;
        return $this;
    }
    //@Override
    public function get($columns = ['*']){
        //If withCache() was called, let's cache the query
        if($this->withCache){
            //Get the raw query string with the PDO bindings
            $sql_str = str_replace('?', '"%s"', $this->toSql());
            $sql_str = vsprintf($sql_str, $this->getBindings());
            return MemoryOrRedisCache::remember('query:'.hash('sha256', $sql_str), 15, function() use ($columns){
                return parent::get($columns);
            });
        }else{
            //Return default
            return parent::get($columns);
        }
    }

    public function truncate(){
        if(!AppMode::isTestingOrStaging() && $this->getTableName() === Measurement::TABLE){
            throw new \LogicException("Can't delete measurements!");
        }
        parent::truncate();
    }
    /**
     * @return string
     */
    private function getTableName(): string{
        return QMStr::before(" ", $this->from, $this->from);
    }
}
