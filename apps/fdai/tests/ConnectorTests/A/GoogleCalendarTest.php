<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\ConnectorTests\A;
use App\DataSources\Connectors\GoogleCalendarConnector;
use Tests\ConnectorTests\ConnectorTestCase;
class GoogleCalendarTest extends ConnectorTestCase{
    public $connectorName = GoogleCalendarConnector::NAME;
    public const DISABLED_UNTIL = "2023-04-01";
    public const REASON_FOR_SKIPPING = "Need to implement Experimental warning on front end";
    public function testGoogleCalendar(){
        if($this->weShouldSkip()){return;}
        $parameters = [
            'source' => GoogleCalendarConnector::ID,
            'fromTime' => strtotime("2012-01-01"),
            'variables' => [
                'Melatonin by NatureMade',
                'Xifaxan',
                'Remeron Powder',
                'Garlic',
                'Chelated Zinc',
                'Magnesium by Sundown Naturals',
                'Acetyl L-Carnitine',
                'Triamcinolone Acetonide',
                'Adderall Xr',
                'Flonase',
                'Doctor\'s Best Alpha-lipoic Acid 600',
                'Clobetasol Propionate Spray',
                'Magnesium Citrate By Now',
                'Bupropion Sr',
                'Kefir By Lifeway',
                'Vitamin D3 by Naturewise',
            ],
        ];
        $this->connectImportCheckDisconnect($parameters);
        $this->checkConnectorLogin();
    }
}
