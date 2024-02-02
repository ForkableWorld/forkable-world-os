<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Variable;
use App\DataSources\QMDataSource;
use App\Logging\QMLog;
use App\Models\Measurement;
use App\Models\UserVariableClient;
use App\Models\Variable;
use App\Properties\Base\BaseDataSourcesCountProperty;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Storage\DB\ReadonlyDB;
use App\Traits\PropertyTraits\IsCalculated;
use App\Traits\PropertyTraits\VariableProperty;
use App\Types\QMArr;
use App\Variables\QMCommonVariable;
class VariableDataSourcesCountProperty extends BaseDataSourcesCountProperty
{
    use VariableProperty;
    use IsCalculated;
    public $table = Variable::TABLE;
    public $parentClass = Variable::class;
	/**
	 * @param $dataSourcesCount
	 * @return mixed
	 */
	public static function convertDataSourcesCountToDataSources($dataSourcesCount): array{
		if(!$dataSourcesCount){
			return [];
		}
		$dataSources = [];
		foreach($dataSourcesCount as $dataSourceName => $count){
			$dataSourceName = strtolower($dataSourceName);
			if(isset($dataSources[$dataSourceName])){
				continue;
			}
			$dataSource = QMDataSource::getDataSourceWithoutDBQuery($dataSourceName);
			if(!$dataSource && strpos($dataSourceName, " ")){ // MedHelper is stored as "med helper" for some reason
				$noSpaces = str_replace(" ", "", $dataSourceName);
				$dataSource = QMDataSource::getDataSourceWithoutDBQuery($noSpaces);
			}
			if($dataSource){
				$dataSource->setCount($count);
				if(isset($dataSources[$dataSource->displayName])){
					$dataSource->setCount($count + $dataSources[$dataSource->displayName]->count);
				}
				$dataSources[$dataSource->displayName] = $dataSource;
			}
		}
		QMArr::sortDescending($dataSources, 'count');
		$indexedByDisplayName = [];
		/** @var QMDataSource $dataSource */
		foreach($dataSources as $dataSource){
			$indexedByDisplayName[$dataSource->displayName] = $dataSource;
		}
		return $indexedByDisplayName;
	}
	public static function updateDataSourcesCount()
    {
        //Measurement::writable()->where(Measurement::FIELD_SOURCE_NAME, "0")->update([Measurement::FIELD_SOURCE_NAME => null]);
        //Measurement::writable()->where(Measurement::FIELD_SOURCE_NAME, "1052648855194.apps.googleusercontent.com")->update([Measurement::FIELD_SOURCE_NAME => null]);
        $variableIdsWithEmptySources = QMCommonVariable::readonly()
            ->where(self::NAME, "[]")
            ->select(['id'])
            ->getArray();
        $total = count($variableIdsWithEmptySources);
        QMLog::info("$total variables have empty data sources count");
        $db = $db = ReadonlyDB::db();
        $i = 0;
        foreach ($variableIdsWithEmptySources as $variableId) {
            $i++;
            $progress = "($i of $total)";
            $sourcesCount = [];
            $variableId = $variableId->id;
            $measurements = QMMeasurement::readonly()
                ->whereNotNull(Measurement::FIELD_CLIENT_ID)
                ->select([
                    Measurement::FIELD_VARIABLE_ID,
                    Measurement::FIELD_SOURCE_NAME,
                    Measurement::FIELD_CLIENT_ID,
                    $db->raw("COUNT(*) as count")
                ])
                ->groupBy(Measurement::FIELD_CLIENT_ID)
                ->where(Measurement::FIELD_VARIABLE_ID, $variableId)
                ->getArray();
            if (empty($measurements)) {
                QMLog::info("No measurement sources for $variableId $progress");
                continue;
            }
            foreach ($measurements as $m) {
                $sourcesCount[$m->source_name] = $m->count;
            }
            QMLog::info("Updating $variableId with " . json_encode($sourcesCount) . " $progress");
            Variable::query()
                ->where(Variable::FIELD_ID, $variableId)
                ->update([self::NAME => json_encode($sourcesCount)]);
        }
    }
    /**
     * @param Variable $model
     * @return array
     */
    public static function calculate($model): array{
        $uvcs = $model->user_variable_clients()->get();
        if(!$uvcs->count()){return [];}
        $dsc = [];
        /** @var UserVariableClient $uvc */
        foreach($uvcs as $uvc){
            $name = ($ds = $uvc->getQMDataSource()) ? $ds->getTitleAttribute() : $uvc->client_id;
            if(!isset($dsc[$name])){$dsc[$name] = 0;}
            $dsc[$name]++;
        }
        $model->setAttribute(static::NAME, $dsc);
        return $dsc;
    }
}
