<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Logging\QMIgnition;
use App\Storage\S3\S3Private;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use function le;
class IgnitionController extends Controller {
	/**
	 * @throws FileNotFoundException
	 */
	public function show(){
		$time = $this->request->get('time');
		if(!$time){
			le("Please provide time param!");
		}
		$html = S3Private::getHtml(QMIgnition::getReportPath($time));
		if(!$html){
			le("Report $time not found!");
		}
		$html = QMIgnition::replace_path_placeholders($html);
		$html = str_replace('encodeURIComponent(e)', 'e', $html);
		echo $html;
	}
}
