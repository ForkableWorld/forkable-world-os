<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Variable;
use App\Models\Variable;
use App\Properties\Base\BaseSynonymsProperty;
use App\Properties\Unit\UnitNameProperty;
use App\Properties\UnitCategory\UnitCategoryNameProperty;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\QMUnit;
use App\Slim\Model\QMUnitCategory;
use App\Storage\Memory;
use App\Traits\HasSeed;
use App\Traits\PropertyTraits\IsCalculated;
use App\Traits\PropertyTraits\VariableProperty;
use App\Types\QMArr;
use App\Types\QMStr;
use App\Variables\VariableSearchResult;
use Database\Seeders\DatabaseSeeder;
class VariableSynonymsProperty extends BaseSynonymsProperty
{
    use VariableProperty;
    use IsCalculated;
    public $table = Variable::TABLE;
    public $parentClass = Variable::class;
    protected array $notInArray = [
        "Raw Spinach by"
    ];
	/**
	 * @param string $unitName
	 * @param $synonym
	 * @param array $toKeep
	 * @return array
	 */
	private static function replaceUnitName(string $unitName, $synonym, array $toKeep): array{
		$replaced = str_replace($unitName, "", $synonym);
		$replaced = str_replace("()", " ", $replaced);
		$replaced = str_replace("  ", " ", $replaced);
		$replaced = trim($replaced);
		$toKeep[] = $replaced;
		return $toKeep;
	}
	private static function formatSynonym(string $value): string{
		return VariableNameProperty::removeDisallowedLastCharacters($value);
	}
	public function showOnUpdate(): bool{return QMAuth::isAdmin();}
    /**
     * @param VariableSearchResult|Variable $v
     * @return array
     * @noinspection PhpParameterNameChangedDuringInheritanceInspection
     */
    public static function calculate($v): array{
	    $v = $v->getVariable();
        $synonyms = $v->getSynonymsAttribute();
        $synonyms = self::removeNumeric($synonyms);
        try {
            $synonyms = self::addSpendingNames($v, $synonyms);
        } catch (\Throwable $e) {
            //le($e);
        }
        $synonyms = self::addWithoutAndInsideParenthesis($v, $synonyms);
        $synonyms = self::addBeforeDash($v, $synonyms);
        $synonyms = self::addBeforeComma($v, $synonyms);
        // Why? $synonyms = self::addBeforeAndAfterSlashes($v, $synonyms);
        $synonyms = self::addBeforeBy($v, $synonyms);
        $synonyms = self::addWithoutUnitCategory($v, $synonyms);
        $synonyms = self::addBeforeAndAfterOr($v, $synonyms);
        $synonyms = self::addBeforeDisorder($v, $synonyms);
        $synonyms = self::addBeforeSeverity($v, $synonyms);
        $synonyms = self::addNameDisplayNameAndAlias($synonyms, $v);
        $toKeep = self::unescapeDoubleQuotes($synonyms);
        $toKeep = self::removeNumeric($toKeep);
        $toKeep = QMStr::removeLowerCaseDuplicates($toKeep);
        // This sucks: $toKeep = QMStr::addSingularVersions($toKeep); ex. mikepsinn/qm-api becomes  mikepsinn/qm-apus
        $toKeep = self::removeUnitNames($toKeep);
        $toKeep = array_unique($toKeep);
        $v->setAttribute(static::NAME, $toKeep);
        return $toKeep;
    }
    private static function removeUnitNames(array $synonyms):array{
        $toKeep = [];
        foreach($synonyms as $synonym){
            $u = QMUnit::findByNameOrSynonym($synonym, false);
            if(!$u){
                $toKeep[] = $synonym;
            }
        }
        return $toKeep;
    }
	private static function addVersionsWithoutUnitNames(array $synonyms):array{
		$toKeep = [];
		foreach($synonyms as $synonym){
			$toKeep[] = $synonym;
			$unitName = UnitNameProperty::fromString($synonym, false);
			if($unitName){
				$toKeep = self::replaceUnitName($unitName, $synonym, $toKeep);
			}
		}
		return $toKeep;
	}
	private static function addVersionsWithoutUnitCategoryNames(array $synonyms):array{
		$toKeep = [];
		foreach($synonyms as $synonym){
			$toKeep[] = $synonym;
			$unitName = UnitCategoryNameProperty::fromString($synonym, false);
			if($unitName){
				$toKeep = self::replaceUnitName($unitName, $synonym, $toKeep);
			}
		}
		return $toKeep;
	}
	/**
	 * @param array $providedParams
	 * @param array $newVariable
	 * @param string $originalVariableName
	 * @param QMUnit $unit
	 * @return array
	 */
    public static function setNewVariableSynonyms(array $providedParams, array $newVariable,
                                                  string $originalVariableName, QMUnit $unit): array{
		$synonyms = [$originalVariableName];
		if(isset($newVariable['name'])){$synonyms[] = $newVariable['name'];}
		$arr = array_merge($providedParams, $newVariable);
		$plucked = self::pluck($arr);
		if($plucked){
			if(is_string($plucked)){$plucked = [$plucked];}
			$synonyms = array_merge($synonyms, $plucked);
		}
        if ($unit->isCurrency()) {
	        $synonyms[] = VariableNameProperty::toSpending($originalVariableName);
            foreach ($synonyms as $key => $value) {
	            $synonyms[$key] = VariableNameProperty::toSpending($value);
            }
        }
		$synonyms = self::addVersionsWithoutUnitNames($synonyms);
	    $synonyms = self::addVersionsWithoutUnitCategoryNames($synonyms);
	    $synonyms = self::format($synonyms);
        if($alias = VariableCommonAliasProperty::pluck($providedParams)){
            $synonyms[] = $alias;
        }
	    $synonyms = array_values($synonyms);
        return array_unique($synonyms);
    }
    /**
     * @param VariableSearchResult|string|array $v
     * @return string[]
     */
    public static function format($v): array {
		if(is_array($v)){
			$arr = $v;
		} else{
			if(is_string($v)){
				$synonyms = $v;
			} else {
				$synonyms = $v->synonyms;
			}
			$arr = QMArr::toArray($synonyms);
		}
		foreach($arr as $key => $value){
			$arr[$key] = self::formatSynonym($value);
		}
		return array_unique($arr);
    }
    /**
     * @param string $original
     * @param Variable $v
     * @return array
     */
    public static function decodeOrFallbackToName(string $original, Variable $v): array {
        if(is_array($original)){return $original;}
        if(!$original || $original === "[]"){
            //$v->logDebug("No synonyms!");
            return [$v->name];
        }
        if($MEM = Memory::get($original, __FUNCTION__)){return $MEM;}
        $decoded = json_decode($original, true);
        if(is_string($decoded)){ // Sometimes we had double encode
            $decoded = json_decode($decoded, true);
        }
        if(is_string($decoded)){ // Sometimes we had double encode
            $decoded = self::decodeByExplodingOnCommas($original);
        }
        if(!$decoded){ // Maybe it was too long for DB column?
            $v->logError("Could not decode synonyms: $original");
            if(strlen($original) > 0.9 * self::MAX_LENGTH){
                return Memory::set($original, self::decodeByExplodingOnCommas($original),
                    __FUNCTION__);
            }
            $decoded = [$v->name];
        }
		$decoded = self::format($decoded);
        return Memory::set($original, $decoded, __FUNCTION__);
    }
    /**
     * @param $synonyms
     * @return array
     */
    public static function toArray($synonyms): array{
        if(is_string($synonyms)){
            $decoded = json_decode($synonyms, true);
            if(!$decoded && strlen($synonyms) > self::MAX_LENGTH - 10){
                $decoded = explode('"', $synonyms);
                foreach($decoded as $key => $one){
                    if(strlen($one) < 3){
                        unset($decoded[$key]);
                    }
                }
            }
            if(is_string($decoded)){
                return self::decodeByExplodingOnCommas($synonyms);
            }
            $synonyms = array_values($decoded);
        }
        $synonyms = QMArr::toArray($synonyms);
        return $synonyms;
    }
    /**
     * @param Variable $v
     * @param array $synonyms
     * @return array
     */
    public static function addSpendingNames(Variable $v, array $synonyms): array{
        $unit = $v->getUserOrCommonUnit();
        if($unit->isCurrency()){
            $synonyms[] = VariableNameProperty::toSpending($v->name);
            foreach($synonyms as $key => $value){
                $synonyms[$key] = VariableNameProperty::toSpending($value);
            }
        }
        return $synonyms;
    }
    /**
     * @param Variable $v
     * @param array $synonyms
     * @return array
     */
    public static function addWithoutAndInsideParenthesis(Variable $v, array $synonyms): array{
        if(strpos($v->name, '(') !== false){
            $synonyms[] = trim(QMStr::before('(', $v->name));
            $inParenthesis = QMStr::between($v->name, '(', ')');
            if(!QMUnit::getUnitByAbbreviatedName($inParenthesis) && !QMUnitCategory::getByName($inParenthesis)){
                $synonyms[] = $inParenthesis;
            }
        }
        return $synonyms;
    }
    /**
     * @param Variable $v
     * @param array $synonyms
     * @return array
     */
    public static function addBeforeDash(Variable $v, array $synonyms): array{
        if(strpos($v->name, ' - ') !== false){
            $synonyms[] = trim(QMStr::before(' - ', $v->name));
        }
        return $synonyms;
    }
    /**
     * @param Variable $v
     * @param array $synonyms
     * @return array
     */
    public static function addBeforeComma(Variable $v, array $synonyms): array{
        if(strpos($v->name, ', ') !== false){
            $synonyms[] = trim(QMStr::before(', ', $v->name));
        }
        return $synonyms;
    }
    /**
     * @param Variable $v
     * @param array $synonyms
     * @return array
     */
    public static function addBeforeAndAfterSlashes(Variable $v, array $synonyms): array{
        if(strpos($v->name, '/') !== false){
            $synonyms[] = trim(QMStr::before('/', $v->name));
            $synonyms[] = trim(QMStr::after('/', $v->name));
        }
        return $synonyms;
    }
    /**
     * @param Variable $v
     * @param array $synonyms
     * @return array
     */
    public static function addBeforeBy(Variable $v, array $synonyms): array{
        if(stripos($v->name, ' by ') !== false){
            $synonyms[] = trim(QMStr::before(' by ', $v->name, $v->name, true));
        }
        return $synonyms;
    }
    /**
     * @param Variable $v
     * @param array $synonyms
     * @return array
     */
    public static function addWithoutUnitCategory(Variable $v, array $synonyms): array{
        foreach(QMUnitCategory::getIndexedByName() as $unitCategory){
            if(stripos($v->name, $unitCategory->name) && stripos($v->name, $unitCategory->name.'s') === false){
                $synonyms[] = trim(str_ireplace($unitCategory->name, '', $v->name));
            }
        }
        return $synonyms;
    }
    /**
     * @param Variable $v
     * @param array $synonyms
     * @return array
     */
    public static function addBeforeAndAfterOr(Variable $v, array $synonyms): array{
        if(stripos($v->name, ' or ') !== false){
            $synonyms[] = QMStr::after(' or ', $v->name, $v->name, true);
            $synonyms[] = QMStr::before(' or ', $v->name, $v->name, true);
        }
        return $synonyms;
    }
    /**
     * @param Variable $v
     * @param array $synonyms
     * @return array
     */
    public static function addBeforeDisorder(Variable $v, array $synonyms): array{
        if(stripos($v->name, ' Disorder') !== false){
            $synonyms[] = QMStr::before(' Disorder', $v->name, $v->name, true);
        }
        return $synonyms;
    }
    /**
     * @param Variable $v
     * @param array $synonyms
     * @return array
     */
    public static function addBeforeSeverity(Variable $v, array $synonyms): array{
        if(stripos($v->name, ' Severity') !== false){
            $synonyms[] = QMStr::before(' Severity', $v->name, $v->name, true);
        }
        return $synonyms;
    }
    /**
     * @param array $synonyms
     * @param Variable $v
     * @return array
     */
    public static function addNameDisplayNameAndAlias(array $synonyms, Variable $v): array{
        if(!$synonyms){$synonyms = [];}
		$n = $v->name;
	    $d = $v->getDisplayNameAttribute();
		$a = $v->getCommonAlias();
		if(!in_array($n, $synonyms)){$synonyms[] = $n;}
	    if(strtolower($n) !== strtolower($d)){$synonyms[] = $d;}
	    if($a && strtolower($n) !== strtolower($a)){$synonyms[] = $a;}
        return $synonyms;
    }
    /**
     * @param array $synonyms
     * @return array
     */
    public static function unescapeDoubleQuotes(array $synonyms): array{
        $toKeep = [];
        foreach($synonyms as $synonym){
            if($generatingEntitiesForDialogFlow = false){
                if(strpos($synonym, '(') !== false && strpos($synonym, ')') === false){
                    continue;
                }
                if(strpos($synonym, '(') === false && strpos($synonym, ')') !== false){
                    continue;
                }
                if(strpos($synonym, '(') !== false){
                    continue;
                } // Breaks entities
                if(strpos($synonym, ')') !== false){
                    continue;
                } // Breaks entities
                if(strpos($synonym, '"') !== false){
                    continue;
                } // Breaks entities
            }
            $synonym = QMStr::unescapeDoubleQuotes($synonym);
            $toKeep[] = $synonym;
        }
        return $toKeep;
    }
    /**
     * @param array $toKeep
     * @return array
     */
    public static function removeNumeric(array $toKeep): array{
        foreach($toKeep as $key => $value){
            if(is_numeric($value)){
                unset($toKeep[$key]);
            }
            $withoutSpending = str_ireplace(VariableNameProperty::SPENDING_ON_VARIABLE_DISPLAY_NAME_PREFIX, "", $value);
            if(is_numeric($withoutSpending)){
                unset($toKeep[$key]);
            }
        }
        return $toKeep;
    }
    /**
     * @param string $original
     * @return array
     */
    private static function decodeByExplodingOnCommas(string $original): array{
        $keep = [];
        $exploded = explode(",", $original);
        foreach($exploded as $key => $str){
            $str = str_replace('"', '', $str);
            $str = str_replace('[', '', $str);
            $str = str_replace(']', '', $str);
            $str = str_replace('\\', '', $str);
            $str = trim($str);
            $len = strlen($str);
            if($len > VariableNameProperty::MIN_LENGTH && $len < VariableNameProperty::MAX_LENGTH){
                $keep[] = $str;
            }
        }
        return array_values(array_unique($keep));
    }
    public function getRequiredStrings(): array{
        $v = $this->getVariable();
        return [$v->name, $v->getTitleAttribute()];
    }

