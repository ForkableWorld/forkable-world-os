<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Variable;
use App\VariableRelationships\QMGlobalVariableRelationship;
use App\DataSources\Connectors\RescueTimeConnector;
use App\Exceptions\ExceptionHandler;
use App\Exceptions\InvalidAttributeException;
use App\Exceptions\InvalidStringException;
use App\Exceptions\InvalidVariableNameException;
use App\Exceptions\ModelValidationException;
use App\Exceptions\NoUserVariableRelationshipsToAggregateException;
use App\Exceptions\StupidVariableNameException;
use App\Fields\Text;
use App\Files\FileHelper;
use App\Logging\QMLog;
use App\Models\UserVariableRelationship;
use App\Models\Measurement;
use App\Models\UserVariable;
use App\Models\Variable;
use App\Properties\Base\BaseNameProperty;
use App\Properties\Base\BaseUnitIdProperty;
use App\Properties\Unit\UnitNameProperty;
use App\Slim\Model\QMUnit;
use App\Slim\View\Request\QMRequest;
use App\Storage\DB\Writable;
use App\Storage\Memory;
use App\Traits\PropertyTraits\VariableProperty;
use App\Types\ObjectHelper;
use App\Types\QMArr;
use App\Types\QMStr;
use App\UnitCategories\CurrencyUnitCategory;
use App\UnitCategories\MiscellanyUnitCategory;
use App\Units\CountUnit;
use App\Units\DollarsUnit;
use App\Units\FeetUnit;
use App\Units\IndexUnit;
use App\Utils\APIHelper;
use App\VariableCategories\PaymentsVariableCategory;
use App\VariableCategories\SymptomsVariableCategory;
use App\Variables\QMCommonVariable;
use App\Variables\QMUserVariable;
use App\Variables\QMVariable;
use App\Variables\QMVariableCategory;
use App\Variables\VariableSearchResult;
use Exception;
use Illuminate\Database\Eloquent\Builder;

