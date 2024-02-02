<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\ConnectorTests\B;
use App\DataSources\Connectors\MyFitnessPalConnector;
use Tests\ConnectorTests\ConnectorTestCase;
/**
 * Class MyFitnessPalTest
 * @package Tests\Api\Connectors
 */
class MyFitnessPalTest extends ConnectorTestCase {
    public const CREDENTIALS_USERNAME = 'mikesinn524';
    public const CREDENTIALS_PASSWORD = 'c5&Y1jEb$P';
    public const DISABLED_UNTIL = MyFitnessPalConnector::DISABLED_UNTIL;
    public function testMyFitnessPal(){
        $this->connectorName = MyFitnessPalConnector::NAME;
		$this->credentials = [
			'username' => self::CREDENTIALS_USERNAME,
			'password' => self::CREDENTIALS_PASSWORD,
		];
		$this->fromTime = time() - 63113852;
		$this->variablesToCheck = [
			'Carbs',
			'Fat',
			'Protein',
			'Saturated Fat',
			//'Polyunsaturated Fat',
			//'Monounsaturated Fat',
			//'Trans Fat',
			'Cholesterol',
			'Sodium',
			'Potassium',
			'Fiber',
			'Sugar (g)',
			'Vitamin A',
			'Vitamin C (%RDA)',
			'Iron',
			//'Calcium (%RDA)',
		];
        $this->connectImportDisconnect();
    }

}
