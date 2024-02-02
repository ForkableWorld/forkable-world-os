<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\UnitTests;
use App\CodeGenerators\TVarDumper;
use App\Exceptions\ModelValidationException;
use App\Files\FileFinder;
use App\Logging\QMLog;
use App\Models\BaseModel;
use App\Models\UserVariableRelationship;
use App\Properties\GlobalVariableRelationship\GlobalVariableRelationshipCauseUnitIdProperty;
use App\Properties\BaseProperty;
use App\Storage\DB\QMDB;
use App\Storage\DB\TestDB;
use App\Traits\PropertyTraits\IsTemporal;
use App\Types\QMStr;
use App\Utils\QMProfile;
use Tests\UnitTestCase;
class ValidationTest extends UnitTestCase
{
    public function testValidation(){
        $this->skipTest("need to implement");
        return;
	    TestDB::deleteUserData();
        if($profile = true){QMProfile::startLiveProf();}
        $this->checkClass("GlobalVariableRelationship"
            , GlobalVariableRelationshipCauseUnitIdProperty::class
        );
        if($profile){QMProfile::endProfile();}
        UserVariableRelationship::deleteAll();
        $folders = FileFinder::listFolders('app/Properties');
        foreach($folders as $folder){
            $shortClass = QMStr::afterLast($folder, DIRECTORY_SEPARATOR);
            if($shortClass === "Base"){continue;}
            $this->checkClass($shortClass);
        }
    }
    /**
     * @param BaseModel $m
     * @param $attribute
     * @param $value
     * @param string $exceptionShouldContain
     */
    public function assertThrowsValidationException(BaseModel $m,
                                                    $attribute,
                                                    $value,
                                                    string $exceptionShouldContain): void{
        $before = $m->getAttribute($attribute);
        $m->setAttribute($attribute, $value);
        try {
            $m->save();
            $m->setAttribute($attribute, $value);
            $m->validateAttribute($attribute);
            $exported = TVarDumper::dump($value);
            $this->assertFalse(true,
                "Setting ".
                $m->getShortClassName().
                " $attribute to $exported should have thrown an exception containing $exceptionShouldContain");
        } catch (ModelValidationException $e) {
            try {
                $this->assertContains($exceptionShouldContain, $e->getMessage());
            } catch (\Throwable $assertionException){
                $this->assertContains($exceptionShouldContain, $e->getMessage());
            }
            $m->setAttribute($attribute, $before);
        }
    }
    /**
     * @param BaseProperty $property
     * @param BaseModel $m
     */
    public function checkMinimum(BaseProperty $property, BaseModel $m): void{
        if($property->isUnixtime()){
            /** @var IsTemporal $property */
            $min = $property->getEarliestUnixTime();
            $this->assertThrowsValidationException($m, $property->name, $min - 1000, "earliest");
            return;
        }
        $min = $property->getMinimum();
        if($min !== null){
            $this->assertThrowsValidationException($m, $property->name, $min - 1000, "minimum");
        }
    }
    /**
     * @param BaseProperty $property
     * @param BaseModel $m
     */
    public function checkMaximum(BaseProperty $property, BaseModel $m): void{
        if($property->isUnixtime()){
            /** @var IsTemporal $property */
            $max = $property->getLatestUnixTime();
            $this->assertThrowsValidationException($m, $property->name, $max + 1000, "latest");
            return;
        }
        $max = $property->getMaximum();
        if($max !== null){
            $this->assertThrowsValidationException($m, $property->name, $max + 1000, "maximum");
        }
    }
    /**
     * @param BaseProperty $property
     * @param BaseModel $m
     */
    public function checkMaxLength(BaseProperty $property, BaseModel $m): void{
        $max = $property->getMaxLength();
        if($max > QMDB::LENGTH_LIMIT_TEXT){le('$max > QMDB::LENGTH_LIMIT_TEXT');}
        if($max !== null && $property->isString()){
            $val = str_repeat('*', $max + 1);
            $this->assertThrowsValidationException($m, $property->name, $val, "maximum");
        }
    }
    /**
     * @param BaseProperty $property
     * @param BaseModel $m
     */
    public function checkMinLength(BaseProperty $property, BaseModel $m): void{
        $min = $property->getMinLength();
        if($min !== null){
            $val = str_repeat('*', $min - 1);
            $this->assertThrowsValidationException($m, $property->name, $val, "minimum");
        }
    }
    /**
     * @param string|null $shortClass
     * @param string $propertyClass
     * @throws ModelValidationException
     */
    public function checkClass(string $shortClass, string $propertyClass = null): void{
        /** @var BaseModel $modelClass */
        $modelClass = QMStr::toFullClassName($shortClass);
        \App\Logging\ConsoleLog::info("Checking $shortClass...");
        /** @var BaseModel $m */
        $m = $modelClass::fakeFromPropertyModels();
        $properties = $m->getPropertyModels();
        foreach($properties as $property){
            if($propertyClass && get_class($property) !== $propertyClass){continue;}
            \App\Logging\ConsoleLog::info("Checking $shortClass $property->name");
            $this->checkMinimum($property, $m);
            $this->checkMaximum($property, $m);
            $this->checkMaxLength($property, $m);
            $this->checkMinLength($property, $m);
            $m->validate();
        }
    }
}
