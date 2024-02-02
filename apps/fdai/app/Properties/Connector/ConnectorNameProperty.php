<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Connector;
use App\Models\Connector;
use App\Properties\Base\BaseNameProperty;
use App\Slim\View\Request\QMRequest;
use App\Traits\PropertyTraits\ConnectorProperty;
use App\Types\QMStr;
class ConnectorNameProperty extends BaseNameProperty
{
    use ConnectorProperty;
    public $table = Connector::TABLE;
    public $parentClass = Connector::class;
    public $minLength = 2;
	/**
	 * @return string
	 */
	public static function fromRequest(bool $throwException = false): ?string{
		if(QMRequest::getParam('connectorName')){
			return QMRequest::getParam('connectorName');
		}
		$url = QMRequest::urlWithoutProtocol();
		$string = QMStr::after('/connectors/', $url);
		if(empty($string)){
			return null;
		}
		return QMStr::before('/', $string);
	}
}
