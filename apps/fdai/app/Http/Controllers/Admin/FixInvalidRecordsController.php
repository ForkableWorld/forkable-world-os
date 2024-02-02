<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers\Admin;
use App\Properties\BaseProperty;
use App\Slim\Middleware\QMAuth;
use App\Slim\View\Request\QMRequest;
use App\Types\QMStr;
use App\UI\Alerter;
use App\Utils\EnvOverride;
use App\Utils\UrlHelper;
use Tests\TestGenerators\CleanupPhpUnitTestFile;
use function redirect;
class FixInvalidRecordsController {
	public const PATH = 'fix-invalid-records';
	public function fixInvalidRecords(){
		$user = QMAuth::getQMUser();
		/** @var BaseProperty $class */
		$class = QMRequest::getParam('class');
		/** @var BaseProperty $prop */
		$prop = new $class();
		$parent = $prop->getParentModel();
		$short = QMStr::toShortClassName($class);
		if(EnvOverride::isLocal()){
			$url = CleanupPhpUnitTestFile::getUrl("fixInvalid" . $short . "Records", "$short::fixInvalidRecords();",
				$class);
			return redirect($url);
		} else{
			$result = $class::fixInvalidRecords();
			Alerter::toastWithHtml("Fixed " . count($result) . " invalid " . QMStr::classToTitle($class) . " records");
			return redirect($parent->generateDataLabIndexUrl());
		}
	}
	public static function generateFixInvalidRecordsUrl(string $propertyClass): string{
		return UrlHelper::generateApiUrl(self::PATH, ['class' => $propertyClass]);
	}
}
