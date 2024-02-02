<?php
namespace Tests\Traits;
use App\Exceptions\TooManyQueriesException;
use App\Logging\QMLog;
use Yajra\DataTables\QueryDataTable;
trait DataTableTestTrait
{
    public function assertQueriesEqual(array $expected, array $actual){
        $actual = json_decode(json_encode($actual), true);
        foreach($actual as $key => $item){
            unset($item['time']);
            $actual[$key] = $item;
        }
        foreach($expected as $key => $item){
            unset($item['time']);
            $expected[$key] = $item;
        }
        $this->assertEquals($expected, $actual);
    }
    /**
     * @param int $expected
     * @throws TooManyQueriesException
     */
    public function assertDataTableQueryCount(int $expected){
        $queries = QueryDataTable::getQueries();
        if(count($queries) > $expected){
            throw new TooManyQueriesException(
                "Expected $expected queries but got ".count($queries), $queries);
        }
    }
    protected function assertDataTableQueriesEqual(array $expected){
        //if(!$response->queries,"No queries from this response: "){le($response);}
        $queries = QueryDataTable::getQueries();
        if($queries !== $expected){
            $message = "ACTUAL QUERIES:\n".QMLog::var_export($queries, true).
                "\nDO NOT MATCH EXPECTED:\n".QMLog::var_export($expected);
            self::compareObjectFixture(__FUNCTION__, $queries, $message);
        }
    }
}
