<?php /** @noinspection PhpDocMissingThrowsInspection */
/** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\UnitTests\Variables;
use App\Models\Variable;
use App\Properties\Variable\VariableNameProperty;
use App\Types\QMStr;
use App\Slim\Model\QMUnit;
use App\Units\DollarsUnit;
use App\Units\OuncesUnit;
use App\Units\QuartsUnit;
use App\VariableCategories\EmotionsVariableCategory;
use App\VariableCategories\EnvironmentVariableCategory;
use App\VariableCategories\PhysiqueVariableCategory;
use App\VariableCategories\SleepVariableCategory;
use App\VariableCategories\SocialInteractionsVariableCategory;
use App\VariableCategories\SymptomsVariableCategory;
use App\Variables\CommonVariables\EmotionsCommonVariables\OverallMoodCommonVariable;
use App\Variables\CommonVariables\EnvironmentCommonVariables\PrecipitationCommonVariable;
use App\Variables\CommonVariables\EnvironmentCommonVariables\UVIndexCommonVariable;
use App\Variables\CommonVariables\GoalsCommonVariables\CodeCommitsCommonVariable;
use App\Variables\CommonVariables\SleepCommonVariables\SleepStartTimeCommonVariable;
use App\Variables\CommonVariables\SocialInteractionsCommonVariables\FacebookPagesLikedCommonVariable;
use App\Variables\CommonVariables\SocialInteractionsCommonVariables\FacebookPostsMadeCommonVariable;
use App\Variables\QMCommonVariable;
use Tests\UnitTestCase;
class CommonVariableTest extends UnitTestCase
{
    public function testMinimumSecondsBetweenMeasurements(){
        $v = QMCommonVariable::findByNameOrId(FacebookPagesLikedCommonVariable::NAME);
        $this->assertEquals(SocialInteractionsVariableCategory::ID, $v->getQMVariableCategory()->id);
        $v = QMCommonVariable::findByNameOrId(FacebookPostsMadeCommonVariable::NAME);
        $this->assertEquals(SocialInteractionsVariableCategory::ID, $v->getQMVariableCategory()->id);
        QMCommonVariable::updateDatabaseTableFromHardCodedConstants();
        $idExpectedSeconds = [
            UVIndexCommonVariable::ID => 86400,
            OverallMoodCommonVariable::ID => 60,
            CodeCommitsCommonVariable::ID => 60,
            PrecipitationCommonVariable::ID => 86400,
            SleepStartTimeCommonVariable::ID => 86400,
            FacebookPostsMadeCommonVariable::ID => 60
        ];
        foreach($idExpectedSeconds as $id => $expectedSeconds){
            $v = QMCommonVariable::find($id);
            $this->assertEquals($expectedSeconds, $v->getMinimumAllowedSecondsBetweenMeasurements(),
                $v->name." should have $expectedSeconds min seconds");
        }
        $idExpectedSeconds = [
            EnvironmentVariableCategory::ID => 86400,
            SymptomsVariableCategory::ID => 60,
            EmotionsVariableCategory::ID => 60,
            SleepVariableCategory::ID => 86400,
            PhysiqueVariableCategory::ID => 86400,
        ];
        foreach($idExpectedSeconds as $id => $expectedSeconds){
            $variables = Variable::whereVariableCategoryId($id)->get();
            foreach($variables as $v){
                $this->assertEquals($expectedSeconds, $v->getMinimumAllowedSecondsBetweenMeasurements(),
                    $v->name." should have $expectedSeconds min seconds");
            }
        }
    }
    public function testFormatVariableName(){
        $name = "UV Index";
        $this->assertFormattedVariableNameEquals("UV Index", $name);
        $name = "Bob's Red Mill Organic Rolled Oats, 32 Ounces (Pack of 4)";
        $this->assertFormattedVariableNameEquals("Bob's Red Mill Organic Rolled Oats", $name);
        $name = "6425-6431 N Muscatel Ave, San Gabriel, CA 91775, USA";
        $this->assertFormattedVariableNameEquals("6425-6431 N Muscatel Ave, San Gabriel, CA 91775, USA", $name);
        $name = "Acidic Foods - 6-oz Granules: Guaranteed";
        $this->assertFormattedVariableNameEquals("Acidic Foods - Granules", $name);
        $name = "Regular Can Coke 355ml (12 Oz)";
        $this->assertFormattedVariableNameEquals("Regular Can Coke", $name);
        $name = "Nutricost Psyllium Husk Powder 500 Grams 5g Per Serving";
        $this->assertFormattedVariableNameEquals("Nutricost Psyllium Husk Powder", $name);
        $name = "Sundown Nat Odor Garlic 100 Mg 250 Ea";
        $this->assertFormattedVariableNameEquals("Sundown Nat Odor Garlic", $name);
        $name = "Verena Street 12 oz. Mississippi Grogg Flavored Medium Ground Coffee, Case Of 6";
        $this->assertFormattedVariableNameEquals("Verena Street Mississippi Grogg Flavored Medium Ground Coffee", $name);
        $name = "Sundown Naturals Melatonin 5 mg Gummies (Pack of 60), Strawberry Flavored, Supports Sound, Quality Sleep*, Gluten Free, Dairy Free, Non-GMO, No Artificial Flavors";
        $this->assertFormattedVariableNameEquals("Sundown Naturals Melatonin Gummies", $name);
        $name = "1521412625 Unique Test Variable";
        $this->assertFormattedVariableNameEquals("1521412625 Unique Test Variable", $name);
        $name = "3 of MAX CAT Adult Cat Food Roasted Salmon Flavor";
        $this->assertFormattedVariableNameEquals("MAX CAT Adult Cat Food Roasted Salmon Flavor", $name);
        $name = "IncompatibleMeasurements Test Variable (Duration)";
        $this->assertFormattedVariableNameEquals("IncompatibleMeasurements Test Variable (Duration)", $name);
        $name = "Fresh Organic Bananas Approximately 3 Lbs 1 Bunch of 6-9 Bananas";
        $this->assertFormattedVariableNameEquals("Fresh Organic Bananas", $name);
        $name = "D'Addario EJ16 Phosphor Bronze Light Acoustic Guitar Strings Single-Pack";
        $number = QMStr::getNumberFromStringWithLeadingSpaceOrAtBeginning($name);
        $this->assertNull($number);
        $this->assertFormattedVariableNameEquals("D'Addario EJ16 Phosphor Bronze Light Acoustic Guitar Strings", $name);
        $name = "Glycerin Vegetable Kosher USP-Highest Quality Available-1 Quart";
        $quart =  QMUnit::getUnitFromString($name);
        $this->assertEquals(QuartsUnit::NAME, $quart->name);
        $this->assertFormattedVariableNameEquals("Glycerin Vegetable Kosher USP", $name);
        $name = "Optimum Nutrition Opti-Men Daily Multivitamin Supplement, 90 Count (Packaging May Vary)";
        $this->assertFormattedVariableNameEquals("Optimum Nutrition Opti-Men Daily Multivitamin Supplement", $name);
        $unit = QMUnit::getByNameOrId(DollarsUnit::NAME);
        $this->assertFormattedVariableNameEquals(VariableNameProperty::SPENDING_ON_VARIABLE_DISPLAY_NAME_PREFIX ."Optimum Nutrition Opti-Men Daily Multivitamin Supplement", $name, $unit);
        $name = "#1 Memory Foam Sleep Mask by Eazy Sleeperz with FREE Ear Plugs and Carry Pouch Breathable and Fully Adjustable for Ultimate";
        $this->assertFormattedVariableNameEquals("Memory Foam Sleep Mask by Eazy Sleeperz", $name);
        $name = "Maldon Sea Salt Flakes, 8.5 Ounce Box";
        $this->assertUnitFromVariableNameEquals(OuncesUnit::NAME, $name);
        $this->assertFormattedVariableNameEquals("Maldon Sea Salt Flakes", $name);
        $name = "Unique Test Variable Mon Sep 19 2016 19:07:55 GMT+0000 (UTC)";
        $this->assertFormattedVariableNameEquals("Unique Test Variable Mon Sep 19", $name);
    }
    /**
     * @param string $expected
     * @param string $original
     * @param null $unit
     */
    private function assertFormattedVariableNameEquals(string $expected, string $original, $unit = null){
        $formatted = VariableNameProperty::sanitizeSlow($original, $unit);
        $this->assertEquals($expected, $formatted);
    }
    /**
     * @param string $expectedUnitName
     * @param string $variableName
     */
    private function assertUnitFromVariableNameEquals(string $expectedUnitName, string $variableName){
        $unitFromVariableName = QMUnit::getUnitFromString($variableName);
        $this->assertEquals($expectedUnitName, $unitFromVariableName->name);
    }
}
