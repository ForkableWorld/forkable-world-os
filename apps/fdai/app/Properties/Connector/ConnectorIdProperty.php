<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Connector;
use App\Models\Connector;
use App\Properties\Base\BaseIntegerIdProperty;
use App\Slim\View\Request\QMRequest;
use App\Traits\PropertyTraits\ConnectorProperty;
use App\Traits\PropertyTraits\IsPrimaryKey;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
class ConnectorIdProperty extends BaseIntegerIdProperty{
	use IsPrimaryKey;
    use ConnectorProperty;
    public $table = Connector::TABLE;
    public $parentClass = Connector::class;
    public $isPrimary = true;
    public $autoIncrement = true;
    public const SYNONYMS = [
        'connector_id',
        'id',
    ];
	/**
	 * @param bool $throwException
	 * @return int
	 */
	public static function fromRequest(bool $throwException = true): int{
		$connectorId = QMRequest::getParam('connectorId');
		if($connectorId){
			return $connectorId;
		}
		$connector = QMRequest::getParam('connector');
		if($connector){
			$connector = json_decode(json_encode($connector), true);
			$connectorId = $connector['realId'] ?? $connector['id'];
		}
		if(!$connectorId && $throwException){
			throw new BadRequestHttpException("Please provide connector object or connectorId with request!");
		}
		return $connectorId;
	}
}
