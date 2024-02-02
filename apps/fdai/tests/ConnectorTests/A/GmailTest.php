<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\ConnectorTests\A;
use App\DataSources\Connectors\GmailConnector;
use App\Models\Variable;
use App\Properties\Variable\VariableNameProperty;
use App\Variables\QMCommonVariable;
use Tests\UnitTests\Products\AmazonTest;
use Tests\ConnectorTests\ConnectorTestCase;

class GmailTest extends ConnectorTestCase {
    public const DISABLED_UNTIL = "2019-01-01";
    public function testGmail(){
        if(time() < strtotime(AmazonTest::DISABLED_UNTIL)){
            $this->skipTest('Waiting for Amazon to approve use of US product API at '.
                'https://affiliate-program.amazon.com/assoc_credentials/home');
            return;
        }
        $this->truncateTrackingReminders();
        $this->fromTime = time() - 365 * 86400;
        $this->variablesToCheck = [VariableNameProperty::toSpending(GmailConnector::TEST_VARIABLE)];
        $this->connectImportCheckDisconnect();
        $testVariable = QMCommonVariable::findByNameIdOrSynonym(GmailConnector::TEST_VARIABLE);
        $this->assertNotNull($testVariable, "No variable with name ".GmailConnector::TEST_VARIABLE);
        $paymentVariable = $testVariable->getSpendingVariable();
        $testVariable->validateAttribute(Variable::FIELD_PRODUCT_URL);
        $paymentVariable->validateAttribute(Variable::FIELD_PRODUCT_URL);
        $this->assertHasTrackingReminderFor($testVariable->getVariableName(), GmailConnector::NAME);
        $this->checkConnectorLogin();
    }
}
