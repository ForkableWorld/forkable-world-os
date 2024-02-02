<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\Share;
use App\Slim\Model\QMResponseBody;
use App\Slim\Model\User\AuthorizedClients;
class ShareResponse extends QMResponseBody {
	public $authorizedClients;
	/**
	 * ShareResponse constructor.
	 * @param null $responseArray
	 * @param int|null $code
	 */
	public function __construct($responseArray = null, int $code = null){
		parent::__construct($responseArray, $code);
	}
	/**
	 * @param mixed $authorizedClients
	 */
	public function setAuthorizedClients(AuthorizedClients $authorizedClients): void{
		$this->authorizedClients = $authorizedClients;
	}
}
