<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnhandledExceptionInspection */
namespace App\PhpUnitJobs\Code;
use App\VariableRelationships\QMUserVariableRelationship;
use App\Files\FileFinder;
use App\Files\PHP\PhpClassFile;
use App\Models\IpDatum;
use App\Models\MeasurementImport;
use App\PhpUnitJobs\JobTestCase;
use App\Properties\BaseProperty;
use App\Properties\BasePropertyGenerator;
use App\Properties\PropertiesGenerator;
use App\Types\QMStr;
class PropertiesJob extends JobTestCase {
	public function testGenerateProperties(){
		IpDatum::generateProperties();
		MeasurementImport::generateProperties();
		//Variable::generateProperties(Variable::FIELD_DEFAULT_VALUE);
	}
	public function testAddImportant(){
		BaseProperty::addImportanceProperty();
	}
	public function testAddPropertyTraits(){
		PropertiesGenerator::addPrimaryKeyTraits();
	}
	public function testMoveHasTraits(){
		$files = FileFinder::getFilesContaining('app/Traits', "ValueTrait", false, 'php');
		PhpClassFile::moveClasses([
			'app/Traits/IsCalculated.php',
		], "App\\Traits\\PropertyTraits");
	}
	public function testGeneratePropertyTraits(){
		BasePropertyGenerator::generatePropertyTraits();
	}
	public function getGenerateSnakeCamelCaseMap(){
		QMStr::outputSnakeCamelCaseMapsForObject(QMUserVariableRelationship::getUserVariableRelationships(['limit' => 1])[0]);
	}
}
