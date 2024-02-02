<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\Measurement;
use App\DataSources\Connectors\AmazonConnector;
use App\Exceptions\IncompatibleUnitException;
use App\Exceptions\InvalidAttributeException;
use App\Exceptions\InvalidVariableValueAttributeException;
use App\Exceptions\InvalidVariableValueException;
use App\Exceptions\ModelValidationException;
use App\Exceptions\NoChangesException;
use App\Exceptions\UnauthorizedException;
use App\Models\Measurement;
use App\Products\ProductHelper;
use App\Properties\Variable\VariableNameProperty;
use App\Slim\Controller\PostController;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Slim\Model\Reminders\QMTrackingReminder;
use App\Storage\Memory;
use App\Types\QMArr;
use App\Variables\QMCommonVariable;
use App\Variables\QMUserVariable;

class PostMeasurementController extends PostController {
    private array $amazonPaymentUserVariables = [];

    /**
     * @throws IncompatibleUnitException
     * @throws InvalidVariableValueException
     * @throws ModelValidationException
     * @throws NoChangesException
     * @throws InvalidAttributeException
     * @throws UnauthorizedException
     */
    public function post(){
        $variables = $this->getAmazonMeasurementSets();
        if(empty($variables)){
            $variables = $this->saveNormalMeasurements();
        }
        return $this->writeJsonWithGlobalFields(201, [
            'status'  => '201',
            'success' => true,
            'data'    => [
                'userVariables' => $this->unsetNullTagProperties($variables),
                'measurements' => Memory::getNewMeasurementsForUserByVariable(QMAuth::getUser()->id)
            ]
        ]);
    }

    /**
     * @return array|null
     * @throws IncompatibleUnitException
     * @throws InvalidVariableValueException
     * @throws ModelValidationException
     * @throws NoChangesException
     * @throws UnauthorizedException
     * @throws InvalidVariableValueAttributeException
     * @throws NoChangesException
     * @throws UnauthorizedException
     */
    private function getAmazonMeasurementSets(): ?array
    {
        $uncheckedMeasurementSetsRaw = $this->getUncheckedPostBodyArray();
        if(isset($uncheckedMeasurementSetsRaw[0]["total"])){
            //QMGlobals::setCurrentConnector(QMConnector::getConnectorByDisplayName("Amazon"));
            foreach($uncheckedMeasurementSetsRaw as $amazonOrder){
                foreach($amazonOrder["items"] as $itemName => $detailsUrl){
                    $m = new QMMeasurement($amazonOrder['date'], $amazonOrder['total']);
                    $m->setOriginalUnitByNameOrId("$");
                    $m->setSourceName(AmazonConnector::NAME);
                    $this->getUserVariableFromAmazonItem($itemName, $detailsUrl)->addToMeasurementQueue($m);
                }
            }
            foreach($this->getAmazonPaymentUserVariables() as $userVariable){
                $userVariable->saveMeasurements();
            }
            QMTrackingReminder::createRemindersForNewPaymentVariables($this->getAmazonPaymentUserVariables());
            return array_values($this->getAmazonPaymentUserVariables());
        }
        return null;
    }
    /**
     * @return array
     */
    private function getUncheckedPostBodyArray(): array {
        $arr = $this->getRequestJsonBodyAsArray(false);
        if(!isset($arr[0])){$arr = [$arr];}
        return $arr;
    }

    /**
     * @return QMUserVariable[]
     * @throws IncompatibleUnitException
     * @throws InvalidVariableValueException
     * @throws ModelValidationException
     * @throws NoChangesException
     * @throws ModelValidationException
     * @throws ModelValidationException
     */
    private function saveNormalMeasurements(): array
    {
        $uncheckedMeasurementSetsRaw = $this->getUncheckedPostBodyArray();
        if(!$this->isMeasurementSet()){
            return $this->saveMeasurements($uncheckedMeasurementSetsRaw);
        }
        $processed = [];
        foreach($uncheckedMeasurementSetsRaw as $set){
            $items = $this->getMeasurementItems($set);
            unset($set['measurements'], $set['measurementItems']);
            foreach($items as $i){
                $processed[] = QMArr::mergeSnakizedNotNull($set, $i);
            }
        }
        $userVariables = $this->saveMeasurements($processed);
        return $userVariables;
    }

    /**
     * @param array $items
     * @return array
     * @throws IncompatibleUnitException
     * @throws InvalidVariableValueException
     * @throws ModelValidationException
     * @throws NoChangesException
     */
    private function saveMeasurements(array $items): array{
        $measurements = Measurement::upsert($items);
        $userVariables = [];
        foreach($measurements as $m){
            $uv = $m->getQMUserVariable();
            $userVariables[$uv->name] = $uv;
        }
        return $userVariables;
    }
    /**
     * @param $measurementSet
     * @return mixed
     */
    private function getMeasurementItems($measurementSet){
        $measurementItems = QMArr::getValue($measurementSet, [
            'measurementItems',
            'measurements'
        ]);
        return $measurementItems;
    }
    /**
     * @return bool
     */
    private function isMeasurementSet(): bool
    {
        $uncheckedMeasurementSetsRaw = $this->getUncheckedPostBodyArray();
        $measurementItems = $this->getMeasurementItems($uncheckedMeasurementSetsRaw[0]);
        if($measurementItems){
            return true;
        }
        return false;
    }

    /**
     * @param $itemName
     * @param null $asin
     * @return QMUserVariable
     * @throws UnauthorizedException
     */
    private function getUserVariableFromAmazonItem($itemName, $asin = null){
        if(isset($this->amazonPaymentUserVariables[$itemName])){
            return $this->amazonPaymentUserVariables[$itemName];
        }
        $commonVariable = QMCommonVariable::findByNameIdOrSynonym(
            VariableNameProperty::SPENDING_ON_VARIABLE_DISPLAY_NAME_PREFIX.$itemName);
        if($commonVariable){
            return $this->amazonPaymentUserVariables[$itemName] = $commonVariable->findQMUserVariable(
                $this->getUserIdParamOrAuthenticatedUserId());
        }
        //$product = AmazonHelper::getByAsinOrKeyword($asin, $itemName);  Let's not use ASIN because it returns unavailable products sometimes
        $product = ProductHelper::getByKeyword($itemName);
        return $this->amazonPaymentUserVariables[$itemName] = $product->getQMCommonVariableWithActualProductName()
            ->getSpendingVariable()
            ->findQMUserVariable($this->getUserIdParamOrAuthenticatedUserId());
        //return $this->amazonPaymentUserVariables[$itemName] = $product->getCommonVariableWithOriginalSearchTermAsName()->getPaymentVariable()->findUserVariable($this->getUserIdParamOrAuthenticatedUserId());
    }
    /**
     * @return QMUserVariable[]
     */
    public function getAmazonPaymentUserVariables(): array
    {
        return $this->amazonPaymentUserVariables;
    }
}
