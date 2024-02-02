<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers;

use App\DataSources\Connectors\Exceptions\ConnectorDisabledException;
use App\Exceptions\NoGeoDataException;
use App\Exceptions\UnauthorizedException;
use App\Models\Connection;
use App\Models\ConnectorImport;
use App\Utils\UrlHelper;
class ApiConnectorController extends Controller
{
	/**
	 * @param int|null $id
	 * @return mixed
	 * @throws ConnectorDisabledException
	 * @throws NoGeoDataException
	 * @throws UnauthorizedException
	 */
    public function import(int $id = null)
    {
		if($id){
			$import = ConnectorImport::find($id);
			if(!$import->canReadMe()){
				throw new UnauthorizedException();
			}
			return UrlHelper::redirect(ConnectorImport::generateDataLabShowUrl($id));
		}
        $c = Connection::findByRequest();
	    if(!$c->canWriteMe()){
		    throw new UnauthorizedException();
	    }
        $c->import(__METHOD__);
		if(!$c->canReadMe()){
			throw new UnauthorizedException();
		}
        return $c->getHtmlPage();
    }
}
