<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Measurement;
use App\AppSettings\AppSettings;
use App\DataSources\QMConnector;
use App\DataSources\QMDataSource;
use App\Models\Measurement;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\Base\BaseSourceNameProperty;
use App\Slim\View\Request\QMRequest;
use App\Traits\PropertyTraits\MeasurementProperty;
class MeasurementSourceNameProperty extends BaseSourceNameProperty
{
    use MeasurementProperty;
    public $table = Measurement::TABLE;
    public $parentClass = Measurement::class;
    protected $shouldNotContain = [
        "googleusercontent",
    ];
	public $shouldNotEqual = [
		"0",
	];
	/**
	 * @param null $data
	 * @return string
	 */
	public static function getDefault($data = null): ?string{
        if($c = QMConnector::getCurrentlyImportingConnector()){
            return $c->displayName;
        }
        $connectorId = MeasurementConnectorIdProperty::pluck($data);
        if($connectorId){
            return QMDataSource::getDataSourceWithoutDBQuery($connectorId)->displayName;
        }
        if($sourceName = QMRequest::getQueryParam('appName')){
            return $sourceName;
        }
        if($clientId = MeasurementClientIdProperty::pluck($data)){
            $name = AppSettings::findInMemory($clientId);
            if($name){return $name;}
        }
        if(!$clientId){
            $clientId = BaseClientIdProperty::fromMemory();
        }
        return $clientId ?? null;
    }
}
