<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpPropertyOnlyWrittenInspection */
namespace App\Exceptions;
use App\Models\BaseModel;
class NoChangesException extends \Exception
{
    /**
     * @var BaseModel
     */
    private $existingModel;
	/**
	 * @param $providedData
	 * @param \App\Models\BaseModel|null $existingModel
	 */
	public function __construct($providedData, BaseModel $existingModel = null){
        $this->existingModel = $existingModel;
        parent::__construct("This record already existed and nothing changed.\nProvided data:".
            \App\Logging\QMLog::print_r($providedData, true), 400);
    }
}
