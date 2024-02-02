<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Models;
use App\AppSettings\AppSettings;
use App\Http\Controllers\Web\AppsController;
use App\Models\Application;
use App\Models\Collaborator;
use App\Models\OAAccessToken;
use App\Models\OAClient;
use App\Models\OARefreshToken;
use App\Properties\Application\ApplicationIconUrlProperty;
use App\Properties\Base\BaseAccessTokenProperty;
use App\Properties\Base\BaseClientIdProperty;
use App\Slim\Controller\AppSettings\PostAppSettingsController;
use App\Storage\DB\TestDB;
use App\UI\ImageUrls;
use App\Utils\Env;
use DB;
use Illuminate\Auth\AuthenticationException;
use Tests\QMBaseTestCase;
use Tests\UnitTestCase;
/**
 * @package Tests\UnitTests\Files
 * @coversDefaultClass \App\Models\Application;
 */
class StudiesPageTest extends UnitTestCase {

	public function testGetStudiesPage(){
		$this->actingAsUserOne();
		$r = $this->get('studies');
		$content = $r->getContent();
		$this->compareHtmlPage('studies', $content);
		$this->assertContains("studies-input", $content);
	}
}