class VariableNameProperty extends BaseNameProperty
{
    use VariableProperty;
    public const UNIQUE_TEST_VARIABLE = "Unique Test Variable";
    public const AAA_TEST_VARIABLE = "Aaa Test "; // TODO: Change tests to use Unique Test Variable
    public const MINIMUM_VARIABLE_NAME_LENGTH_REQUIRED_TO_APPLY_FORMATTING = 20;
    public const SPENDING_ON_VARIABLE_DISPLAY_NAME_PREFIX = "Spending on ";
    public const PAYMENT_VARIABLE_NAME_SUFFIX = " (".PaymentsVariableCategory::NAME.")";
    public const PURCHASES_OF_VARIABLE_DISPLAY_NAME_PREFIX = "Purchases of ";
    public const TIME_SPENT_PREFIX = RescueTimeConnector::TIME_SPENT_ON;
    public const SP_CYCLICALLY_ADJUSTED_PRICE_EARNINGS_RATIO_OR_CAPE = "S&P Cyclically Adjusted Price Earnings Ratio or CAPE";
    public $table = Variable::TABLE;
    public $parentClass = Variable::class;
    protected $shouldNotContain = self::STUPID_VARIABLE_NAMES_LIKE;
    protected $requiredStrings = [];
    public const TEST_VARIABLE_NAMES_LIKE = [
        self::UNIQUE_TEST_VARIABLE,
        self::AAA_TEST_VARIABLE,
    ];
    public const PRIVATE_NAMES_LIKE = [
        "Phone Call "
    ];
    public const SYNONYMS = [
        'variableName',
        'name',
        'variable',
    ];
    public const STUPID_VARIABLE_NAMES_LIKE = [
        // Need for time ':',
        '�',
        '•',
        'Ships From',
        '_', // We replace spaces with this for url slugs
        'Sugar (count)', // Use Consumed Sugar
        'Headache (count)', // Use Number of Headaches
        'Time (Rating)',
        'Neutral Time',
        'Neutral Productivity',
        'Purchases of Uncategorized Spending',
        'Uncategorized',
        'General Utilities',
        'accessToken',
        'access_token',
        'secret=',
        'token=',
        'FREE SHIPPING',
        'Total Purchases',
        ' 7(',
        '(PE702 - 7)',
        'Daily Average Grade For ',
        ' Assignment Grade',
        'Walking (Rating)',
        'Skin Temperature (Weight)'
    ];
    public static $globalStupidVariableNames = [
        'Water (count)', // Use Glasses of Water
        'Amazon Purchase',
        'ESTIMATED TOTAL',
        'Purchases of Amazon',
        'Purchases of PayPal',
        'Purchases of Welcome To The Amazon Prime Rewards Visa Signature Card',
        'QUANTITY 1',
        'Resting Heart Rate (count)',
        'Steps (bpm)',
        'Spending On Hd Hi?=',
        'Spending On Chrom?=',
        'Spending On ESTIMATED TOTAL',
        'Spending On Rugby?=',
        'Payment',
        'Purchases from Purchases of',
        'Elevation',
        'Uv Index (index)',
        'Fresh Air (by Weight)',
        'Current Average Grade For Activity',
        'Cloud Cover Amount ',
        '[LARGER PREMIUM 5 SET]',
        'Fruit', // Use Servings of Fruit,
        'Neck Exercises', // Use Did Neck Exercises
    ];
    public static function renamePaymentsToPurchasesOf(){
        $rows =
            QMCommonVariable::readonly()->whereLike(Variable::FIELD_NAME,
                '%'.self::PAYMENT_VARIABLE_NAME_SUFFIX. '%"')
                ->getArray();
        if($rows){
            foreach($rows as $row){
                $newName = self::toSpending($row->name);
                QMLog::info("Renaming $row->name to $newName");
                try {
                    QMCommonVariable::writable()
                        ->where(Variable::FIELD_ID, $row->id)
                        ->update([Variable::FIELD_NAME => $newName]);
                } catch (Exception $exception) {
                    ExceptionHandler::logExceptionOrThrowIfLocalOrPHPUnitTest($exception);
                }
            }
        }
        $rows =
            QMCommonVariable::readonly()
                ->whereRaw(Variable::FIELD_NAME, '%- Total Spent%')
                ->getArray();
        foreach($rows as $row){
            $newName = self::toSpending($row->name);
            QMLog::info("Renaming $row->name to $newName");
            try {
                QMCommonVariable::writable()
                    ->where(Variable::FIELD_ID, $row->id)
                    ->update([Variable::FIELD_NAME => $newName]);
            } catch (Exception $exception) {
                ExceptionHandler::logExceptionOrThrowIfLocalOrPHPUnitTest($exception);
            }
        }
    }
    public static function deleteStupidBoringVariables(){
        $variables = QMVariable::getStupidVariables();
        foreach($variables as $variable){
            $variable->deleteCommonVariableAndAllAssociatedRecords("$variable->name is a stupid variable",
                true,
                true);
        }
        $after = QMVariable::getStupidVariables(true);
    }
    public static function renameTotalPurchasesToPurchasesFrom(){
        $qb =
            QMCommonVariable::readonly()
                ->whereRaw(Variable::FIELD_NAME." " . \App\Storage\DB\ReadonlyDB::like() . " '% Total Purchases%'")
                ->where(Variable::FIELD_DEFAULT_UNIT_ID, "<>", DollarsUnit::ID);
        /** @var QMCommonVariable[] $rows */
        $rows = $qb->getDBModels();
        foreach($rows as $row){
            $newName = str_replace('- Total Purchases', '', $row->name);
            $newName = "Purchases from ".$newName;
            QMLog::infoWithoutContext("$row->name to $newName");
            $result = Variable::whereId($row->id)->update([
                    Variable::FIELD_NAME               => $newName,
                    Variable::FIELD_ONSET_DELAY        => null,
                    Variable::FIELD_DURATION_OF_ACTION => null,
                ]);
            //$this->assertTrue($result);
        }
        $after = $qb->count();
		if($after !== 0){le('$after !== 0, $after');}
    }
    public static function renamePurchasesToSpending(){
        $qb =
            QMCommonVariable::readonly()
                ->whereRaw(Variable::FIELD_NAME." " . \App\Storage\DB\ReadonlyDB::like() . " '%Purchases of %'")
                ->where(Variable::FIELD_DEFAULT_UNIT_ID, DollarsUnit::ID);
        /** @var QMCommonVariable[] $rows */
        $rows = $qb->getDBModels();
        foreach($rows as $row){
            $newName = str_replace(self::PURCHASES_OF_VARIABLE_DISPLAY_NAME_PREFIX,
                self::SPENDING_ON_VARIABLE_DISPLAY_NAME_PREFIX,
                $row->name);
            QMLog::infoWithoutContext("$row->name to $newName");
            $result = Variable::whereId($row->id)->update([
                    Variable::FIELD_NAME               => $newName,
                    Variable::FIELD_ONSET_DELAY        => null,
                    Variable::FIELD_DURATION_OF_ACTION => null,
                ]);
            //$this->assertTrue($result);
        }
        $after = $qb->count();
		if(0 !== $after){le('0 !== $after');}
    }
    /**
     * @throws InvalidVariableNameException
     */
    public static function fixSpending(){
        $qb = QMCommonVariable::qb();
        $qb->whereNull(Variable::FIELD_DELETED_AT);
        $qb->whereRaw(Variable::TABLE.'.'.Variable::FIELD_NAME." " . \App\Storage\DB\ReadonlyDB::like() . " '% Spending'");
        $qb->where(Variable::TABLE.'.'.Variable::FIELD_DEFAULT_UNIT_ID, CountUnit::ID);
        $qb->whereRaw(Variable::TABLE.'.'.Variable::FIELD_NAME." " . \App\Storage\DB\ReadonlyDB::like() . " 'Purchases of%'");
        $rows = $qb->getArray();
        $total = count($rows);
        $i = 0;
        foreach($rows as $row){
            $v = QMCommonVariable::findByNameOrId($row->id);
            $i++;
            $newName = str_replace(" Spending", "", $row->name);
            QMLog::infoWithoutContext("$i of $total: $row->name to $newName (unit is $v->unitAbbreviatedName)");
            $v->rename($newName, "$row->name to $newName");
        }
        $after = $qb->getArray();
		if(0 != $after){le('0 !== $after');}
    }
    public static function fixTooShort(){
        $variables = Variable::query()->havingRaw("length(name) < 3")->get();
        foreach($variables as $v){
            $v->logInfo($v->variable_category->name." ");
            $v->name = $v->name." ".$v->variable_category->name;
            try {
                $v->save();
            } catch (ModelValidationException $e) {
                le($e);
            }
        }
    }
    public static function updateManualTrackingEntities()
    {
        $variables = QMCommonVariable::getCommonVariables([
            'numberOfUserVariables' => '(gt)1',
            'manualTracking' => true
        ]);
        $body = ['name' => 'variable-name'];
        foreach ($variables as $variable) {
            $synonyms = $variable->getSynonymsAttribute();
            $body['entries'][] = [
                'value' => $variable->name,
                'synonyms' => $synonyms
            ];
        }
        FileHelper::writeJsonFile('/vagrant/log', $body, 'variable-name-entities');
        APIHelper::makePostRequest('https://api.dialogflow.com/v1/entities?v=20150910', $body,
	        '9a5901a820574462a4379060e961625f');
    }
    public static function updateSymptomEntities(){
        $variables = QMCommonVariable::getCommonVariables([
            'variableCategoryName'  => SymptomsVariableCategory::NAME,
            'numberOfUserVariables' => '(gt)0'
        ]);
        $body = ['name' => 'symptom-variable-name'];
        $entries = [];
        foreach($variables as $variable){
            if(strpos($variable->name, '>') !== false){
                continue;
            }
            if(strpos($variable->name, '/') !== false){
                continue;
            }
            $synonyms = $variable->getSynonymsAttribute();
            $entries[] = [
                'value'    => $variable->name,
                'synonyms' => $synonyms
            ];
        }
        $body['entries'] = $entries;
        FileHelper::writeJsonFile('/vagrant/log', $entries, 'symptom-variable-name-entities');
        APIHelper::makePostRequest('https://api.dialogflow.com/v1/entities?v=20150910', $body,
	        '9a5901a820574462a4379060e961625f');
    }
    /**
     * @param string $name
     * @param QMUnit|int $unit
     * @param bool $fast
     * @param QMVariableCategory|int|null $variableCategory
     * @return string|string[]
     * @return string|string[]
     */
    public static function addSuffix(string $name, QMUnit $unit, bool $fast = true, $variableCategory = null){
        $cacheKey = $name."-". $unit->abbreviatedName;
        if($cached = Memory::get($cacheKey, __FUNCTION__)){return $cached;} // This prevents repeating the process a million times when getting all user variables for common variable analysis
	    $name = self::removeUnitInParenthesis($name, $unit, $fast);
        if($unit->getSuffix()){
            $name .= " ".ucfirst($unit->getSuffix());
        }else if($variableCategory){
            if(is_int($variableCategory)){
                $variableCategory = QMVariableCategory::find($variableCategory);
            }
            if($suffix = $variableCategory->getSuffix()){
                if(stripos($name, $suffix) === false){
                    $name .= " ".ucfirst($suffix);
                }
            }
        }
        Memory::set($cacheKey, $name, __FUNCTION__);
        return $name;
    }
    /**
     * @param string $name
     * @param QMUnit $unit
     * @param bool $fast
     * @return string
     */
    public static function removeSuffix(string $name, QMUnit $unit, bool $fast = true): string{
        // This causes more problems than its worth
//        if($name === BloodPressureDiastolicBottomNumberCommonVariable::NAME ||
//            $name === BloodPressureSystolicTopNumberCommonVariable::NAME){
//            return "Blood Pressure";
//        }
        return self::removeUnitInParenthesis($name, $unit, $fast);
    }
    /**
     * @param int $id
     * @return string|null
     */
    public static function fromId($id): ?string{
        if($name = self::fromIdFromMemory($id)){
            return $name;
        }
        $v = QMCommonVariable::findByNameIdOrSynonym($id);
        if(!$v){
            return null;
        }
        return $v->getVariableName();
    }
    /**
     * @param int $id
     * @return string|null
     */
    public static function fromIdFromMemory(int $id): ?string{
        if($v = Variable::findInMemory($id)){
            return $v->getNameAttribute();
        }
        if($v = VariableSearchResult::getVariableConstantsById($id)){
            return $v->getVariableName();
        }
        if($v = QMUserVariable::findInMemoryForAnyUser($id)){
            return $v->getVariableName();
        }
        $v = QMCommonVariable::findInMemoryByNameOrId($id);
        if(!$v){
            return null;
        }
        return $v->getVariableName();
    }
    /**
     * @param string $variableName
     * @param QMUnit $unit
     * @return string
     */
    public static function withUnit(string $variableName, QMUnit $unit): string{
        $variableNameWithoutUnit =
        $originalVariableName =
        $variableName = self::sanitizeSlow($variableName); // Have to shorten here or it might be too long
        $openParenthesisPosition = strpos($variableName, ' (');
        if($openParenthesisPosition > 2){
            $variableNameWithoutUnit = substr($originalVariableName, 0, $openParenthesisPosition);
        }
        $unitNameOrCategory = $unit->categoryName;
        if($unit->categoryName === MiscellanyUnitCategory::NAME){
            $unitNameOrCategory = $unit->abbreviatedName;
        }
        if($unit->categoryName === CurrencyUnitCategory::NAME){
            $unitNameOrCategory = PaymentsVariableCategory::NAME;
        }
        $newVariableName = $variableNameWithoutUnit.' ('.$unitNameOrCategory.')';
        return $newVariableName;
    }
    /**
     * @param string $originalName
     * @return string
     */
    public static function toPurchases(string $originalName): string{
        $newName = self::stripSpendingPurchasePayments($originalName);
        $newName = str_replace("Spent", "Purchases", $newName);
        $newName = VariableNameProperty::PURCHASES_OF_VARIABLE_DISPLAY_NAME_PREFIX.$newName;
        return $newName;
    }
    /**
     * @param string $originalName
     * @return bool
     */
    public static function isSpending(string $originalName): bool{
        $result = stripos($originalName, VariableNameProperty::SPENDING_ON_VARIABLE_DISPLAY_NAME_PREFIX) !== false;
        return $result;
    }
    /**
     * @param string $originalName
     * @return string
     */
    public static function toSpending(string $originalName): string{
        if(stripos($originalName, 'Allowance') !== false){
            return $originalName;
        }
        if(stripos($originalName, 'Monetary') !== false){
            return $originalName;
        }
        $newName = self::stripSpendingPurchasePayments($originalName);
        $newName = VariableNameProperty::SPENDING_ON_VARIABLE_DISPLAY_NAME_PREFIX.$newName;
        return $newName;
    }
    /**
     * @param string $originalName
     * @return mixed|string
     */
    public static function stripSpendingPurchasePayments(string $originalName): string{
        $newName = str_ireplace(VariableNameProperty::SPENDING_ON_VARIABLE_DISPLAY_NAME_PREFIX, '', $originalName);
        $newName = str_ireplace(VariableNameProperty::PURCHASES_OF_VARIABLE_DISPLAY_NAME_PREFIX, '', $newName);
        $newName = str_ireplace(VariableNameProperty::PAYMENT_VARIABLE_NAME_SUFFIX, '', $newName);
        $newName = str_ireplace(" - Total Spent", '', $newName);
        $newName = trim($newName);
        return $newName;
    }
    /**
     * @param $string
     * @return string
     */
    public static function removeExcludedWords($string): string{
        if(strlen($string) < VariableNameProperty::MINIMUM_VARIABLE_NAME_LENGTH_REQUIRED_TO_APPLY_FORMATTING){
            return $string;
        }
        $excludedWords = [
            'Approximately',
            //'Kills', //Freshburst Listerine Antiseptic Mouthwash Kills Germs Causing Bad Breath
            //'Fresh',  // 'Fresh Express Mature Leaf Spinach' becomes 'Express Mature Leaf Spinach'
            '#1'
        ];
        $afterExclusion = $string;
        foreach($excludedWords as $excludedWord){
            $afterExclusion = str_replace([
                ' '.$excludedWord,
                $excludedWord.' '
                                          ],
                                          '',
                                          $afterExclusion);
            $lowerCase = strtolower($excludedWord);
            $afterExclusion = str_replace([
                ' '.$lowerCase,
                $lowerCase.' '
                                          ],
                                          '',
                                          $afterExclusion);
        }
        if(!empty($afterExclusion) && strlen($afterExclusion) > 5){
            $string = trim($afterExclusion);
        }
        return $string;
    }
	/**
	 * @param string $originalString
	 * @return string
	 */
    public static function removeStringFollowingTerminatingString(string $originalString): string{
        if(strlen($originalString) < VariableNameProperty::MINIMUM_VARIABLE_NAME_LENGTH_REQUIRED_TO_APPLY_FORMATTING){
            return $originalString;
        }
        // Put least definitive removals first because they're most likely to be over-ruled if near the beginning
        $terminatingStrings = [
            //' (',  TODO: Why do we want to remove parenthesis?
            '(Pack of ',
            'Pack of ',
            ' With ',
            'with FREE',
            ' - ',
            ' Kills',
            //Freshburst Listerine Antiseptic Mouthwash Kills Germs Causing Bad Breath
            ', ',
            // DenTek Extra Strong Triple Clean Floss Picks, Mouthwash Blast 90 Ea (Pack Of 6)
            ' Up To ',
            'Rechargeable',
            'Waterproof',
            ' GMT+',
            ' 201',
            '-201',
            ' -- ',
            ' Season',
            ' Ep.',
            ' | ',
            ' Ea.',
            ' Ea ',
            'Highest Quality',
            // "Glycerin Vegetable Kosher USP-Highest Quality Available-1 Quart"
            'Single-',
            ' Sets',
            ' To Support ',
            '-Pack',
            'Case Of',
            '(Case Of',
            ' Box',
            ' Guaranteed'
        ];
        $afterExclusion = $originalString;
        foreach($terminatingStrings as $terminatingString){
            $afterExclusion = QMStr::before($terminatingString, $afterExclusion, $afterExclusion);
            $afterExclusion = QMStr::before(strtolower($terminatingString), $afterExclusion,
                $afterExclusion);
            if(empty($afterExclusion) ||
                strlen($afterExclusion) <
                VariableNameProperty::MINIMUM_VARIABLE_NAME_LENGTH_REQUIRED_TO_APPLY_FORMATTING){
                $afterExclusion = $originalString;
            }
        }
        if(QMStr::containsUnterminatedParenthesis($afterExclusion)){
            return $originalString; // Blood Pressure (Diastolic - Bottom Number)
        }
        return $afterExclusion;
    }
    /**
     * @param string $originalString
     * @param string|null $unitName
     * @return string
     */
    public static function removeValueAndUnit(string $originalString, string $unitName = null): string{
        if(!QMStr::getNumbersFromString($originalString)){
            return $originalString;
        }
        if(!$unitName){
            $unitName = UnitNameProperty::fromString($originalString);
        }
        if(!$unitName){
            return $originalString;
        }
        $shortened = QMStr::removeSubstringAndPrecedingNumber($originalString, $unitName);
        $shortened = trim($shortened);
        if(strlen($shortened) < 27){
			// Cuts off Gummies if shorter for Sundown Naturals Melatonin 5 mg Gummies (Pack of 60), Strawberry Flavored, Supports Sound, Quality Sleep*, Gluten Free, Dairy Free, Non-GMO, No Artificial Flavors
            $shortened = QMStr::removeSubstringAndPrecedingNumber($originalString, $unitName, false);
        }
        return $shortened;
    }
    /**
     * @return int|string
     */
    public static function nameIdOrSearchPhraseFromRequest(){
        $name = VariableNameProperty::fromRequestDirectly(false);
        if($name){
            return $name;
        }
        $search = QMRequest::getSearchPhrase();
        if($search){
            return $search;
        }
        $id = VariableIdProperty::fromRequestDirectly(false);
        return $id;
    }
    /**
     * @param string $replacementVariableName
     * @param string $variableNameToDelete
     * @param bool $requireConfirmation
     * @return bool
     */
    public static function replaceVariableByName(string $replacementVariableName,
                                                 string $variableNameToDelete,
                                                 bool $requireConfirmation = false): bool{
        if($replacementVariableName === $variableNameToDelete){
            le("$replacementVariableName === $variableNameToDelete");
        }
        $db = Writable::db();
        $replacementVariableRow = Variable::whereName($replacementVariableName)->first();
        if(!$replacementVariableRow){
            QMLog::info("Could not find $replacementVariableName variable");
            return false;
        }
        $numberOfMeasurements =
            $db->table(Measurement::TABLE)
                ->where(Measurement::FIELD_VARIABLE_ID, $replacementVariableRow->id)
                ->count();
        $numberOfCorrelations =
            $db->table(UserVariableRelationship::TABLE)
                ->where(UserVariableRelationship::FIELD_CAUSE_VARIABLE_ID, $replacementVariableRow->id)
                ->count();
        $message = "$replacementVariableRow->name has $replacementVariableRow->number_of_user_variables users and
            $numberOfMeasurements measurements and $numberOfCorrelations user_variable_relationships as cause.";
        QMLog::info($message);
        $variableToDeleteRow = Variable::whereName($variableNameToDelete)->first();
        if(!$variableToDeleteRow){
            QMLog::info("Could not find $variableToDeleteRow variable");
            return false;
        }
        $numberOfMeasurements =
            $db->table(Measurement::TABLE)
                ->where(Measurement::FIELD_VARIABLE_ID, $variableToDeleteRow->id)
                ->count();
        $numberOfCorrelations =
            $db->table(UserVariableRelationship::TABLE)
                ->where(UserVariableRelationship::FIELD_CAUSE_VARIABLE_ID, $variableToDeleteRow->id)
                ->count();
        $message = "$variableToDeleteRow->name has $variableToDeleteRow->number_of_user_variables users and
            $numberOfMeasurements measurements and $numberOfCorrelations user_variable_relationships as cause.";
        QMLog::info($message);
        if($requireConfirmation){
            QMLog::info("Are you sure you want to replace it? [y/N]");
            $handle = fopen('php://stdin', 'rb');
            $line = fgets($handle);
            if(trim($line) !== 'y'){
                QMLog::info("Skipping $variableToDeleteRow->name variable!");
                return false;
            }
            fclose($handle);
        }
        QMLog::info("Replacing and deleting $variableToDeleteRow->name variable and all related records...");
        $success = VariableIdProperty::replaceEverywhere($replacementVariableRow->id,
            $variableToDeleteRow->id,
            "replacing with $replacementVariableName");
        if(!$success){
            return false;
        }
        $numberOfMeasurements =
            $db->table(Measurement::TABLE)
                ->where(Measurement::FIELD_VARIABLE_ID, $replacementVariableRow->id)
                ->count();
        $numberOfCorrelations =
            $db->table(UserVariableRelationship::TABLE)
                ->where(UserVariableRelationship::FIELD_CAUSE_VARIABLE_ID, $replacementVariableRow->id)
                ->count();
        $message =
            "Replaced and deleted $variableToDeleteRow->name variable and all related records!  Now $replacementVariableName has $numberOfMeasurements measurements and $numberOfCorrelations user_variable_relationships as cause. ";
        QMLog::info($message);
        return true;
    }
    /**
     * @param $variableNameOrId
     * @param array $newVariableData
     * @return QMCommonVariable|null
     */
    public static function getVariableByFormattedNameIfDifferentFromProvided($variableNameOrId,
                                                                             array $newVariableData): ?QMCommonVariable{
	    $unit = $v = null;
		if($newVariableData){$unit = BaseUnitIdProperty::pluckParentDBModel($newVariableData);}
        $formattedName = VariableNameProperty::sanitizeSlow($variableNameOrId, $unit);
        if($formattedName !== $variableNameOrId){
            $v = QMCommonVariable::findByName($formattedName);
        }
        return $v;
    }
	/**
	 * @param array $arr
	 * @return array
	 */
    public static function validateNewSpendingVariable(array $arr): array{
        if(strpos($arr['name'], VariableNameProperty::SPENDING_ON_VARIABLE_DISPLAY_NAME_PREFIX) !== false){
            if($arr[Variable::FIELD_DEFAULT_UNIT_ID] !== QMUnit::getDollars()->id){
                le("Payments in name but unit is ".
                    QMUnit::getByNameOrId($arr[Variable::FIELD_DEFAULT_UNIT_ID])->getAbbreviatedName().
                    " for ".
                    $arr['name']);
            }
            if($arr[Variable::FIELD_VARIABLE_CATEGORY_ID] === QMVariableCategory::getEmotions()->id){
                le("Emotions should not have payment in name!");
            }
            if($arr[Variable::FIELD_VARIABLE_CATEGORY_ID] === QMVariableCategory::getSymptoms()->id){
                le("Symptoms should not have payment in name!");
            }
            foreach(QMCommonVariable::$purchasesAndSpendingConstants as $key => $value){
                if(!isset($arr[$key])){
                    $arr[$key] = $value;
                }
            }
        }
        return $arr;
    }
	/**
	 * @param array $newVariable
	 * @return array
	 */
    public static function validateNewPurchasesVariable(array $newVariable): array{
        if(strpos($newVariable['name'], VariableNameProperty::PURCHASES_OF_VARIABLE_DISPLAY_NAME_PREFIX) !== false){
            if($newVariable[Variable::FIELD_DEFAULT_UNIT_ID] !== CountUnit::ID){
                le("Purchases in name but unit is ".
                    QMUnit::getByNameOrId($newVariable[Variable::FIELD_DEFAULT_UNIT_ID])->getAbbreviatedName().
                    " for ".
                    $newVariable['name']);
            }
            if($newVariable[Variable::FIELD_VARIABLE_CATEGORY_ID] === QMVariableCategory::getEmotions()->id){
                le("Emotions should not have purchases in name!");
            }
            if($newVariable[Variable::FIELD_VARIABLE_CATEGORY_ID] === QMVariableCategory::getSymptoms()->id){
                le("Symptoms should not have purchases in name!");
            }
        }
        return $newVariable;
    }
    /**
     * @param string $variableNameToDelete
     * @param string $replacementVariableName
     * @throws ModelValidationException
     * @throws StupidVariableNameException
     * @throws \App\Exceptions\AlreadyAnalyzedException
     * @throws \App\Exceptions\AlreadyAnalyzingException
     * @throws \App\Exceptions\DuplicateFailedAnalysisException
     * @throws \App\Exceptions\NotEnoughDataException
     * @throws \App\Exceptions\TooSlowToAnalyzeException
     */
    public static function replaceVariableNameAndUpdate(string $variableNameToDelete, string $replacementVariableName){
        $keep = Variable::findByName($replacementVariableName);
        $delete = Variable::findByName($variableNameToDelete);
        if(!$keep || strtolower($variableNameToDelete) === strtolower($replacementVariableName)){ // We changed case
            $delete->rename($replacementVariableName);
            return;
        }
        if($keep->default_unit_id !== $delete->default_unit_id){
            le("Please convert ".
                $delete->name.
                " unit from ".
                $delete->defaultUnit->name.
                " to ".
                $keep->defaultUnit->name.
                " with changeDefaultUnitEverywhere before merging!");
        }
        $keep->addSynonymsAndSave($delete->synonyms);
        $keep->addSynonymsAndSave($delete->name);
        $relations = QMCommonVariable::getRelatedDbFields();
        $oldId = $delete->id;
        $newId = $keep->id;
        foreach($relations as $relation){
            $table = $relation['table'];
            $field = $relation['field'];
            Writable::statementStatic("update ignore $table
                set $field = $newId
                where $field = $oldId;");
        }
        $userVariables = UserVariable::whereVariableId($oldId)->get();
        foreach($userVariables as $uv){
            Measurement::whereUserVariableId($uv->id)->forceDelete();
        }
        foreach($relations as $relation){
            $table = $relation['table'];
            $field = $relation['field'];
            Writable::statementStatic("delete $table from $table
                where $field = $oldId;");
        }
        try {
            $message = "This variable was replaced with $keep->name.  Do not delete to avoid re-creation";
            $delete->update([
                Variable::FIELD_PARENT_ID              => $keep->id,
                Variable::FIELD_DELETED_AT             => now_at(),
                Variable::FIELD_INTERNAL_ERROR_MESSAGE => $message,
                Variable::FIELD_USER_ERROR_MESSAGE     => $message,
            ]);
        } catch (Exception $e) {
            le($e);
        }
        Memory::flush();
        try {
            QMGlobalVariableRelationship::analyzeAggregatedCorrelationsForVariable($newId);
        } catch (NoUserVariableRelationshipsToAggregateException $e) {
            le($e);
        }
        $v = QMCommonVariable::find($newId);
        $v->analyzeFullyAndSave(__FUNCTION__);
    }
    public static function detailLinkField(): Text{
        $p = new static();
        return Text::make($p->getTitleAttribute(), $p->name, function ($value, $resource, $attribute) {
            /** @var UserVariable $resource */
            return $resource->getVariableName();
        })->sortable()
            ->readonly()
            ->detailLink()
            ->rules('required');
    }
    /**
     * @param string $name
     * @param QMUnit|null $unit
     * @param bool $fast
     * @return string
     */
    private static function removeUnitInParenthesis(string $name, ?QMUnit $unit, bool $fast): string{
        $original = $name;
        $name = str_replace([
            " (".$unit->abbreviatedName.")",
            " (".$unit->name.")",
            " (".strtolower($unit->name).")",
            " (".$unit->categoryName.")"
                            ],
                            "",
                            $name);
        if($fast){
            $name = self::sanitizeFast($name, $unit);
        }else{
            $name = self::sanitizeSlow($name, $unit);
        }
        if(empty($name)){le("Could not create display name for $original");}
        return $name;
    }
    /**
     * @return Builder|Variable
     */
    public static function whereTestVariable(): Builder{
        //return Variable::whereNameLike("Unique Test V");
        $qb = Variable::query()->where(function ($query) {
            foreach(self::TEST_VARIABLE_NAMES_LIKE as $str){
                $query->orWhere(Variable::FIELD_NAME, "LIKE", '%'.$str.'%');
            }
        });
        return $qb;
    }
    /**
     * @param string $str
     * @return Builder|Variable
     */
    public static function whereLike(string $str): Builder{
        $qb = Variable::query()
            ->where(Variable::FIELD_NAME, "LIKE", '%'.$str.'%');
        return $qb;
    }
    /**
     * @param Builder|Variable $qb
     */
    public static function whereNotTestVariable(Builder $qb){
        foreach(self::TEST_VARIABLE_NAMES_LIKE as $str){
            $qb->where(Variable::FIELD_NAME, "NOT LIKE", '%'.$str.'%');
        }
    }
    public static function isTest(string $name): bool{
        foreach(self::TEST_VARIABLE_NAMES_LIKE as $str){
            if(stripos($name, $str) !== false){
                return true;
            }
        }
        return false;
    }
    /**
     * @param string $haystack
     * @throws InvalidStringException
     */
    public static function assertDoesNotContainPrivateNames(string $haystack): void {
        foreach(self::PRIVATE_NAMES_LIKE as $private){
            if(stripos($haystack, $private) !== false){
                throw new InvalidStringException(__FUNCTION__, $private, $haystack);
            }
        }
    }
    /**
     * @param string $haystack
     * @throws InvalidStringException
     */
    public static function assertDoesNotContainStupidNames(string $haystack): void {
        foreach(self::STUPID_VARIABLE_NAMES_LIKE as $private){
            if(stripos($haystack, $private) !== false){
                throw new InvalidStringException(__FUNCTION__, $private, $haystack);
            }
        }
    }
    /**
     * @param string $name
     * @param QMUnit|null $unit
     * @param $variable
     * @throws InvalidVariableNameException
     */
    public static function validateScore(string $name, ?QMUnit $unit, $variable): void{
        if(stripos($name, 'score') !== false){
            if($unit->isDurationCategory()){
                throw new InvalidVariableNameException($name,
                    "$name with word score should NOT have duration unit!",
                    $variable);
            }
        }
    }
    /**
     * @param string $name
     * @param $variable
     * @throws InvalidVariableNameException
     */
    public static function validateSpending(string $name, $variable): void{
        if(stripos($name, " Spending") !== false &&
            stripos($name, self::SPENDING_ON_VARIABLE_DISPLAY_NAME_PREFIX) === 0){
            throw new InvalidVariableNameException($name,
                "Variable names should not start and end with Spending. ",
                $variable);
        }
    }
    /**
     * @return array
     */
    public static function getStupid(): array{
        $names = self::$globalStupidVariableNames;
        $names = array_merge($names, RescueTimeConnector::$stupidVariableNames);
        return $names;
    }
    protected function getShouldNotContain(): array{
        return $this->shouldNotContain;
    }
	protected function getShouldNotEqual(): array{
		return self::getStupid();
	}
    /**
     * @param string $name
     * @return bool
     */
    public static function isStupid(string $name): bool{
        $stupid = QMArr::inArrayCaseInsensitive($name, self::getStupid());
        if($stupid){
            return true;
        }
        foreach(VariableNameProperty::STUPID_VARIABLE_NAMES_LIKE as $stupidName){
            if(stripos($name, $stupidName) !== false){
                return true;
            }
        }
        if(stripos($name, "purchases of") !== false &&
            stripos($name, "spending") !== false){
            return true;
        }
        return false;
    }
    /**
     * @param string $name
     * @param QMUnit|null $unit
     * @param QMVariable|Variable|UserVariable|null $variable
     * @return string
     * @throws InvalidVariableNameException
     * @throws StupidVariableNameException
     */
    public static function validateNew(string $name, ?QMUnit $unit, $variable = null): string{
        $name = trim($name);
        if(mb_strlen($name) > self::MAX_LENGTH){$name = mb_substr($name, 0, 121).'...';}
        if($unit){$unit->validateVariableNameForUnit($name);}
        if($name === ''){
            throw new InvalidVariableNameException($name,
                'Variable name must have at least 1 character. ', $variable);
        }
        if(self::isStupid($name)){
            throw new StupidVariableNameException($name, "It's a blacklisted stupid variable name. ",
                $variable);
        }
        self::validateSpending($name, $variable);
        self::validateScore($name, $unit, $variable);
        return $name;
    }
    /**
     * @throws StupidVariableNameException
     * @throws InvalidAttributeException
     */
    public function validate(): void {
        if(!$this->shouldValidate()){return;}
        parent::validate();
        $v = $this->getVariable();
        $name = $this->getDBValue();
        try {
            QMStr::assertNotJson($name, Variable::FIELD_NAME);
        } catch (InvalidStringException $e) {
            $this->throwException(__METHOD__.": ".$e->getMessage());
        }
        $unit = ($v->default_unit_id) ? $v->getQMUnit() : null; // Sometimes name is set before unit id during fill
        try {
            self::validateNew($name, $unit, $this);
        } catch (InvalidVariableNameException $e) {
            $this->throwException(__METHOD__.": ".$e->getMessage());
        }
    }
    /**
     * @param Variable|VariableSearchResult|QMUserVariable $variable
     * @return mixed|string
     */
    public static function variableToDisplayName($variable): string {
        if($name = ObjectHelper::get($variable,
            ['alias', 'commonAlias', 'displayName'])){
            return $name;
        }
        $name = $variable->getNameAttribute();
        $unit = $variable->getUserOrCommonUnit();
        $cacheKey = $name."-".$unit->abbreviatedName;
        if($cached = Memory::get($cacheKey, __FUNCTION__)){return $cached;}
        // This prevents repeating the process a million times when getting all user variables for common variable analysis
        $displayName = VariableNameProperty::removeSuffix($name, $unit);
        if(empty($displayName)){le("Could not create display name for $name");}
        $displayName = QMStr::titleCaseSlow($displayName);
        Memory::set($cacheKey, $displayName, __FUNCTION__);
        return $displayName;
    }
    /**
     * @param string $s
     * @param QMUnit|null $unit
     * @return string
     */
    public static function sanitizeFast(string $s, QMUnit $unit = null): string{
        $cacheKey = $s;
        if($unit){$cacheKey .= "-unit-".$unit->id;}
        if($cached = Memory::get($cacheKey, __FUNCTION__)){return $cached;} // This prevents repeating the process a million times when getting all user variables for common variable analysis
        $s = str_replace(" - Total Spent", VariableNameProperty::PAYMENT_VARIABLE_NAME_SUFFIX, $s);
        if(strlen($s) < 2){le('Variable name must contain at least 2 characters. Name is: '.$s);}
        $s = self::removeParenthesisPrefix($s);
        $s = self::removeDisallowedCharacters($s);
        //$string = self::removeCommonWords($string, false); // Causes too many problems
        $s = QMStr::removeYYYMMDDFromString($s);
        $s = self::removeLastWordUntilUnderMaxLength($s);
        $s = self::removeDisallowedLastCharacters($s);
        $s = self::removeDisallowedPrefix($s);
        // Why is this necessary?  It removes the number from our test variables $s = self::removeNumericLastWords($s);
        $s = self::removeLastWordIfUnit($s);
        //$string = self::removeNumericLastCharacters($string);  // Let's not do this because it cuts Indoor CO2 off
        if($unit && $unit->name === DollarsUnit::NAME){
            $s = self::toSpending($s);
        }
        if($unit){
            try {
                $unit->validateVariableNameForUnit($s);
            } catch (InvalidVariableNameException $e) {
                le($e);
            }
        }
        $s = str_replace('()', '', $s);
        $variableName = QMStr::removeDoubleSpacesFromString($s);
        $s = trim($variableName);
        $s = QMStr::unescapeDoubleQuotes($s);
        Memory::set($cacheKey, $s, __FUNCTION__);
        return $s;
    }
    /**
     * @param string $s
     * @param QMUnit|null $unit
     * @return string
     */
    public static function sanitizeSlow(string $s, QMUnit $unit = null): string{
        $cacheKey = $s;
        if($unit){$cacheKey .= "-unit-".$unit->id;}
        if($cached = Memory::get($cacheKey, __FUNCTION__)){return $cached;} // This prevents repeating
        $s = str_replace(" - Total Spent", VariableNameProperty::PAYMENT_VARIABLE_NAME_SUFFIX, $s);
        if(strlen($s) < 2){
            le('Variable name must contain at least 2 characters. Name is: '.$s);
        }
        if(self::containsNumberWithPrecedingSpaceOrParenthesis($s)){
            $unitNameFromString = UnitNameProperty::fromString($s);
            if(!$unitNameFromString && QMStr::isAddress($s)){
                return $s;
            }
            if($unitNameFromString && !self::containsAllowedUnit($unitNameFromString)){ // Don't remove Feet from Swollen Feet
                $before = strlen($s);
                $s = self::removeValueAndUnit($s, $unitNameFromString);
                $after = strlen($s);
                if($after < $before){
                    $s = self::removeValueAndUnit($s);
                } // Twice for Sundown Nat Odor Garlic 100 Mg 250 Ea
            }
        }
        $s = self::removeParenthesisPrefix($s);
        $s = self::removeDisallowedCharacters($s);
        //$string = self::removeCommonWords($string, false); // Causes too many problems
        $s = QMStr::removeYYYMMDDFromString($s);
        $s = self::removeLastWordUntilUnderMaxLength($s);
        $s = self::removeExcludedWords($s);
        $s = self::removeStringFollowingTerminatingString($s);
        $s = self::removeDisallowedLastCharacters($s);
        $s = self::removeDisallowedPrefix($s);
        // Why is this necessary?  It removes the number from our test variables $s = self::removeNumericLastWords($s);
        $s = self::removeLastWordIfUnit($s);
        //$string = self::removeNumericLastCharacters($string);  // Let's not do this because it cuts Indoor CO2 off
        if($unit && $unit->name === DollarsUnit::NAME){
            $s = self::toSpending($s);
        }
        if($unit){
            try {
                $unit->validateVariableNameForUnit($s);
            } catch (InvalidVariableNameException $e) {
                le($e);
            }
        }
        if($unit){
            $s = str_replace("($unit->abbreviatedName)", "", $s);
        }
        $s = str_replace('()', '', $s);
        $variableName = QMStr::removeDoubleSpacesFromString($s);
        $s = trim($variableName);
        //$s = QMStr::titleCaseSlow($s);  // We don't want to incorrectly case this and lose the original so we'll
	    // just use display name for that purpose
        $s = QMStr::unescapeDoubleQuotes($s);
        Memory::set($cacheKey, $s, __FUNCTION__);
        return $s;
    }
	/**
	 * @param string $originalString
	 * @return bool|mixed
	 */
    private static function containsNumberWithPrecedingSpaceOrParenthesis(string $originalString): bool {
        $numbers = QMStr::getNumbersFromString($originalString);
        if(!$numbers){
            return false;
        }
        foreach($numbers as $number){
            if(stripos($originalString, " ".$number) !== false){
                return true;
            }
            if(stripos($originalString, "(".$number) !== false){
                return true;
            }
        }
        return false;
    }
	/**
	 * @param string $string
	 * @return string
	 * Why do we need this?
	 */
    public static function removeNumericLastWords(string $string): string{
        if(strlen($string) < VariableNameProperty::MINIMUM_VARIABLE_NAME_LENGTH_REQUIRED_TO_APPLY_FORMATTING){
            return $string;
        }
        $lastWord = QMStr::getLastWord($string);
        while(is_numeric($lastWord)){
            $string = QMStr::removeLastWord($string);
            $lastWord = QMStr::getLastWord($string);
        }
        return $string;
    }
    /**
     * @param $string
     * @return string
     */
    private static function removeLastWordUntilUnderMaxLength($string): string{
        $maxLength = self::MAX_LENGTH;
        $bufferForCategorySuffix = 12;  // i.e. (Payments)
        $maxLength -= $bufferForCategorySuffix;
        while(strlen($string) > $maxLength){
            $string = QMStr::removeLastWord($string);
        }
        return $string;
    }
    /**
     * @param $string
     * @return string
     */
    private static function removeDisallowedPrefix($string): string{
        $originalString = $string;
        $firstWord = QMStr::getFirstWordOfString($string);
        if(is_numeric($firstWord)){
            $string = str_replace($firstWord.' ', '', $string);
            $firstWord = QMStr::getFirstWordOfString($string);
            if($firstWord === "of"){
                $string = str_replace($firstWord.' ', '', $string);
                return $string;
            }
        }
        return $originalString;
    }
    /**
     * @param $string
     * @return string
     */
    public static function removeDisallowedLastCharacters($string): string{
        $lastCharacter = QMStr::getLastCharacter($string);
        $disallowedLastCharacters = [
            ',',
            '.',
            '(',
            '-'
        ];
        foreach($disallowedLastCharacters as $disallowedLastCharacter){
            while($disallowedLastCharacter === $lastCharacter){
                $string = QMStr::removeLastCharacter($string);
                $lastCharacter = QMStr::getLastCharacter($string);
            }
        }
        return $string;
    }
    /**
     * @param $string
     * @return string
     */
    private static function removeDisallowedCharacters($string): string{
        // This removes all foreign characters $string = QMStr::removeDiamondWithQuestionMark($string);
        //$string = str_replace("'", "", $string); // Don't remove from D'Addario EJ16 Phosphor Bronze Light Acoustic Guitar Strings
        return str_replace([
            '*',
            ':'
                           ], [
            '',
            ' '
                           ], $string);
    }
    /**
     * @param string $s
     * @return string
     */
    private static function removeLastWordIfUnit(string $s): string {
        $original = $s;
        if(self::containsAllowedUnit($s)){return $s;}
        $lastWord = QMStr::getLastWord($s);
        if(strlen($lastWord) === 1 && stripos($s, 'vitamin') !== false){
            return $s;
        }
        if(str_contains($lastWord, "(")){
            $withParenthesis = $lastWord;
            $withoutParenthesis = str_replace(")", "", $lastWord);
            $withoutParenthesis = str_replace("(", "", $withoutParenthesis);
            $unit = QMUnit::getByNameOrId($withoutParenthesis, false);
            if($unit){
                $s = str_replace($withParenthesis, "", $s);
            }
        } else {
            $unit = QMUnit::findByNameOrSynonym($lastWord, false);
            if($unit){
                $s = str_replace(" ".$lastWord, "", $s);
            }
        }
        if (strlen($s) < 5) {
            return $original;
        }
        return $s;
    }
    /**
     * @param string $str
     * @return bool
     */
    private static function containsAllowedUnit(string $str):bool{
        if(stripos($str, CountUnit::NAME) !== false){return true;} // Don't remove Count from Daily Step Count
        if(stripos($str, FeetUnit::NAME) !== false){return true;} // Don't remove Feet from Swollen Feet
        if(stripos($str, IndexUnit::NAME) !== false){return true;} // Don't remove Index from Pollen Index
        return false;
    }
	/**
	 * @param string $before
	 * @return string
	 */
    private static function removeParenthesisPrefix(string $before): string{
        if(strpos($before, '(') === 0){  // Handles `(4 oz) 2.5% Retinol Moisturizer Cream for Face with Hyaluronic Acid`
            $sanitized = substr($before, strpos($before, ')') + 1, strlen($before) - 1);
            if(empty($sanitized)){ // Handle (Bottle Size: 60ml, Nic Level: 12mg)
                $sanitized = str_replace([
                    '(',
                    ')'
                                         ], '', $before);
            }
            return $sanitized;
        }
        return $before;
    }
	/**
	 * @return void
	 */
	public static function fixInvalidRecords(){
        //return parent::fixInvalidRecords();
        $strings = (new static())->getShouldNotContain();
        foreach($strings as $string){
            $vars = Variable::whereNameLike($string)->get();
            foreach($vars as $var){
                QMLog::infoFast($var->name);
            }
        }
    }
    /**
     * @param $arr
     * @return string[]
     */
    public static function pluckNames($arr): array{
        return static::pluckColumn($arr);
    }
    /**
     * @param \ArrayAccess|array $arr
     * @return static[]
     */
    public static function indexByVariableName($arr): array{
        return static::indexBy($arr);
    }
}
