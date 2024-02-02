<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\Traits;
use App\Models\UserVariable;
use App\Models\Variable;
use App\Properties\Base\BaseFillingTypeProperty;
use App\Slim\Model\Measurement\AnonymousMeasurement;
use App\Slim\Model\QMUnit;
use App\Variables\QMCommonVariable;
use App\Variables\QMUserVariable;
use App\Variables\QMVariable;
trait QMVariableTestTrait
{
    public static function assertWeCanGetCommonVariableByName(string $name){
        $v = QMCommonVariable::findByName($name);
        static::assertNotNull($v, "$name Variable not found.  See available variables at: ".
            Variable::generateDataLabIndexUrlsString());
    }
    /**
     * @param $nameOrId
     * @return Variable
     */
    protected function assertVariableExistsInDb($nameOrId): Variable{
        if(is_int($nameOrId)){
            $v = Variable::whereId($nameOrId)->first();
        } else{
            $v = Variable::whereName($nameOrId)->first();
        }
        $this->assertNotNull($v, "$nameOrId variable not found in database!");
        return $v;
    }
	/**
	 * @param $expectedNameOrId
	 * @param $variable
	 */
	public function assertVariableUnitIs($expectedNameOrId, $variable){
        $expectedUnit = QMUnit::getByNameOrId($expectedNameOrId);
        if($variable instanceof UserVariable || $variable instanceof QMUserVariable){
            $userVariable = $variable;
            $commonVariable = $variable->getCommonVariable();
        } else{
            $commonVariable = $variable;
            $userVariable = null;
        }
        $actualCommonUnit = $commonVariable->getCommonUnit();
        $message = "\nCommon Unit for $commonVariable->name is $actualCommonUnit but expected $expectedUnit.".
            "\nCommon Variable: ".$commonVariable->getUrlsString();
        if($userVariable){
            $userUnit = $userVariable->getUserUnit();
            $message .= "\nUser Unit is $userUnit. ".
                "\nUser Variable: ".$userVariable->getUrlsString();
        }
        $this->assertEquals($expectedUnit->name, $actualCommonUnit->name, "Wrong common unit!  ". $message);
        if($userVariable){
            $this->assertEquals($expectedUnit->name, "Right common unit, but wrong user unit!  ". $actualCommonUnit->name, $message);
        }
    }
    public static function assertVariableMeasurementAtFieldsNotNull(int $id){
        static::assertFieldsLikeNotNull(Variable::class, $id, '_measurement_at');
    }
	/**
	 * @param $variableNameOrId
	 * @throws \App\Exceptions\AlreadyAnalyzedException
	 * @throws \App\Exceptions\AlreadyAnalyzingException
	 * @throws \App\Exceptions\ModelValidationException
	 */
	public static function analyzeAndCheckCommonVariable($variableNameOrId){
        $v = QMCommonVariable::findByNameOrId($variableNameOrId);
        $v->setAnalysisEndedAt(db_date(time() - 6 * 60)); // Make sure we don't get already analyzing exception
        $v->analyzeFully(__FUNCTION__);
        static::assertNotNull($v->numberOfRawMeasurementsWithTagsJoinsChildren);
        static::assertGreaterThanOrEqual($v->numberOfMeasurements,
            $v->numberOfRawMeasurementsWithTagsJoinsChildren,
            "There are $v->numberOfMeasurements numberOfRawMeasurements for $v but ".
            "$v->numberOfRawMeasurementsWithTagsJoinsChildren numberOfRawMeasurementsWithTagsJoinsChildren");
        static::assertVariableMeasurementAtFieldsNotNull($v->getId());
        $measurements = $v->getMeasurementsWithTags();
        $latest = AnonymousMeasurement::last($measurements);
        $row = Variable::find($v->getVariableIdAttribute());
        $field = Variable::FIELD_LATEST_TAGGED_MEASUREMENT_START_AT;
        static::assertDateEquals($latest->getStartAt(), $row->$field);
    }
    /**
     * @param int $expected
     * @param QMVariable $variable
     * @return array
     */
    public static function assertDataSourcesCount(int $expected, QMVariable $variable): array{
        $names = $variable->getDataSourceDisplayNames();
        if(count($names) !== $expected){
            $names = $variable->getDataSourceDisplayNames();
        }
        self::assertCount($expected, $names,
            "$variable has $variable->numberOfMeasurements measurements and source names for $variable are: \n\t".
            \App\Logging\QMLog::print_r($names, true));
        return $names;
    }
    /**
     * @param QMVariable $variable
     * @param string $message
     */
    public static function assertNoFillingValue(QMVariable $variable, string $message = ""): void{
        self::assertNull($variable->getFillingValueAttribute(), $message);
        self::assertFalse($variable->hasFillingValue(), $message);
        self::assertFalse($variable->hasFillingValue(), $message);
        self::assertNull($variable->fillingValue, $message);
        self::assertEquals(BaseFillingTypeProperty::FILLING_TYPE_NONE, $variable->getFillingTypeAttribute(), $message);
    }
    /**
     * @param QMVariable|UserVariable $variable
     * @param string $message
     */
    public static function assertZeroFillingValue($variable, string $message = ""): void{
        self::assertEquals(BaseFillingTypeProperty::FILLING_TYPE_ZERO, $variable->getFillingTypeAttribute(), $message);
        self::assertEquals(0, $variable->getFillingValueAttribute(), $message);
        self::assertTrue($variable->hasFillingValue(), $message);
        self::assertEquals(0, $variable->fillingValue, $message);
        self::assertEquals(BaseFillingTypeProperty::FILLING_TYPE_ZERO, $variable->getFillingTypeAttribute(), $message);
        self::assertTrue($variable->hasFillingValue(), $message);
    }
}
