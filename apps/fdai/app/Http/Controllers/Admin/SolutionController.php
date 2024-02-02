<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Slim\View\Request\QMRequest;
use App\Solutions\BaseRunnableSolution;
use App\Utils\EnvOverride;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Tests\TestGenerators\SolutionPhpUnitTestFile;
class SolutionController extends Controller {
	/**
	 * @return Application|RedirectResponse|Redirector|string
	 */
	public function runSolution(){
		if(EnvOverride::isLocal()){
			$url = SolutionPhpUnitTestFile::generateByClass(QMRequest::getParam(['class', 'solutionClass']));
			return redirect($url);
		}
		return BaseRunnableSolution::runAndRedirect($_GET);
	}
}
