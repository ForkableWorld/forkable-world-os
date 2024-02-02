<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Http\Controllers\Admin;
use App\Buttons\Admin\HorizonButton;
use App\Computers\ThisComputer;
use App\Http\Controllers\Controller;
use App\Jobs\PHPUnitFolderJob;
use App\Slim\View\Request\QMRequest;
use App\Utils\Env;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Request;
use Tests\QMBaseTestCase;
use function le;
use function redirect;
class PHPUnitController extends Controller {
	/**
	 * @param Request $request
	 * @return string
	 */
	public function runPHPUnitTest(Request $request){
		$folderToTest = QMRequest::getInput('folder');
		$branch = QMRequest::getInput('branch');
		return QMBaseTestCase::checkoutAndTestFolder($folderToTest, $branch)->output();
//		if(!Subdomain::isTesting()){ // Don't want to delete production DB!!!
//			return redirect(QMRequest::getTestVersionOfCurrentUrl());
//		}
		//debugger("");
		$cypress = QMRequest::getInput('cypress');
		if($cypress){
			$url = Env::getAppUrl();
			$process = ThisComputer::run("APP_URL=$url cypress run");
			le($process->getOutput());
		}
		$folder = QMRequest::getInput('folder');
		$sha = QMRequest::getInput('sha');
		$immediate = QMRequest::getInput('immediate');
		if($folder){
			if($immediate){
				PHPUnitFolderJob::dispatchSync($folder, $sha);
			} else {
				PHPUnitFolderJob::dispatch($folder, $sha);
			}
			return redirect(HorizonButton::url());
		}
		$class = $request->get('class');
		if(!$class){
			le("Please provide test class param!");
		}
		$test = $request->get('test');
		if(!$test){
			le("Please provide test test param!");
		}
		$result = QMBaseTestCase::runTestOrClass($class, $test);
		le($result); // Should show ignition page with glows?
	}
}
