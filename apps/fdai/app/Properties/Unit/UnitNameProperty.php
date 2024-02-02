<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Unit;
use App\Models\Unit;
use App\Slim\Model\QMUnit;
use App\Storage\Memory;
use App\Traits\PropertyTraits\UnitProperty;
use App\Properties\Base\BaseNameProperty;
use App\Types\QMStr;
use App\UnitCategories\MiscellanyUnitCategory;
class UnitNameProperty extends BaseNameProperty
{
	const MIN_LENGTH = 2;
	public $minLength = self::MIN_LENGTH;
    use UnitProperty;
    public $table = Unit::TABLE;
    public $parentClass = Unit::class;
    /**
     * @param int|string $id
     * @return mixed|null
     */
    public static function fromId($id){
        $val = Memory::get($id, __METHOD__);
        if($val !== null){return $val;}
        $val = parent::fromId($id);
        Memory::set($id, $val, __METHOD__);
        return $val;
    }
	/**
	 * @param string $variableName
	 * @param bool $preferNonMiscellaneous
	 * @return string
	 */
	public static function fromString(string $variableName, bool $preferNonMiscellaneous = true): ?string{
		// Regular Can Coke 355ml (12 Oz),  Acidic Foods - 6-oz Granules: Guaranteed,  Jarrow Formulas Curcumin 95, Provides Antioxidant Support, 500 mg, 120 Veggie Caps
		$variableName = QMStr::removeNonAlphaNumericCharactersFromString($variableName, " ");
		$variableName = QMStr::addSpaceBetweenNumbersAndLetters($variableName);
		$unitName = self::getUnitNameFromStringByFullName($variableName, $preferNonMiscellaneous);
		if(!$unitName){
			$unitName = self::fromStringByAbbreviatedName($variableName, $preferNonMiscellaneous);
		}
		if(!$unitName){
			$unitName = self::fromStringBySynonyms($variableName);
		}
		if($unitName){
			return $unitName;
		}
		if($preferNonMiscellaneous){
			return self::fromString($variableName, false);
		}
		return null;
	}
	/**
	 * @param $variableName
	 * @param bool $preferNonMiscellaneous
	 * @return string
	 */
	public static function fromStringByAbbreviatedName($variableName,
		bool $preferNonMiscellaneous = true): ?string{
		$variableWords = explode(' ', $variableName);
		$previousWordIsNumeric = false;
		foreach($variableWords as $originalWord){
			//$alphabeticalWord = StringHelper::removeNonAlphabeticalCharactersFromString($originalWord);
			$includeAdvanced =
				(strlen($originalWord) > 2 || $previousWordIsNumeric);  // Let's avoid matching weird but short units
			$caseSensitive = strlen($originalWord) <
				2;  // No strtolower if 1 character to avoid matching S with seconds and M as meters
			$unit = QMUnit::getUnitByAbbreviatedName($originalWord, $caseSensitive, $includeAdvanced);
			if($unit && (!$preferNonMiscellaneous || $unit->categoryName !== MiscellanyUnitCategory::NAME)){
				return $originalWord;
			}
			$previousWordIsNumeric = is_numeric($originalWord);
		}
		return null;
	}
	/**
	 * @param $variableName
	 * @return string
	 */
	public static function fromStringBySynonyms($variableName): ?string{
		$variableWords = explode(' ', $variableName);
		foreach($variableWords as $word){
			//$word = StringHelper::removeNonAlphabeticalCharactersFromString($word);
			$includeAdvanced = strlen($word) > 2;  // Let's avoid matching weird but short units
			$unit = QMUnit::getUnitBySynonyms($word, $includeAdvanced);
			if($unit){
				return $word;
			}
		}
		return null;
	}
	/**
	 * @return string
	 */
	public static function getList(): string{
		$units = QMUnit::all();
		$names = [];
		foreach($units as $unit){
			$names[] =  $unit->name;
		}
		sort($names);
		return implode("\n\t", $names);
	}
	/**
	 * @param $variableName
	 * @param bool $preferNonMiscellaneous
	 * @return string
	 */
	private static function getUnitNameFromStringByFullName($variableName, bool $preferNonMiscellaneous = true): ?string{
		$variableWords = explode(' ', $variableName);
		foreach($variableWords as $word){
			//$word = StringHelper::removeNonAlphabeticalCharactersFromString($word);
			$unit = QMUnit::getUnitByFullName($word);
			if($unit){ // Preference to longer more unique names
				if(!$preferNonMiscellaneous ||
					$unit->categoryName !== MiscellanyUnitCategory::NAME){
					return $word;
				}
			}
		}
		return null;
	}
}
