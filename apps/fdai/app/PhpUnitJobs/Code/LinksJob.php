<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnhandledExceptionInspection */
namespace App\PhpUnitJobs\Code;
use App\PhpUnitJobs\JobTestCase;
use App\Storage\DB\Writable;
use App\Utils\QMRoute;
class LinksJob extends JobTestCase {
	public function testGenerateLinksFromRoutes(){
		Writable::updateTableComments();
		$admin = QMRoute::getAdminMiddlewareRoutes();
		$datalab = QMRoute::getDataLabRoutes();
		$noMiddleware = QMRoute::getRoutesWithoutMiddleware();
		QMRoute::generateWpLinksForRoutes();
	}
}
