<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\PhpUnitJobs\Code;
use App\Files\FileHelper;
use App\Logging\QMLog;
use App\Models\Variable;
use App\PhpUnitJobs\JobTestCase;
use App\Types\QMStr;
use App\VariableCategories\NutrientsVariableCategory;
use App\Variables\QMCommonVariable;
class VariableModelGenerator extends JobTestCase{
    public function testGenerateVariableModel(){
        $variables = QMCommonVariable::getCommonVariables([
            Variable::FIELD_VARIABLE_CATEGORY_ID     => NutrientsVariableCategory::ID,
            Variable::FIELD_NUMBER_OF_USER_VARIABLES => "(gt)50"
        ]);
        foreach ($variables as $v){
            $v->generateChildModelCode();
        }
    }
    public function testMoveFiles() {
        $variables = QMCommonVariable::getHardCodedVariables();
        $total = count($variables);
        $i = 0;
        foreach ($variables as $constant) {
            $i++;
            $percent = round($i / $total * 100);
            $constant->logInfo("$percent% complete");
            /** @var QMCommonVariable $constant */
            $category = QMStr::toClassName($constant->getVariableCategoryName());
            $originalPath = $constant->filePath;
            $newPath =
                str_replace("CommonVariables", "CommonVariables/" . $category . "CommonVariables", $originalPath);
            if (file_exists($newPath)) {
                \App\Logging\ConsoleLog::info("$newPath already exists");
                continue;
            }
            $previousClassName = "CommonVariables\\" . QMStr::afterLast($originalPath, '/');
            $previousClassName = str_replace('.php', '', $previousClassName);
            $newClassName =
                str_replace("CommonVariables", "CommonVariables\\" . $category . "CommonVariables", $previousClassName);
            $before = file_get_contents($originalPath);
            $after = str_replace("\CommonVariables;", "\CommonVariables\\" . $category . "CommonVariables;", $before);
            FileHelper::writeByFilePath($newPath, $after);
            FileHelper::replaceTextInAllFilesRecursively('Api', $previousClassName, $newClassName, 'php');
            FileHelper::replaceTextInAllFilesRecursively('tests', $previousClassName, $newClassName, 'php');
            FileHelper::replaceTextInAllFilesRecursively('Jobs', $previousClassName, $newClassName, 'php');
            //$constant->generateChildModelCode();
        }
    }
    public function testGenerateTestVariables() {
        $names = [
            0   => 'Commits',
            1   => 'BMI',
            2   => 'Active Time',
            3   => 'App Usage',
            4   => 'CauseVariableName',
            5   => 'EffectVariableName',
            6   => 'LatestSourceTimeVariableName',
            7   => 'Back Pain',
            8   => 'Temperature at Glen Carbon Crossing, Illinois, US',
            9   => 'AnyEffect',
            10  => 'AnyCause',
            11  => 'AnyCauseSumFilling',
            12  => 'Overall Mood',
            13  => 'Outdoor Temperature',
            14  => 'Calories Burned',
            15  => 'REM Sleep Duration',
            16  => 'Deep Sleep Duration',
            17  => 'Light Sleep Duration',
            18  => 'Duration of Awakenings During Sleep',
            19  => 'Periods of Deep Sleep',
            20  => 'Periods of Light Sleep',
            21  => 'Periods of REM Sleep',
            22  => 'Awakenings',
            23  => 'Daily Step Count',
            24  => 'Walk Or Run Distance',
            25  => 'Body Weight',
            26  => 'Body Mass Index Or BMI',
            27  => 'Body Fat',
            28  => 'Caloric Intake',
            29  => 'Water',
            30  => 'Sleep Start Time',
            31  => 'Time In Bed',
            32  => 'Sleep Duration',
            33  => 'Minutes To Fall Asleep',
            34  => 'Minutes After Wakeup Still In Bed',
            35  => 'Sleep Efficiency From Fitbit',
            36  => 'Heart Rate (Pulse)',
            37  => 'Fat Burn Heart Rate Zone Calories Out',
            38  => 'Fat Burn Heart Rate Zone',
            39  => 'Resting Heart Rate (Pulse)',
            40  => 'Cardio Heart Rate Zone Calories Out',
            41  => 'Cardio Heart Rate Zone',
            42  => 'Peak Heart Rate Zone Calories Out',
            43  => 'Peak Heart Rate Zone',
            44  => 'Vitamin B-12, Added',
            45  => 'Cloud Cover',
            46  => 'Efficiency Score From Rescuetime',
            47  => 'Hourly Step Count',
            48  => 'Code Commits',
            49  => 'Precipitation',
            50  => 'Outdoor Humidity',
            51  => 'Blood Pressure (Diastolic - Bottom Number)',
            52  => 'Fatty Acids, Total Polyunsaturated',
            53  => 'Fatty Acids, Total Monounsaturated',
            54  => 'Fatty Acids, Total Saturated',
            55  => 'Time Spent On Business Activities',
            56  => 'Vitamin E, Added',
            57  => 'Folate, DFE',
            58  => 'Folate, Food',
            59  => 'Vitamin K (phylloquinone)',
            60  => 'Choline, Total',
            61  => 'Folate, Total',
            62  => 'Vitamin B-6',
            63  => 'Vitamin C, Total Ascorbic Acid',
            64  => 'Lutein + Zeaxanthin',
            65  => 'Cryptoxanthin, Beta',
            66  => 'Vitamin D (D2 + D3)',
            67  => 'Average Daily Outdoor Temperature',
            68  => 'Moderately Unproductive Score',
            69  => 'Very Unproductive Score',
            70  => 'Moderately Productive Score',
            71  => 'Very Productive Score',
            72  => 'Productivity Pulse From Rescuetime',
            73  => 'Twitter Status Update',
            74  => 'Wind Speed',
            75  => 'Time Between Sunrise And Sunset',
            76  => 'Daily Low Outdoor Temperature',
            77  => 'Daily High Outdoor Temperature',
            78  => 'Vitamin E (alpha-tocopherol)',
            79  => 'Pollen Index',
            80  => 'Indoor Pressure',
            81  => 'Indoor Noise',
            82  => 'Indoor Humidity',
            83  => 'Indoor CO2',
            84  => 'Indoor Temperature',
            85  => 'Air Quality Index',
            86  => 'Likes On Your Facebook Posts',
            87  => 'Comments On Your Facebook Posts',
            88  => 'Facebook Posts Made',
            89  => 'Facebook Pages Liked',
            90  => 'Vitamin A',
            91  => 'Time Spent On Communication And Scheduling Hours',
            92  => 'Calcium (%RDA)',
            93  => 'Vitamin C (%RDA)',
            94  => 'Sugar (g)',
            95  => 'Time Spent Shopping',
            96  => 'Barometric Pressure',
            97  => 'Fructose',
            98  => 'Core Body Temperature',
            99  => 'Blood Pressure (Systolic - Top Number)',
            100 => 'Trans Fat',
            101 => 'Saturated Fat',
            102 => 'Polyunsaturated Fat',
            103 => 'Time Spent On Social Networking',
            104 => 'Sodium',
            105 => 'Protein',
            106 => 'Potassium',
            107 => 'Monounsaturated Fat',
            108 => 'Iron',
            109 => 'Fiber',
            110 => 'Fat',
        ];
        foreach ($names as $name) {
            $v = QMCommonVariable::findByName($name);
            if (!$v) {
                \App\Logging\ConsoleLog::info("no $name");
                continue;
            }
            $v->generateChildModelCode();
        }
    }
}