    /**
     * @throws \App\Exceptions\InvalidAttributeException
     */
    protected function assertContainsDisplayName():void {
        /** @var Variable $v */
        $v = $this->getParentModel();
        $display = $v->getTitleAttribute();
        $arr = $this->getAccessorValue();
        if(!QMArr::inArrayCaseInsensitive($display, $arr)){
            if(DatabaseSeeder::isReprocessingSeed()){
                if(str_ends_with($display, ',')){
                    $display = QMStr::removeLastCharacter($display);
                    $v->common_alias = $display;
                    return;
                }
            }// Need to do case-insensitive check
            $this->throwException("should contain variable name $display");
        }
    }
    public function validate(): void {
        if(!$this->shouldValidate()){return;}
        $this->assertContainsVariableName();
        $this->assertContainsDisplayName();
        $arr = $this->getAccessorValue();
        foreach($this->notInArray as $needle){
            if(in_array($needle, $arr)){
                $this->throwException("Should not contain $needle");
            }
        }
        parent::validate();
    }
    /**
     * @throws \App\Exceptions\InvalidAttributeException
     */
    protected function assertContainsVariableName(): void{
        $v = $this->getVariable();
        $arr = $this->getAccessorValue();
        if(!QMArr::inArrayCaseInsensitive($v->name, $arr)){ // Need to do case-insensitive check
            $this->throwException("should contain variable name $v->name");
        }
    }
}
