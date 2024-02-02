<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Measurement;
use App\Models\Measurement;
use App\Traits\PropertyTraits\MeasurementProperty;
use App\Properties\Base\BaseNoteProperty;
use App\Types\QMStr;
use App\Utils\Stats;
use App\Slim\Model\Measurement\AdditionalMetaData;
use App\Slim\Model\User\QMUser;
use App\Variables\QMCommonVariable;
class MeasurementNoteProperty extends BaseNoteProperty
{
    use MeasurementProperty;
    public $table = Measurement::TABLE;
    public $parentClass = Measurement::class;
    public const SYNONYMS = [
        'note', // Needs to come first or we always get empty array
        'additional_meta_data',
    ];
    /**
     * @param QMUser $user
     * @param array $requestParams
     * @return array
     */
    public static function getAverageValueByNote(QMUser $user, array $requestParams): array{
        if (!isset($requestParams['variableId'])) {
            $variable = QMCommonVariable::findByNameOrId($requestParams['variableName']);
            $requestParams['variableId'] = $variable->getVariableIdAttribute();
        }
        $qb = Measurement::writable()
            ->where('variable_id', $requestParams['variableId'])
            ->whereRaw('note <> ""')
            ->whereRaw('note <> "null"')
            ->whereRaw('note <> "NULL"')
            ->whereRaw('note IS NOT NULL');
        if (!$user->isAdmin()) {
            $qb->where('user_id', $user->id);
        }
        $measurements = $qb->getArray();
        $wordsWithAverageValue = self::createWordCloudArrayFromMeasurements($measurements);
        $phrasesWithAverageValues = self::createWordCloudArrayFromMeasurements($measurements, true);
        $wordsWithAverageValueByCountAsc = $wordsWithAverageValue;
        usort($wordsWithAverageValueByCountAsc, [
            __CLASS__,
            "sort_array_by_count"
        ]);
        $wordsWithAverageValueByCountDesc = array_reverse($wordsWithAverageValueByCountAsc);
        $result['wordsByAverageValueAscending'] = array_slice($wordsWithAverageValueByCountDesc, 0, 100);
        usort($result['wordsByAverageValueAscending'], [
            __CLASS__,
            "sort_array_by_average"
        ]);
        $result['words'] = $result['wordsByAverageValueDescending'] = array_reverse($result['wordsByAverageValueAscending']);
        $phrasesWithAverageValueByCountAsc = $phrasesWithAverageValues;
        usort($phrasesWithAverageValueByCountAsc, [
            __CLASS__,
            "sort_array_by_count"
        ]);
        $phrasesWithAverageValueByCountDesc = array_reverse($phrasesWithAverageValueByCountAsc);
        $result['phrasesByAverageValueAscending'] = array_slice($phrasesWithAverageValueByCountDesc, 0, 100);
        usort($result['phrasesByAverageValueAscending'], [
            __CLASS__,
            "sort_array_by_average"
        ]);
        $result['phrases'] = $result['phrasesByAverageValueDescending'] = array_reverse($result['phrasesByAverageValueAscending']);
        return $result;
    }
    /**
     * @param $a
     * @param $b
     * @return int
     */
    public static function sort_array_by_count($a, $b): int{
        /** @noinspection TypeUnsafeComparisonInspection */
        if($a['count'] == $b['count']){
            return 0;
        }
        return ($a['count'] < $b['count']) ? -1 : 1;
    }
    /**
     * @param $a
     * @param $b
     * @return int
     */
    public static function sort_array_by_average($a, $b): int{
        if($a['average'] === $b['average']){
            return 0;
        }
        return ($a['average'] < $b['average']) ? -1 : 1;
    }
    /**
     * @param $measurements
     * @param bool $usePhrases
     * @return array
     */
    public static function createWordCloudArrayFromMeasurements($measurements, $usePhrases = false): array{
        $measurementValuesForAllWords = [];
        $wordsWithMeasurementValues = $wordsWithAverageValue = [];
        $mostOccurrencesOfSameWord = 0;
        $leastOccurrencesOfSameWord = 1000;
        $maxSize = 100;
        $minSize = 10;
        $red = "#F44336";
        $blue = "#2196F3";
        foreach($measurements as $measurement){
            $measurementValuesForAllWords[] = $measurement->value;
            $notePhrase = strtoupper($measurement->note);
            $notePhrase = str_replace([
                "?",
                "!",
                ",",
                ";",
                "."
            ],
                "",
                $notePhrase);
            $disallowedWords = [
                'about',
                'and',
                'at',
                'but',
                'from',
                'is',
                'not',
                'of',
                'or',
                'that',
                'this',
                'to',
                'was',
                "they're",
                "a",
                "am",
                "them",
                "on",
                "be",
                "if",
                "in",
                "so",
                "do",
                'what',
                'with',
                "the",
                "it's",
                "it",
                "for",
                "don't",
                "when",
                "then",
                "just",
                "as",
                "get",
                "are",
                "too",
                "en",
                "de"
            ];
            if(!$usePhrases){
                $noteWords = explode(" ", $notePhrase);
                foreach($noteWords as $noteWord){
                    if(!in_array(strtolower($noteWord), $disallowedWords, true)){
                        $wordsWithMeasurementValues[$noteWord][] = $measurement->value;
                    }
                }
            }else{
                $wordsWithMeasurementValues[$notePhrase][] = $measurement->value;
            }
        }
        $averageValueForAllWords = Stats::average($measurementValuesForAllWords);
        foreach($wordsWithMeasurementValues as $word => $measurementValuesForAllWords){
            if(is_string($word) && $word !== ''){
                $wordsWithAverageValue[$word]['text'] = $word;
                $avgValueForWord =
                $wordsWithAverageValue[$word]['average'] = Stats::average($measurementValuesForAllWords, 3);
                $wordsWithAverageValue[$word]['distanceFromAverage'] = abs($averageValueForAllWords - $avgValueForWord);
                $numberOfMeasurementsForWord =
                $wordsWithAverageValue[$word]['count'] = count($measurementValuesForAllWords);
                if($avgValueForWord > $averageValueForAllWords){
                    $wordsWithAverageValue[$word]['color'] = $red;  //TODO: Use adjustBrightness function below
                }else{
                    $wordsWithAverageValue[$word]['color'] = $blue;  //TODO: Use adjustBrightness function below
                }
                if($numberOfMeasurementsForWord > $mostOccurrencesOfSameWord){
                    $mostOccurrencesOfSameWord = $numberOfMeasurementsForWord;
                }
                if($numberOfMeasurementsForWord < $leastOccurrencesOfSameWord){
                    $leastOccurrencesOfSameWord = $numberOfMeasurementsForWord;
                }
            }
        }
        $normalizedWords = [];
        foreach($wordsWithAverageValue as $item){
            if($mostOccurrencesOfSameWord === $leastOccurrencesOfSameWord){
                $item['size'] = 20;
            }else{
                $item['size'] =
                    ($maxSize - $minSize) *
                    ($item['distanceFromAverage'] - $leastOccurrencesOfSameWord) /
                    ($mostOccurrencesOfSameWord - $leastOccurrencesOfSameWord) + $minSize;
            }
            $normalizedWords[] = $item;
        }
        return $normalizedWords;
    }
    /**
     * @param $hex
     * @param $steps
     * @return string
     */
    public function adjustBrightness($hex, $steps): string{
        // Steps should be between -255 and 255. Negative = darker, positive = lighter
        $steps = max(-255, min(255, $steps));
        // Normalize into a six character long hex string
        $hex = str_replace('#', '', $hex);
        if(strlen($hex) === 3){
            $hex = str_repeat(substr($hex, 0, 1), 2).str_repeat(substr($hex, 1, 1), 2).str_repeat(substr($hex, 2, 1), 2);
        }
        // Split into three parts: R, G and B
        $color_parts = str_split($hex, 2);
        $return = '#';
        foreach($color_parts as $color){
            $color = hexdec($color); // Convert to decimal
            $color = max(0, min(255, $color + $steps)); // Adjust color
            $return .= str_pad(dechex($color), 2, '0', STR_PAD_LEFT); // Make two char hex code
        }
        return $return;
    }
    /**
     * @param array $target
     * @param array|object $source
     * @return array
     */
    public static function addToArrayIfPresent(array $target, $source): array{
        return parent::addToArrayIfPresent($target, $source);
    }
    public function toDBValue($value){
        if($value && !QMStr::isJson($value)){
            $meta = new AdditionalMetaData($value);
            $value = $meta->compress();
        }
        return $value;
    }
    public function getDBValue():?string{
        $p = $this->getParentModel();
        $val = $p->getRawAttribute($this->name);
        return $this->toDBValue($val);
    }
}
