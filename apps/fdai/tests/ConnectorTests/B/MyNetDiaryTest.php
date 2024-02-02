<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\ConnectorTests\B;
use App\Properties\Connection\ConnectionImportedDataFromAtProperty;
use Tests\ConnectorTests\ConnectorTestCase;
class MyNetDiaryTest extends ConnectorTestCase {
    const CREDENTIALS_USERNAME = 'quantimodo';
    const CREDENTIALS_PASSWORD = 'B1ggerstaff!';
    public function testMyNetDiary() {
        $this->skipTest('Failing!');
        $this->connectorName = 'mynetdiary';
		$this->credentials = [
			'username' => self::CREDENTIALS_USERNAME,
			'password' => self::CREDENTIALS_PASSWORD,
		];
		$this->fromTime = ConnectionImportedDataFromAtProperty::generateEarliestUnixTime();
		$this->variablesToCheck = [
			'Carbs',
			'Cholesterol',
			'Fat',
			'Fiber',
			'Iron',
			'Protein',
			'Sodium',
			'Calories',
			'Net Carbs',
			'Diabetes Carbs',
			'Zinc (%RDA)',
			'Water (g)',
		];
        $this->connectImportDisconnect();
    }
}
