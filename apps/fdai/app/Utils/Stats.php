<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */
namespace App\Utils;
use App\VariableRelationships\QMUserVariableRelationship;
use App\Exceptions\ExceptionHandler;
use App\Logging\QMLog;
use App\Types\QMArr;
use App\Types\QMStr;
use App\Types\TimeHelper;
use Exception;
/** Stats class
 * Static class containing a variety of useful statistical functions.
 * Fills in where PHP's math functions fall short.  Many functions are
 * used extensively by the probability distributions.
 */
class Stats {
	/**
	 * @param $a
	 * @param $b
	 * @return bool
	 */
	public static function equal($a, $b): bool{ return Stats::floatsEqual($a, $b); }
	/**
	 * @param $a
	 * @return bool
	 */
	public static function isZero($a): bool{ return self::equal($a, 0); }
	/**
	 * Function that calculate mean value of array
	 * @param array $arr
	 * @param int|null $significantFigures
	 * @return float|int
	 */
	public static function mean(array $arr, int $significantFigures = null){
		$mean = self::average($arr);
		if($significantFigures){
			$mean = self::roundByNumberOfSignificantDigits($mean, $significantFigures);
		}
		return $mean;
	}
	/**
	 * Calculate standard deviation of array, by definition it is square root of variance
	 * @param array $arr
	 * @return float
	 */
	public static function standardDeviation(array $arr): float{
		if(!$arr){
			le("No values provided to " . __FUNCTION__);
		}
		$fVariance = self::variance($arr);
		return (float)sqrt($fVariance);
	}
	/**
	 * Calculate variance of array
	 * @param array $arr
	 * @return float|int|number
	 */
	public static function variance(array $arr){
		if(!$arr){
			le("No values provided to " . __FUNCTION__);
		}
		$fVariance = 0.0;
		if(count($arr) < 2){
			QMLog::debug('Got less then 2 elements for variance calculation');
			return $fVariance;
		}
		$mean = array_sum($arr) / count($arr);
		foreach($arr as $i){
			$fVariance += ($i - $mean) ** 2;
		}
		if(count($arr) > 1){
			$fVariance /= count($arr) - 1;
		}
		return $fVariance;
	}
	/**
	 * Calculate Skewness and Kurtosis, two parameters are sent by reference, so be carefull
	 * @param array $arr
	 * @param float $skew
	 * @param float $kurt
	 */
	public static function skewnessAndKurtosis(array $arr, &$skew, &$kurt){
		if(!$arr){
			le("No values provided to " . __FUNCTION__);
		}
		$count = count($arr);
		if($count < 2){
			QMLog::debug('Got less then 2 elements for skewnessAndKurtosis calculation');
			return;
		}
		$sum = 0;
		$mean = self::average($arr);
		foreach($arr as $val){
			$sum += ($val - $mean) ** 3;
		}
		$deviation = self::standardDeviation($arr);
		$denominator = (($count - 1) * ($deviation ** 3));
		if($denominator){
			$skew = $sum / $denominator;
		}
		$sum = 0;
		foreach($arr as $val){
			$sum += ($val - $mean) ** 4;
		}
		$denominator = (($count - 1) * ($deviation ** 4));
		if($denominator){
			$kurt = $sum / $denominator;
		}
	}
	/**
	 * Function that calculate range
	 * @param array $arr
	 * @return bool|float|int
	 */
	public static function range(array $arr){
		if(!$arr){
			le("No values provided to " . __FUNCTION__);
		}
		sort($arr);
		$sml = $arr[0];
		$lrg = $arr[count($arr) - 1];
		return ($lrg - $sml);
	}
	/**
	 * Function that calculate mode
	 * @param array $arr
	 * @return bool|float|int|string
	 */
	public static function mode(array $arr){
		if(!$arr){
			le("No values provided to " . __FUNCTION__);
		}
		$assoc = [];
		foreach($arr as $val){
			if(isset($assoc[(string)$val])){
				$assoc[(string)$val]++;
			} else{
				$assoc[(string)$val] = 1;
			}
		}
		arsort($assoc);
		$mode = null;
		foreach($assoc as $val => $count){
			$mode = $val;
			break;
		}
		return $mode;
	}
	/**
	 * Calculates QM Score
	 * @param QMUserVariableRelationship $correlation
	 * @param $numberOfPairs
	 * @param $causeArray
	 * @param $effectArray
	 * @return float
	 */
	public static function qmScore(QMUserVariableRelationship $correlation, int $numberOfPairs, array $causeArray,
		array $effectArray){
		if($correlation->avgDailyValuePredictingHighOutcome === null ||
			$correlation->avgDailyValuePredictingLowOutcome === null){
			return null;
		}
		$sampleSizeCoefficient = 1 - exp(-$numberOfPairs / 100);
		$causeSkewness = $causeKurtosis = $effectSkewness = $effectKurtosis = null;
		self::skewnessAndKurtosis($causeArray, $causeSkewness, $causeKurtosis);
		if(empty($causeSkewness) or empty($causeKurtosis)){
			return null;
		}
		self::skewnessAndKurtosis($effectArray, $effectSkewness, $effectKurtosis);
		if(empty($causeSkewness) or empty($causeKurtosis)){
			return null;
		}
		$skewnessCoefficient = (1 / (1 + $causeSkewness ^ 2)) * (1 / (1 + $effectSkewness ^ 2));
		$kurtosisCoefficient = (1 / (1 + $causeKurtosis ^ 2)) * (1 / (1 + $effectKurtosis ^ 2));
		$causeMean = self::mean($causeArray);
		$stdDev = self::standardDeviation($causeArray);
		if($stdDev <= 0){
			return null;
		}
		$predictiveDifferenceCoefficient = (($correlation->avgDailyValuePredictingHighOutcome - $causeMean) / $stdDev) -
			(($correlation->avgDailyValuePredictingLowOutcome - $causeMean) / $stdDev) ^ 2;
		$qmScore = $correlation->optimalPearsonProduct ^
			2 * $sampleSizeCoefficient * $skewnessCoefficient * $kurtosisCoefficient *
			$predictiveDifferenceCoefficient * $correlation->calculateWeightedAverageVote() * $correlation->userVote;
		return $qmScore;
	}
	/**
	 * Sum Function
	 * Sums an array of numeric values.  Non-numeric values
	 * are treated as zeroes.
	 * @param array $data An array of numeric values
	 * @return float The sum of the elements of the array
	 * @static
	 */
	public static function sum(array $data): float{
		if(!$data){
			le("No values provided to " . __FUNCTION__);
		}
		$data = array_values($data);
		$sum = 0.0;
		foreach($data as $element){
			if(is_numeric($element)){
				$sum += $element;
			} else{
				le("element value ($element) is not numeric!");
			}
		}
		return $sum;
	}
	/**
	 * @param $orderedArray
	 * @return int
	 */
	public static function countChanges(array $orderedArray): int{
		$changes = 0;
		if(!isset($orderedArray[0])){
			$orderedArray = array_values($orderedArray);
		}
		for($i = 0; $i < count($orderedArray) - 1; $i++){
			if($orderedArray[$i] != $orderedArray[$i + 1]){
				$changes++;
			}
		}
		return $changes;
	}
	/**
	 * Product Function
	 * Multiplies an array of numeric values.  Non-numeric values
	 * are treated as ones.
	 * @param array $data An array of numeric values
	 * @return float The product of the elements of the array
	 * @static
	 */
	public static function product(array $data): float{
		$product = 1;
		foreach($data as $element){
			if(is_numeric($element)){
				$product *= $element;
			}
		}
		return $product;
	}
	/**
	 * Average Function
	 * Takes the arithmetic mean of an array of numeric values.
	 * Non-numeric values are treated as zeroes.
	 * Function that calculate average value of array
	 * @param array $arr
	 * @param int|null $significantFigures
	 * @param string|null $logLabel
	 * @return float|int
	 */
	public static function average(array $arr, int $significantFigures = null, string $logLabel = null): float{
		$count = count($arr);
		if(!$count){
			le("Empty array provided to Stats::average!");
		}
		$sum = array_sum($arr);
		$average = $sum / $count;
		if($significantFigures && $average){
			$average = self::roundByNumberOfSignificantDigits($average, $significantFigures);
		}
		if($logLabel){
			QMLog::info("Average $logLabel: $average");
		}
		return $average;
	}
	/**
	 * @param array $arr
	 * @param string $propertyName
	 * @param int $significantFigures
	 * @param string|null $logLabel
	 * @return float
	 */
	public static function getAverageOfPropertyOrField(array $arr, string $propertyName, int $significantFigures = 5,
		string $logLabel = null){
		$values = [];
		foreach($arr as $item){
			if(!is_array($item)){
				$item = json_decode(json_encode($item), true);
			}
			if($item[$propertyName] === null){
				continue;
			}
			$values[] = $item[$propertyName];
		}
		if(!$values){
			return null;
		}
		return self::average($values, $significantFigures, $logLabel);
	}
	/**
	 * @param float $original
	 * @param float $new
	 * @param int $significantFigures
	 * @return float
	 */
	public static function getPercentChange($original, $new, $significantFigures = null){
		if(!$original){
			QMLog::error("Original value cannot be 0!");
			return false;
		}
		$change = ($new - $original) / $original * 100;
		if($significantFigures){
			$change = self::roundByNumberOfSignificantDigits($change, $significantFigures);
		}
		return $change;
	}
	/**
	 * Sum-Squared Error Function
	 * Returns the sum of squares of errors of an array of numeric values.
	 * Non-numeric values are treated as zeroes.
	 * @param array $data An array of numeric values
	 * @return float The sum of the squared errors of the elements of the array
	 * @static
	 */
	public static function sse(array $data){
		$average = self::average($data);
		$sum = 0.0;
		foreach($data as $element){
			if(is_numeric($element)){
				$sum += ($element - $average) ** 2;
			} else{
				$sum += (0 - $average) ** 2;
			}
		}
		return $sum;
	}
	/**
	 * Standard Deviation Function
	 * Returns the population standard deviation of an array.
	 * Non-numeric values are treated as zeroes.
	 * @param array $data An array of numeric values
	 * @return float The population standard deviation of the supplied array
	 * @static
	 */
	public static function stdDev(array $data): float{
		return sqrt(self::variance($data));
	}
	/**
	 * UserVariableRelationship Function
	 * Returns the correlation of two arrays.  The two arrays must
	 * be of equal length. Non-numeric values are treated as zeroes.
	 * @param array $x An array of numeric values
	 * @param array $y An array of numeric values
	 * @return float The correlation of the two supplied arrays
	 * @static
	 */
	public static function calculatePearsonCorrelationCoefficient($x, $y){
		if((self::stdDevFromStats($x) * self::stdDevFromStats($y)) == 0){
			QMLog::debug('Cannot calculate correlation due to lack of variance in data.  stddev of datax = ' .
				self::stdDevFromStats($x) . ' and stddev of datay = ' . self::stdDevFromStats($y));
			return null;
		}
		return self::covarianceFromStats($x, $y) / (self::stdDevFromStats($x) * self::stdDevFromStats($y));
	}
	/**
	 * Factorial Function
	 * Returns the factorial of an integer.  Values less than 1 return
	 * as 1.  Non-integer arguments are evaluated only for the integer
	 * portion (the floor).
	 * @param int $x An array of numeric values
	 * @return int The factorial of $x, i.e. x!
	 * @static
	 */
	public static function factorial($x): int{
		$sum = 1;
		for($i = 1, $iMax = floor($x); $i <= $iMax; $i++){
			$sum *= $i;
		}
		return $sum;
	}
	/**
	 * Error Function
	 * Returns the real error function of a number.
	 * An approximation from Abramowitz and Stegun is used.
	 * Maximum error is 1.5e-7. More information can be found at
	 * http://en.wikipedia.org/wiki/Error_function#Approximation_with_elementary_functions
	 * @param float $x Argument to the real error function
	 * @return float A value between -1 and 1
	 * @static
	 */
	public static function erf($x){
		if($x < 0){
			return -self::erf(-$x);
		}
		$t = 1 / (1 + 0.3275911 * $x);
		return 1 - (0.254829592 * $t - 0.284496736 * ($t ** 2) + 1.421413741 * ($t ** 3) + -1.453152027 * ($t ** 4) +
				1.061405429 * ($t ** 5)) * exp(-$x ** 2);
	}
	/**
	 * Gamma Function
	 * Returns the gamma function of a number.
	 * The gamma function is a generalization of the factorial function
	 * to non-integer and negative non-integer values.
	 * The relationship is as follows: gamma(n) = (n - 1)!
	 * Stirling's approximation is used.  Though the actual gamma function
	 * is defined for negative, non-integer values, this approximation is
	 * undefined for anything less than or equal to zero.
	 * @param float $x Argument to the gamma function
	 * @return float The gamma of $x
	 * @static
	 */
	public static function gamma($x){
		//Lanczos' Approximation from Wikipedia
		// Coefficients used by the GNU Scientific Library
		$g = 7;
		$p = [
			0.99999999999980993,
			676.5203681218851,
			-1259.1392167224028,
			771.32342877765313,
			-176.61502916214059,
			12.507343278686905,
			-0.13857109526572012,
			9.9843695780195716e-6,
			1.5056327351493116e-7,
		];
		// Reflection formula
		if($x < 0.5){
			return M_PI / (sin(M_PI * $x) * self::gamma(1 - $x));
		} else{
			$x--;
			$y = $p[0];
			for($i = 1; $i < $g + 2; $i++){
				$y += $p[$i] / ($x + $i);
			}
			$t = $x + $g + 0.5;
			return ((2 * M_PI) ** 0.5) * ($t ** ($x + 0.5)) * exp(-$t) * $y;
		}
	}
	/**
	 * Log Gamma Function
	 * Returns the natural logarithm of the gamma function.  Useful for
	 * scaling.
	 * @param float $x Argument to the gamma function
	 * @return float The natural log of gamma of $x
	 * @static
	 */
	public static function gammaln($x){
		//Thanks to jStat for this one.
		$cof = [
			76.18009172947146,
			-86.50532032941677,
			24.01409824083091,
			-1.231739572450155,
			0.1208650973866179e-2,
			-0.5395239384953e-5,
		];
		$xx = $x;
		$y = $xx;
		$tmp = $x + 5.5;
		$tmp -= ($xx + 0.5) * log($tmp);
		$ser = 1.000000000190015;
		for($j = 0; $j < 6; $j++){
			$ser += $cof[$j] / ++$y;
		}
		return log(2.5066282746310005 * $ser / $xx) - $tmp;
	}
	/**
	 * Beta Function
	 * Returns the beta function of a pair of numbers.
	 * @param float $a The alpha parameter
	 * @param float $b The beta parameter
	 * @return float The beta of $a and $b
	 * @static
	 */
	public static function beta($a, $b){
		return self::gamma($a) * self::gamma($b) / self::gamma($a + $b);
	}
	/**
	 * Permutation Function
	 * Returns the number of ways of choosing $r objects from a collection
	 * of $n objects, where the order of selection matters.
	 * @param int $n The size of the collection
	 * @param int $r The size of the selection
	 * @return int $n pick $r
	 * @static
	 */
	public static function permutations($n, $r){
		return self::factorial($n) / self::factorial($n - $r);
	}
	/**
	 * Combination Function
	 * Returns the number of ways of choosing $r objects from a collection
	 * of $n objects, where the order of selection does not matter.
	 * @param int $n The size of the collection
	 * @param int $r The size of the selection
	 * @return int $n choose $r
	 * @static
	 */
	public static function combinations($n, $r){
		return self::permutations($n, $r) / self::factorial($r);
	}
	/**
	 * Function that calculate mean median
	 * @param array $arr
	 * @param int|null $significantFigures
	 * @return bool|float|int
	 */
	public static function median($arr, $significantFigures = null){
		if(!$arr){
			le("No values provided to " . __FUNCTION__);
		}
		sort($arr);
		$count = count($arr);
		if($count % 2 == 0){
			$middle = (int)($count / 2);
			$median = ($arr[$middle - 1] + $arr[$middle]) / 2;
		} else{
			$median = $arr[(int)($count / 2)];
		}
		if($significantFigures){
			$median = self::roundByNumberOfSignificantDigits($median, $significantFigures);
		}
		return $median;
	}
	/**
	 * @param float $number
	 * @param int $maximumDigits
	 * @return float|int
	 */
	public static function roundToSignificantFiguresIfGreater($number, int $maximumDigits = 4){
		if(strlen((string)$number) > $maximumDigits){
			return self::roundByNumberOfSignificantDigits($number, $maximumDigits);
		}
		return $number;
	}
	/**
	 * @param $number
	 * @param $significantFigures
	 * @return float|int
	 */
	public static function roundByNumberOfSignificantDigits($number, int $significantFigures){
		if(is_string($number)){
			QMLog::error("String provided to roundByNumberOfSignificantDigits: $number");
			return $number;
		}
		$originalNumber = $number;
		// PHP7 float precision issues are a nightmare!  You're better off just using number_format()
		if(!$number){
			return 0;
		}
		$multiplier = 1;
		while(abs($number) < 0.1){
			$number *= 10;
			$multiplier /= 10;
		}
		try {
			while(abs($number) >= 1){
				$number /= 10;
				$multiplier *= 10;
			}
		} catch (Exception $e) {
			QMLog::error("Trying to divide $number but got exception: " . $e->getMessage() .
				". Original number was $originalNumber");
			ExceptionHandler::logExceptionOrThrowIfLocalOrPHPUnitTest($e);
			return $originalNumber;
		}
		if(!is_int($significantFigures)){
			QMLog::error("significantFigures should be int but is $significantFigures");
		}
		$rounded = round($number, $significantFigures);
		if(strlen((string)$rounded) > $significantFigures + 3){
			$precision = ini_get('precision');
			$serialize_precision = ini_get('serialize_precision');
			QMLog::error("Could not round! $number rounded to $rounded.  precision: $precision; serialize_precision: $serialize_precision");
		}
		$multiplied = $rounded * $multiplier;  // Don't use invtal because multiplier is below 0 sometimes
		return $multiplied;
	}
	// Function to calculate standard deviation (uses sd_square)
	// input: an array over which you want to calculate the stddev
	// consult the student t-table, degrees of freedom vs. tail probability
	// check here to see how to calculate confidence intervals using the ttable:
	// http://www.ehow.com/how_5933144_calculate-confidence-interval-mean.html
	/**
	 * @param $degreesOfFreedom
	 * @param $minimumProbability
	 * @return int
	 */
	public static function getCriticalTValue($degreesOfFreedom, $minimumProbability){
		$probabilityArray = [
			0.25,
			0.20,
			0.15,
			0.10,
			0.05,
			0.025,
			0.02,
			0.01,
			0.005,
			0.0025,
			0.001,
			0.0005,
		];
		$probabilityKey = array_search($minimumProbability, $probabilityArray, true);
		if($probabilityKey === false){
			return 128;
		}
		//df .25 .20 .15 .10 .05 .025 .02 .01 .005 .0025 .001 .0005
		$criticalTLookupTable = [
			'1' => [
				1.000,
				1.376,
				1.963,
				3.078,
				6.314,
				12.71,
				15.89,
				31.82,
				63.66,
				127.3,
				318.3,
				636.6,
			],
			'2' => [
				0.816,
				1.061,
				1.386,
				1.886,
				2.920,
				4.303,
				4.849,
				6.965,
				9.925,
				14.09,
				22.33,
				31.60,
			],
			'3' => [
				0.765,
				0.978,
				1.250,
				1.638,
				2.353,
				3.182,
				3.482,
				4.541,
				5.841,
				7.453,
				10.21,
				12.92,
			],
			'4' => [
				0.741,
				0.941,
				1.190,
				1.533,
				2.132,
				2.776,
				2.999,
				3.747,
				4.604,
				5.598,
				7.173,
				8.610,
			],
			'5' => [
				0.727,
				0.920,
				1.156,
				1.476,
				2.015,
				2.571,
				2.757,
				3.365,
				4.032,
				4.773,
				5.893,
				6.869,
			],
			'6' => [
				0.718,
				0.906,
				1.134,
				1.440,
				1.943,
				2.447,
				2.612,
				3.143,
				3.707,
				4.317,
				5.208,
				5.959,
			],
			'7' => [
				0.711,
				0.896,
				1.119,
				1.415,
				1.895,
				2.365,
				2.517,
				2.998,
				3.499,
				4.029,
				4.785,
				5.408,
			],
			'8' => [
				0.706,
				0.889,
				1.108,
				1.397,
				1.860,
				2.306,
				2.449,
				2.896,
				3.355,
				3.833,
				4.501,
				5.041,
			],
			'9' => [
				0.703,
				0.883,
				1.100,
				1.383,
				1.833,
				2.262,
				2.398,
				2.821,
				3.250,
				3.690,
				4.297,
				4.781,
			],
			'10' => [
				0.700,
				0.879,
				1.093,
				1.372,
				1.812,
				2.228,
				2.359,
				2.764,
				3.169,
				3.581,
				4.144,
				4.587,
			],
			'11' => [
				0.697,
				0.876,
				1.088,
				1.363,
				1.796,
				2.201,
				2.328,
				2.718,
				3.106,
				3.497,
				4.025,
				4.437,
			],
			'12' => [
				0.695,
				0.873,
				1.083,
				1.356,
				1.782,
				2.179,
				2.303,
				2.681,
				3.055,
				3.428,
				3.930,
				4.318,
			],
			'13' => [
				0.694,
				0.870,
				1.079,
				1.350,
				1.771,
				2.160,
				2.282,
				2.650,
				3.012,
				3.372,
				3.852,
				4.221,
			],
			'14' => [
				0.692,
				0.868,
				1.076,
				1.345,
				1.761,
				2.145,
				2.264,
				2.624,
				2.977,
				3.326,
				3.787,
				4.140,
			],
			'15' => [
				0.691,
				0.866,
				1.074,
				1.341,
				1.753,
				2.131,
				2.249,
				2.602,
				2.947,
				3.286,
				3.733,
				4.073,
			],
			'16' => [
				0.690,
				0.865,
				1.071,
				1.337,
				1.746,
				2.120,
				2.235,
				2.583,
				2.921,
				3.252,
				3.686,
				4.015,
			],
			'17' => [
				0.689,
				0.863,
				1.069,
				1.333,
				1.740,
				2.110,
				2.224,
				2.567,
				2.898,
				3.222,
				3.646,
				3.965,
			],
			'18' => [
				0.688,
				0.862,
				1.067,
				1.330,
				1.734,
				2.101,
				2.214,
				2.552,
				2.878,
				3.197,
				3.611,
				3.922,
			],
			'19' => [
				0.688,
				0.861,
				1.066,
				1.328,
				1.729,
				2.093,
				2.205,
				2.539,
				2.861,
				3.174,
				3.579,
				3.883,
			],
			'20' => [
				0.687,
				0.860,
				1.064,
				1.325,
				1.725,
				2.086,
				2.197,
				2.528,
				2.845,
				3.153,
				3.552,
				3.850,
			],
			'21' => [
				0.663,
				0.859,
				1.063,
				1.323,
				1.721,
				2.080,
				2.189,
				2.518,
				2.831,
				3.135,
				3.527,
				3.819,
			],
			'22' => [
				0.686,
				0.858,
				1.061,
				1.321,
				1.717,
				2.074,
				2.183,
				2.508,
				2.819,
				3.119,
				3.505,
				3.792,
			],
			'23' => [
				0.685,
				0.858,
				1.060,
				1.319,
				1.714,
				2.069,
				2.177,
				2.500,
				2.807,
				3.104,
				3.485,
				3.768,
			],
			'24' => [
				0.685,
				0.857,
				1.059,
				1.318,
				1.711,
				2.064,
				2.172,
				2.492,
				2.797,
				3.091,
				3.467,
				3.745,
			],
			'25' => [
				0.684,
				0.856,
				1.058,
				1.316,
				1.708,
				2.060,
				2.167,
				2.485,
				2.787,
				3.078,
				3.450,
				3.725,
			],
			'26' => [
				0.684,
				0.856,
				1.058,
				1.315,
				1.706,
				2.056,
				2.162,
				2.479,
				2.779,
				3.067,
				3.435,
				3.707,
			],
			'27' => [
				0.684,
				0.855,
				1.057,
				1.314,
				1.703,
				2.052,
				2.15,
				2.473,
				2.771,
				3.057,
				3.421,
				3.690,
			],
			'28' => [
				0.683,
				0.855,
				1.056,
				1.313,
				1.701,
				2.048,
				2.154,
				2.467,
				2.763,
				3.047,
				3.408,
				3.674,
			],
			'29' => [
				0.683,
				0.854,
				1.055,
				1.311,
				1.699,
				2.045,
				2.150,
				2.462,
				2.756,
				3.038,
				3.396,
				3.659,
			],
			'30' => [
				0.683,
				0.854,
				1.055,
				1.310,
				1.697,
				2.042,
				2.147,
				2.457,
				2.750,
				3.030,
				3.385,
				3.646,
			],
			'40' => [
				0.681,
				0.851,
				1.050,
				1.303,
				1.684,
				2.021,
				2.123,
				2.423,
				2.704,
				2.971,
				3.307,
				3.551,
			],
			'50' => [
				0.679,
				0.849,
				1.047,
				1.295,
				1.676,
				2.009,
				2.109,
				2.403,
				2.678,
				2.937,
				3.261,
				3.496,
			],
			'60' => [
				0.679,
				0.848,
				1.045,
				1.296,
				1.671,
				2.000,
				2.099,
				2.390,
				2.660,
				2.915,
				3.232,
				3.460,
			],
			'80' => [
				0.678,
				0.846,
				1.043,
				1.292,
				1.664,
				1.990,
				2.088,
				2.374,
				2.639,
				2.887,
				3.195,
				3.416,
			],
			'100' => [
				0.677,
				0.845,
				1.042,
				1.290,
				1.660,
				1.984,
				2.081,
				2.364,
				2.626,
				2.871,
				3.174,
				3.390,
			],
			'1000' => [
				0.675,
				0.842,
				1.037,
				1.282,
				1.646,
				1.962,
				2.056,
				2.330,
				2.581,
				2.813,
				3.098,
				3.300,
			],
			'100000000' => [
				0.674,
				0.841,
				1.036,
				1.282,
				1.64,
				1.960,
				2.054,
				2.326,
				2.576,
				2.807,
				3.091,
				3.291,
			],
		];
		foreach($criticalTLookupTable as $key => $value){
			if($key > $degreesOfFreedom){
				return $criticalTLookupTable[$key][$probabilityKey];
			}
		}
		//maps the degrees of freedom, note for large values this needs to be rounded to the nearest key
		return 129;
	}
	/**
	 * Sum Function
	 * Sums an array of numeric values.  Non-numeric values
	 * are treated as zeroes.
	 * @param array $data An array of numeric values
	 * @return float The sum of the elements of the array
	 * @static
	 */
	public static function sumFromStats(array $data){
		$sum = 0.0;
		foreach($data as $element){
			if(is_numeric($element)){
				$sum += $element;
			}
		}
		return $sum;
	}
	/**
	 * Average Function
	 * Takes the arithmetic mean of an array of numeric values.
	 * Non-numeric values are treated as zeroes.
	 * @param array $data An array of numeric values
	 * @return float The arithmetic average of the elements of the array
	 * @static
	 */
	public static function averageFromStats(array $data){
		if(!count($data)){
			QMLog::info("No data provided to averageFromStats");
			return null;
		}
		return self::sumFromStats($data) / count($data);
	}
	/**
	 * Sum-XY Function
	 * Returns the sum of products of paired variables in a pair of arrays
	 * of numeric values.  The two arrays must be of equal length.
	 * Non-numeric values are treated as zeroes.
	 * @param array $datax An array of numeric values
	 * @param array $datay An array of numeric values
	 * @return float The products of the paired elements of the arrays
	 * @static
	 */
	public static function sumXYFromStats(array $datax, array $datay){
		$n = min(count($datax), count($datay));
		$sum = 0.0;
		for($count = 0; $count < $n; $count++){
			if(is_numeric($datax[$count])){
				$x = $datax[$count];
			} else{
				$x = 0;
			} //Non-numeric elements count as zero.
			if(is_numeric($datay[$count])){
				$y = $datay[$count];
			} else{
				$y = 0;
			} //Non-numeric elements count as zero.
			$sum += $x * $y;
		}
		return $sum;
	}
	/**
	 * Covariance Function
	 * Returns the covariance of two arrays.  The two arrays must
	 * be of equal length. Non-numeric values are treated as zeroes.
	 * @param array $datax An array of numeric values
	 * @param array $datay An array of numeric values
	 * @return float The covariance of the two supplied arrays
	 * @static
	 */
	public static function covarianceFromStats(array $datax, array $datay){
		if(!count($datax)){
			QMLog::error("No x values");
			return null;
		}
		if(!count($datay)){
			QMLog::error("No y values");
			return null;
		}
		return self::sumXYFromStats($datax, $datay) / count($datax) -
			self::averageFromStats($datax) * self::averageFromStats($datay);
	}
	/**
	 * Variance Function
	 * Returns the population variance of an array.
	 * Non-numeric values are treated as zeroes.
	 * @param array $data An array of numeric values
	 * @return float The variance of the supplied array
	 * @static
	 */
	public static function varianceFromStats(array $data){
		return self::covarianceFromStats($data, $data);
	}
	/**
	 * Standard Deviation Function
	 * Returns the population standard deviation of an array.
	 * Non-numeric values are treated as zeroes.
	 * @param array $data An array of numeric values
	 * @return float The population standard deviation of the supplied array
	 * @static
	 */
	public static function stdDevFromStats(array $data): float{
		return sqrt(self::varianceFromStats($data));
	}
	/**
	 * @param array $causeValues
	 * @param array $effectValues
	 * @return float|int|null
	 */
	public static function testSpearman(array $causeValues, array $effectValues){
		if(count($causeValues) != count($effectValues)){
			le("spearman data arrays should be equal length!");
		}
		$relation1 = Stats::getTrivialRelationSpearman(count($causeValues));
		$relation2 = $relation1;
		array_multisort($causeValues, $relation1); #keeping index associations
		array_multisort($effectValues, $relation2);
		$ranking1 = Stats::getRankingSpearman($causeValues);
		$ranking2 = Stats::getRankingSpearman($effectValues);
		if(!$ranking1){
			le("spearman no ranking1");
		}
		if(!$ranking2){
			le("spearman no ranking2");
		}
		unset($ranking1[-1]);
		unset($ranking2[-1]);
		array_multisort($relation1, $ranking1); #Back to previous orders/relationships
		array_multisort($relation2, $ranking2);
		if(!isset($ranking1) || !isset($ranking2)){
			return null;
		}
		$distances2 = Stats::getDistances2Spearman($ranking1, $ranking2);
		$result = Stats::getCoefficientSpearman($distances2);
		if($result > 1){
			return 1;
		}
		if($result < -1){
			return -1;
		}
		return $result;
	}
	/**
	 * @param int $size
	 * @return array
	 */
	private static function getTrivialRelationSpearman(int $size): array{
		$relation = [];
		for($i = 0; $i < $size; $i++){
			$relation[] = $i;
		}
		return $relation;
	}
	/**
	 * @param $data
	 * @return array|null
	 */
	private static function getRankingSpearman(&$data): ?array{
		$ranking = [];
		$prevValue = '';
		$eqCount = 0;
		$eqSum = 0;
		$rankingPos = 1;
		foreach($data as $key => $value){
			if($value === ''){
				return null;
			}
			if($value != $prevValue){
				if($eqCount > 0){
					#Go back to set mean as ranking
					for($j = 0; $j <= $eqCount; $j++){
						$ranking[$rankingPos - 2 - $j] = $eqSum / ($eqCount + 1);
					}
				}
				$eqCount = 0;
				$eqSum = $rankingPos;
			} else{
				$eqCount++;
				$eqSum += $rankingPos;
			}
			#Keeping $data after sorting order
			$ranking[] = $rankingPos;
			$prevValue = $value;
			$rankingPos++;
		}
		#Go back to set mean as ranking in case last value has repetitions
		for($j = 0; $j <= $eqCount; $j++){
			$ranking[$rankingPos - 2 - $j] = $eqSum / ($eqCount + 1);
		}
		return $ranking;
	}
	/**
	 * @param $ranking1
	 * @param $ranking2
	 * @return array
	 */
	private static function getDistances2Spearman(&$ranking1, &$ranking2): array{
		$distances2 = [];
		for($key = 0, $keyMax = count($ranking1); $key < $keyMax; $key++){
			$distances2[] = ($ranking1[$key] - $ranking2[$key]) ** 2;
		}
		return $distances2;
	}
	/**
	 * @param $distances2
	 * @return float|int
	 */
	private static function getCoefficientSpearman(&$distances2){
		$size = count($distances2);
		$sum = 0;
		for($i = 0; $i < $size; $i++){
			$sum += $distances2[$i];
		}
		return 1 - (6 * $sum / (($size ** 3) - $size));
	}
	/**
	 * @param $arr
	 * @return mixed|null
	 */
	public static function mostCommonValue(array $arr){
		$arr = QMArr::removeNulls($arr);
		if(!$arr){
			return null;
		}
		$countArray = self::getMostCommonValueArray($arr);
		if(!count($countArray)){
			return null;
		}
		foreach($countArray as $key => $value){
			return $key;
		}
		le("This should never happen!");
	}
	/**
	 * @param $arr
	 * @return mixed|null
	 */
	public static function secondMostCommonValue(array $arr): ?float{
		$countArray = self::getMostCommonValueArray($arr);
		if(count($countArray) < 2){
			return null;
		}
		$i = 0;
		foreach($countArray as $key => $value){
			if($i === 1){
				return (float)$key;
			}
			$i++;
		}
		le("This should never happen!");
	}
	/**
	 * @param $arr
	 * @return mixed|null
	 */
	public static function thirdMostCommonValue(array $arr): ?float{
		$countArray = self::getMostCommonValueArray($arr);
		if(count($countArray) < 3){
			return null;
		}
		$i = 0;
		foreach($countArray as $key => $value){
			if($i === 2){
				return (float)$key;
			}
			$i++;
		}
		le("This should never happen!");
	}
	/**
	 * @param $arr
	 * @return array
	 */
	private static function getMostCommonValueArray(array $arr): array{
		$countArray = [];
		foreach($arr as $value){
			if(!isset($countArray[(string)$value])){
				$countArray[(string)$value] = 0;
			}
			$countArray[(string)$value]++;
		}
		krsort($countArray);
		return $countArray;
	}
	/**
	 * Fixes weird issue where < doesn't work on floats derived from strings
	 * @param $lesser
	 * @param $greater
	 * @return bool
	 */
	public static function lessThan($lesser, $greater): bool{
		try {
			if(self::floatsEqual($greater, $lesser)){
				return false;
			}
		} catch (\Throwable $e) {
			le("Could not floatsEqual " . \App\Logging\QMLog::print_r($greater, true) . " and " . \App\Logging\QMLog::print_r($lesser, true) . " because " .
				$e->getMessage());
			throw new \LogicException();
		}
		return $lesser < $greater;
	}
	/**
	 * Fixes weird issue where > doesn't work on floats derived from strings
	 * @param $lesser
	 * @param $greater
	 * @return bool
	 */
	public static function greaterThan($greater, $lesser): bool{
		if(self::floatsEqual($greater, $lesser)){
			return false;
		}
		return $greater > $lesser;
	}
	/**
	 * @param $n
	 * @param float $multiple
	 * @return float|int
	 */
	public static function roundToNearestMultipleOf(float $n, float $multiple): float{
		$nearest = round($n / $multiple) * $multiple;
		return $nearest;
	}
	/**
	 * Simple n-point moving average SMA
	 * The unweighted mean of the previous n data.
	 * First calculate initial average:
	 *  ⁿ⁻¹
	 *   ∑ xᵢ
	 *  ᵢ₌₀
	 * To calculating successive values, a new value comes into the sum and an old value drops out:
	 *  SMAtoday = SMAyesterday + NewNumber/N - DropNumber/N
	 * @param float[] $numbers
	 * @param int $n n-point moving average
	 * @return float[] of averages for each n-point time period
	 */
	public static function movingAverage(array $numbers, int $n): array{
		try {
			return Average::simpleMovingAverage($numbers, $n);
		} catch (\Throwable $e) {
			QMLog::info(__METHOD__.": ".$e->getMessage());
			return Average::simpleMovingAverage($numbers, $n);
		}
	}
	public static function roundDownToNearest(float $n, int $multiple): float{
		$nearest = floor($n / $multiple) * $multiple;
		return $nearest;
	}
	public static function roundUpToNearest(float $n, int $multiple): float{
		$nearest = ceil($n / $multiple) * $multiple;
		return $nearest;
	}
	public static function calculateCAGR(float $initial, float $final, $from, $to): float{
		$years = TimeHelper::yearsBetween($from, $to);
		$cagr = pow($final / $initial, 1 / $years) - 1;
		return $cagr * 100;
	}
	/** @noinspection PhpUnused */
	public static function dailyToCAGR(float $dailyPercentReturn): float{
		return (pow(1 + $dailyPercentReturn / 100, 365) - 1) * 100;
	}
	public static function calculateCompoundDailyGrowthRate(float $initial, float $final, $from, $to,
		int $precision = null): float{
		$days = TimeHelper::daysBetween($from, $to);
		$cagr = pow($final / $initial, 1 / $days) - 1;
		$val = $cagr * 100;
		if($precision){
			return Stats::roundByNumberOfSignificantDigits($val, $precision);
		}
		return $val;
	}
	public static function getRemainder(int $numerator, int $denominator): int{
		return $numerator % $denominator;
	}
	public static function floatsEqual(float $a, float $b): bool{
		$delta = 0.00001;
		return abs($a - $b) < $delta;
	}
	public static function areEqualFloats($a, $b): bool{
		if(!is_float($a)){
			return false;
		}
		if(!is_float($b)){
			return false;
		}
		return self::floatsEqual($a, $b);
	}
	public static function normalizeToMinMax(array $actual, float $desiredMin, float $desiredMax): array{
		$normalized = [];
		$actualMin = min($actual);
		$actualMax = max($actual);
		$desiredRange = $desiredMax - $desiredMin;
		$actualRange = $actualMax - $actualMin;
		foreach($actual as $value){
			if(!$actualRange){
				$normalized[] = $desiredRange / 2;
			} else{
				$normalized[] = $desiredRange / $actualRange * $value + $desiredMin - $actualMin;
			}
		}
		return $normalized;
	}
	public static function is_int($value): bool{
		return QMStr::isInt($value);
	}
}
