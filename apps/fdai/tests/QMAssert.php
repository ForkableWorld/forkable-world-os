<?php /** @noinspection PhpUnused */
namespace Tests;
use App\Exceptions\InvalidStringException;
use App\Exceptions\InvalidUrlException;
use App\Logging\QMLog;
use App\Types\QMStr;
use App\Types\TimeHelper;
use App\Utils\AppMode;
use App\Utils\DiffFile;
use ArrayAccess;
use BadMethodCallException;
use Countable;
use DateTime;
use DateTimeImmutable;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Collection;
use LogicException;
use PHPUnit\Framework\Assert;
use PHPUnit\Util\InvalidArgumentHelper;
use Psr\Log\InvalidArgumentException;
use SimpleXMLElement;
use Traversable;
class QMAssert extends Assert {
	private function __construct(){
	}
	public static function assertCount($expectedCount, $haystack, $message = ''): void{
		if($haystack === null){
			throw new LogicException("Nothing provided to " . __FUNCTION__ . "!  Message: $message");
		}
		parent::assertCount($expectedCount, $haystack, $message);
	}
	/**
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public static function assert404FromExternalUrl(string $url, array $options = ['allow_redirects' => false]){
		$client = new Client();
		try {
			$g = $client->request('GET', $url, $options);
			throw new LogicException("Should have gotten 404 from $url but got " . \App\Logging\QMLog::print_r($g, true));
		} catch (ClientException $e) {
			if($e->getCode() !== 404){
				throw $e;
			}
			QMLog::debug(__METHOD__.": ".$e->getMessage());
		}
	}
	/**
	 * @param mixed $expected
	 * @param mixed $actual
	 * @param string $message
	 */
	public static function assertEqualsOrGenerateDiff($expected, $actual, string $message): void{
		if(!is_string($expected)){
			$expected = QMStr::prettyJsonEncode($expected);
		}
		if(!is_string($actual)){
			$actual = QMStr::prettyJsonEncode($actual);
		}
		if(QMStr::trimWhitespaceAndLineBreaks($expected) !== QMStr::trimWhitespaceAndLineBreaks($actual)){
			$diff = DiffFile::generateDiffUrl($expected, $actual, AppMode::getCurrentTestName());
			le("See html diff at $diff
            $message");
		}
	}
	/**
	 * Asserts the number of elements of an array, Countable or Traversable.
	 * @param int $expectedCount
	 * @param iterable|Collection|array $haystack
	 * @param string $message
	 */
	public static function assertCountGreaterThan(int $expectedCount, $haystack, string $message = ''): void{
		if(!$haystack instanceof Countable && !is_iterable($haystack)){
			throw InvalidArgumentHelper::factory(2, 'countable or iterable');
		}
		$actual = count($haystack);
		if($actual <= $expectedCount){
			$message = "$message
            Should have more than $expectedCount but actually have $actual: ";
			if($haystack){
				$message .= QMLog::table($haystack);
			}
			le($message);
		}
	}
	/**
	 * @param string $haystack
	 * @param $blackList
	 * @param string $type
	 * @param bool $ignoreCase
	 * @param string|null $assertionMessage
	 * @throws InvalidStringException
	 */
	public static function assertStringDoesNotContain(string $haystack, $blackList, string $type,
	                                                  bool $ignoreCase = false, string $assertionMessage = null){
		QMStr::assertStringDoesNotContain($haystack, $blackList, $type, $ignoreCase, $assertionMessage);
	}
	/**
	 * @param $expected
	 * @param $actual
	 * @param string $message
	 * Can compare different date formats
	 * @param float $delta
	 * @param int $maxDepth
	 * @param bool $canonicalize
	 * @param bool $ignoreCase
	 */
	public static function assertSimilar($expected, $actual, string $message = '', float $delta = 0.0,
	                                     int $maxDepth = 10, bool $canonicalize = false, bool $ignoreCase = false){
		if(TimeHelper::isCarbon($expected) || TimeHelper::isCarbon($actual)){
			self::assertDateEquals($expected, $actual, 'expected', 'actual', $message);
		}
		try {
			if(is_numeric($expected) && is_numeric($actual) && (int)$expected === (int)$actual){
				return;
			}
			parent::assertEquals($expected, $actual, $message, $delta, $maxDepth, $canonicalize, $ignoreCase);
		} catch (\Throwable $e) {
			le(__METHOD__.": ".$e->getMessage());
		}
	}
	/**
	 * Asserts that two variables are equal.
	 * @param $expected
	 * @param $actual
	 * @param string|null $expectedName
	 * @param string|null $actualName
	 * @param string $message
	 */
	public static function assertDateEquals($expected, $actual, string $expectedName = null, string $actualName = null,
	                                        string $message = ''): void{
		$res = self::dateEquals($expected, $actual, $expectedName, $actualName);
		if(!$actualName){
			$params = QMBaseTestCase::getArgumentNamesFromFunctionCall();
			$expectedName = $params[0];
			$actualName = $params[1];
		}
		$expectedDate = db_date($expected);
		$actualDate = db_date($actual);
		$message .= "\n$actualName\n\t$actualDate should equal $expectedName\n\t$expectedDate";
		if(!$res){
			le($message);
		}
	}
	/**
	 * @param $expected
	 * @param $actual
	 * @param string|null $expectedName
	 * @param string|null $actualName
	 * @return bool
	 */
	public static function dateEquals($expected, $actual, string $expectedName = null, string $actualName = null): bool{
		return TimeHelper::dateEquals($expected, $actual, $expectedName, $actualName);
	}
	/**
	 * @param string $url
	 * @param string $type
	 * @throws InvalidUrlException
	 */
	public static function assertValidUrl(string $url, string $type){ self::validateUrl($url, $type); }
	/**
	 * @param string $url
	 * @param string $type
	 * @throws InvalidUrlException
	 */
	public static function validateUrl(string $url, string $type){
		QMStr::assertIsUrl($url, $type);
	}
	/**
	 * @psalm-pure
	 * @psalm-assert non-empty-string $value
	 * @param mixed $value
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function stringNotEmpty($value, string $message = ''){
		static::string($value, $message);
		static::notEq($value, '', $message);
	}
	/**
	 * @psalm-pure
	 * @psalm-assert string $value
	 * @param mixed $value
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function string($value, string $message = ''){
		if(!\is_string($value)){
			static::reportInvalidArgument(\sprintf($message ?: 'Expected a string. Got: %s',
			                                       static::typeToString($value)));
		}
	}
	/**
	 * @param string $message
	 * @throws InvalidArgumentException
	 * @psalm-pure this method is not supposed to perform side-effects
	 */
	protected static function reportInvalidArgument(string $message){
		throw new InvalidArgumentException($message);
	}
	/**
	 * @param mixed $value
	 * @return string
	 */
	protected static function typeToString($value): string{
		return \is_object($value) ? \get_class($value) : \gettype($value);
	}
	/**
	 * @param mixed $value
	 * @param mixed $expect
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function notEq($value, $expect, string $message = ''){
		if($expect == $value){
			static::reportInvalidArgument(\sprintf($message ?: 'Expected a different value than %s.',
			                                       static::valueToString($expect)));
		}
	}
	/**
	 * @param mixed $value
	 * @return string
	 */
	protected static function valueToString($value): string{
		if(null === $value){
			return 'null';
		}
		if(true === $value){
			return 'true';
		}
		if(false === $value){
			return 'false';
		}
		if(\is_array($value)){
			return 'array';
		}
		if(\is_object($value)){
			if(\method_exists($value, '__toString')){
				return \get_class($value) . ': ' . self::valueToString($value->__toString());
			}
			if($value instanceof DateTime || $value instanceof DateTimeImmutable){
				return \get_class($value) . ': ' . self::valueToString($value->format('c'));
			}
			return \get_class($value);
		}
		if(\is_resource($value)){
			return 'resource';
		}
		if(\is_string($value)){
			return '"' . $value . '"';
		}
		return (string)$value;
	}
	/**
	 * @psalm-pure
	 * @psalm-assert int $value
	 * @param mixed $value
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function integer($value, string $message = ''){
		if(!\is_int($value)){
			static::reportInvalidArgument(\sprintf($message ?: 'Expected an integer. Got: %s',
			                                       static::typeToString($value)));
		}
	}
	/**
	 * @psalm-pure
	 * @psalm-assert numeric $value
	 * @param mixed $value
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function integerish($value, string $message = ''){
		if(!\is_numeric($value) || $value != (int)$value){
			static::reportInvalidArgument(\sprintf($message ?: 'Expected an integerish value. Got: %s',
			                                       static::typeToString($value)));
		}
	}
	/**
	 * @psalm-pure
	 * @psalm-assert float $value
	 * @param mixed $value
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function float($value, string $message = ''){
		if(!\is_float($value)){
			static::reportInvalidArgument(\sprintf($message ?: 'Expected a float. Got: %s',
			                                       static::typeToString($value)));
		}
	}
	/**
	 * @psalm-pure
	 * @psalm-assert numeric $value
	 * @param mixed $value
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function numeric($value, string $message = ''){
		if(!\is_numeric($value)){
			static::reportInvalidArgument(\sprintf($message ?: 'Expected a numeric. Got: %s',
			                                       static::typeToString($value)));
		}
	}
	/**
	 * @psalm-pure
	 * @psalm-assert int $value
	 * @param mixed $value
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function natural($value, string $message = ''){
		if(!\is_int($value) || $value < 0){
			static::reportInvalidArgument(\sprintf($message ?: 'Expected a non-negative integer. Got: %s',
			                                       static::valueToString($value)));
		}
	}
	/**
	 * @psalm-pure
	 * @psalm-assert bool $value
	 * @param mixed $value
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function boolean($value, string $message = ''){
		if(!\is_bool($value)){
			static::reportInvalidArgument(\sprintf($message ?: 'Expected a boolean. Got: %s',
			                                       static::typeToString($value)));
		}
	}
	/**
	 * @psalm-pure
	 * @psalm-assert scalar $value
	 * @param mixed $value
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function scalar($value, string $message = ''){
		if(!\is_scalar($value)){
			static::reportInvalidArgument(\sprintf($message ?: 'Expected a scalar. Got: %s',
			                                       static::typeToString($value)));
		}
	}
	/**
	 * @psalm-pure
	 * @psalm-assert object $value
	 * @param mixed $value
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function object($value, string $message = ''){
		if(!\is_object($value)){
			static::reportInvalidArgument(\sprintf($message ?: 'Expected an object. Got: %s',
			                                       static::typeToString($value)));
		}
	}
	/**
	 * @psalm-pure
	 * @psalm-assert resource $value
	 * @param mixed $value
	 * @param string|null $type type of resource this should be. @see
	 *     https://www.php.net/manual/en/function.get-resource-type.php
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function resource($value, string $type = null, string $message = ''){
		if(!\is_resource($value)){
			static::reportInvalidArgument(\sprintf($message ?: 'Expected a resource. Got: %s',
			                                       static::typeToString($value)));
		}
		if($type && $type !== \get_resource_type($value)){
			static::reportInvalidArgument(\sprintf($message ?: 'Expected a resource of type %2$s. Got: %s',
			                                       static::typeToString($value), $type));
		}
	}
	/**
	 * @psalm-pure
	 * @psalm-assert callable $value
	 * @param mixed $value
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function isCallable($value, string $message = ''){
		if(!\is_callable($value)){
			static::reportInvalidArgument(\sprintf($message ?: 'Expected a callable. Got: %s',
			                                       static::typeToString($value)));
		}
	}
	/**
	 * @psalm-pure
	 * @psalm-assert array $value
	 * @param mixed $value
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function isArray($value, string $message = ''){
		if(!\is_array($value)){
			static::reportInvalidArgument(\sprintf($message ?: 'Expected an array. Got: %s',
			                                       static::typeToString($value)));
		}
	}
	/**
	 * @psalm-pure
	 * @psalm-assert iterable $value
	 * @param mixed $value
	 * @param string $message
	 * @throws InvalidArgumentException
	 * @deprecated use "isIterable" or "isInstanceOf" instead
	 */
	public static function isTraversable($value, string $message = ''){
		@\trigger_error(\sprintf('The "%s" assertion is deprecated. You should stop using it, as it will soon be removed in 2.0 version. Use "isIterable" or "isInstanceOf" instead.',
		                         __METHOD__), \E_USER_DEPRECATED);
		if(!\is_array($value) && !($value instanceof Traversable)){
			static::reportInvalidArgument(\sprintf($message ?: 'Expected a traversable. Got: %s',
			                                       static::typeToString($value)));
		}
	}
	/**
	 * @psalm-pure
	 * @psalm-assert array|ArrayAccess $value
	 * @param mixed $value
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function isArrayAccessible($value, string $message = ''){
		if(!\is_array($value) && !($value instanceof ArrayAccess)){
			static::reportInvalidArgument(\sprintf($message ?: 'Expected an array accessible. Got: %s',
			                                       static::typeToString($value)));
		}
	}
	/**
	 * @psalm-pure
	 * @psalm-assert countable $value
	 * @param mixed $value
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function isCountable($value, string $message = ''){
		if(!\is_array($value) && !($value instanceof Countable) && !($value instanceof SimpleXMLElement)){
			static::reportInvalidArgument(\sprintf($message ?: 'Expected a countable. Got: %s',
			                                       static::typeToString($value)));
		}
	}
	/**
	 * @psalm-pure
	 * @psalm-template ExpectedType of object
	 * @psalm-param class-string<ExpectedType> $class
	 * @psalm-assert !ExpectedType $value
	 * @param mixed $value
	 * @param string|object $class
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function notInstanceOf($value, $class, string $message = ''){
		if($value instanceof $class){
			static::reportInvalidArgument(\sprintf($message ?: 'Expected an instance other than %2$s. Got: %s',
			                                       static::typeToString($value), $class));
		}
	}
	/**
	 * @psalm-pure
	 * @psalm-param array<class-string> $classes
	 * @param mixed $value
	 * @param array<object|string> $classes
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function isInstanceOfAny($value, array $classes, string $message = ''){
		foreach($classes as $class){
			if($value instanceof $class){
				return;
			}
		}
		static::reportInvalidArgument(\sprintf($message ?: 'Expected an instance of any of %2$s. Got: %s',
		                                       static::typeToString($value),
		                                       \implode(', ', \array_map(['static', 'valueToString'], $classes))));
	}
	/**
	 * @psalm-pure
	 * @psalm-template ExpectedType of object
	 * @psalm-param class-string<ExpectedType> $class
	 * @psalm-assert ExpectedType|class-string<ExpectedType> $value
	 * @param object|string $value
	 * @param string $class
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function isAOf($value, string $class, string $message = ''){
		static::string($class, 'Expected class as a string. Got: %s');
		if(!\is_a($value, $class, \is_string($value))){
			static::reportInvalidArgument(sprintf($message ?: 'Expected an instance of this class or to this class among his parents %2$s. Got: %s',
			                                      static::typeToString($value), $class));
		}
	}
	/**
	 * @psalm-pure
	 * @psalm-template UnexpectedType of object
	 * @psalm-param class-string<UnexpectedType> $class
	 * @psalm-assert !UnexpectedType $value
	 * @psalm-assert !class-string<UnexpectedType> $value
	 * @param object|string $value
	 * @param string $class
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function isNotA($value, string $class, string $message = ''){
		static::string($class, 'Expected class as a string. Got: %s');
		if(\is_a($value, $class, \is_string($value))){
			static::reportInvalidArgument(sprintf($message ?: 'Expected an instance of this class or to this class among his parents other than %2$s. Got: %s',
			                                      static::typeToString($value), $class));
		}
	}
	/**
	 * @psalm-pure
	 * @psalm-param array<class-string> $classes
	 * @param object|string $value
	 * @param string[] $classes
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function isAnyOf($value, array $classes, string $message = ''){
		foreach($classes as $class){
			static::string($class, 'Expected class as a string. Got: %s');
			if(\is_a($value, $class, \is_string($value))){
				return;
			}
		}
		static::reportInvalidArgument(sprintf($message ?: 'Expected an any of instance of this class or to this class among his parents other than %2$s. Got: %s',
		                                      static::typeToString($value),
		                                      \implode(', ', \array_map(['static', 'valueToString'], $classes))));
	}
	/**
	 * @psalm-pure
	 * @psalm-assert null $value
	 * @param mixed $value
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function null($value, string $message = ''){
		if(null !== $value){
			static::reportInvalidArgument(\sprintf($message ?: 'Expected null. Got: %s',
			                                       static::valueToString($value)));
		}
	}
	/**
	 * @psalm-pure
	 * @psalm-assert !null $value
	 * @param mixed $value
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function notNull($value, string $message = ''){
		if(null === $value){
			static::reportInvalidArgument($message ?: 'Expected a value other than null.');
		}
	}
	/**
	 * @psalm-pure
	 * @psalm-assert true $value
	 * @param mixed $value
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function true($value, string $message = ''){
		if(true !== $value){
			static::reportInvalidArgument(\sprintf($message ?: 'Expected a value to be true. Got: %s',
			                                       static::valueToString($value)));
		}
	}
	/**
	 * @psalm-pure
	 * @psalm-assert false $value
	 * @param mixed $value
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function false($value, string $message = ''){
		if(false !== $value){
			static::reportInvalidArgument(\sprintf($message ?: 'Expected a value to be false. Got: %s',
			                                       static::valueToString($value)));
		}
	}
	/**
	 * @psalm-pure
	 * @psalm-assert !false $value
	 * @param mixed $value
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function notFalse($value, string $message = ''){
		if(false === $value){
			static::reportInvalidArgument($message ?: 'Expected a value other than false.');
		}
	}
	/**
	 * @param mixed $value
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function ip($value, string $message = ''){
		if(false === \filter_var($value, \FILTER_VALIDATE_IP)){
			static::reportInvalidArgument(\sprintf($message ?: 'Expected a value to be an IP. Got: %s',
			                                       static::valueToString($value)));
		}
	}
	/**
	 * @param mixed $value
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function ipv4($value, string $message = ''){
		if(false === \filter_var($value, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV4)){
			static::reportInvalidArgument(\sprintf($message ?: 'Expected a value to be an IPv4. Got: %s',
			                                       static::valueToString($value)));
		}
	}
	/**
	 * @param mixed $value
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function ipv6($value, string $message = ''){
		if(false === \filter_var($value, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV6)){
			static::reportInvalidArgument(\sprintf($message ?: 'Expected a value to be an IPv6. Got: %s',
			                                       static::valueToString($value)));
		}
	}
	/**
	 * @param mixed $value
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function email($value, string $message = ''){
		if(false === \filter_var($value, FILTER_VALIDATE_EMAIL)){
			static::reportInvalidArgument(\sprintf($message ?: 'Expected a value to be a valid e-mail address. Got: %s',
			                                       static::valueToString($value)));
		}
	}
	/**
	 * Does non strict comparisons on the items, so ['3', 3] will not pass the assertion.
	 * @param array $values
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function uniqueValues(array $values, string $message = ''){
		$allValues = \count($values);
		$uniqueValues = \count(\array_unique($values));
		if($allValues !== $uniqueValues){
			$difference = $allValues - $uniqueValues;
			static::reportInvalidArgument(\sprintf($message ?: 'Expected an array of unique values, but %s of them %s duplicated',
			                                       $difference, (1 === $difference ? 'is' : 'are')));
		}
	}
	/**
	 * @psalm-pure
	 * @param mixed $value
	 * @param mixed $expect
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function same($value, $expect, string $message = ''){
		if($expect !== $value){
			static::reportInvalidArgument(\sprintf($message ?: 'Expected a value identical to %2$s. Got: %s',
			                                       static::valueToString($value), static::valueToString($expect)));
		}
	}
	/**
	 * @psalm-pure
	 * @param mixed $value
	 * @param mixed $expect
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function notSame($value, $expect, string $message = ''){
		if($expect === $value){
			static::reportInvalidArgument(\sprintf($message ?: 'Expected a value not identical to %s.',
			                                       static::valueToString($expect)));
		}
	}
	/**
	 * @psalm-pure
	 * @param mixed $value
	 * @param mixed $limit
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function greaterThanEq($value, $limit, string $message = ''){
		if($value < $limit){
			static::reportInvalidArgument(\sprintf($message ?: 'Expected a value greater than or equal to %2$s. Got: %s',
			                                       static::valueToString($value), static::valueToString($limit)));
		}
	}
	/**
	 * @psalm-pure
	 * @param mixed $value
	 * @param mixed $limit
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function lessThanEq($value, $limit, string $message = ''){
		if($value > $limit){
			static::reportInvalidArgument(\sprintf($message ?: 'Expected a value less than or equal to %2$s. Got: %s',
			                                       static::valueToString($value), static::valueToString($limit)));
		}
	}
	/**
	 * Inclusive range, so Assert::(3, 3, 5) passes.
	 * @psalm-pure
	 * @param mixed $value
	 * @param mixed $min
	 * @param mixed $max
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function range($value, $min, $max, string $message = ''){
		if($value < $min || $value > $max){
			static::reportInvalidArgument(\sprintf($message ?: 'Expected a value between %2$s and %3$s. Got: %s',
			                                       static::valueToString($value), static::valueToString($min),
			                                       static::valueToString($max)));
		}
	}
	/**
	 * A more human-readable alias of Assert::inArray().
	 * @psalm-pure
	 * @param mixed $value
	 * @param array $values
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function oneOf($value, array $values, string $message = ''){
		static::inArray($value, $values, $message);
	}
	/**
	 * Does strict comparison, so Assert::inArray(3, ['3']) does not pass the assertion.
	 * @psalm-pure
	 * @param mixed $value
	 * @param array $values
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function inArray($value, array $values, string $message = ''){
		if(!\in_array($value, $values, true)){
			static::reportInvalidArgument(\sprintf($message ?: 'Expected one of: %2$s. Got: %s',
			                                       static::valueToString($value),
			                                       \implode(', ', \array_map(['static', 'valueToString'], $values))));
		}
	}
	/**
	 * @psalm-pure
	 * @param string $value
	 * @param string $subString
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function notContains(string $value, string $subString, string $message = ''){
		if(false !== \strpos($value, $subString)){
			static::reportInvalidArgument(\sprintf($message ?: '%2$s was not expected to be contained in a value. Got: %s',
			                                       static::valueToString($value), static::valueToString($subString)));
		}
	}
	/**
	 * @psalm-pure
	 * @param string $value
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function notWhitespaceOnly(string $value, string $message = ''){
		if(\preg_match('/^\s*$/', $value)){
			static::reportInvalidArgument(\sprintf($message ?: 'Expected a non-whitespace string. Got: %s',
			                                       static::valueToString($value)));
		}
	}
	/**
	 * @psalm-pure
	 * @param string $value
	 * @param string $prefix
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function startsWith(string $value, string $prefix, string $message = ''){
		if(0 !== \strpos($value, $prefix)){
			static::reportInvalidArgument(\sprintf($message ?: 'Expected a value to start with %2$s. Got: %s',
			                                       static::valueToString($value), static::valueToString($prefix)));
		}
	}
	/**
	 * @psalm-pure
	 * @param string $value
	 * @param string $prefix
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function notStartsWith(string $value, string $prefix, string $message = ''){
		if(0 === \strpos($value, $prefix)){
			static::reportInvalidArgument(\sprintf($message ?: 'Expected a value not to start with %2$s. Got: %s',
			                                       static::valueToString($value), static::valueToString($prefix)));
		}
	}
	/**
	 * @psalm-pure
	 * @param mixed $value
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function startsWithLetter($value, string $message = ''){
		static::string($value);
		$valid = isset($value[0]);
		if($valid){
			$locale = \setlocale(LC_CTYPE, 0);
			\setlocale(LC_CTYPE, 'C');
			$valid = \ctype_alpha($value[0]);
			\setlocale(LC_CTYPE, $locale);
		}
		if(!$valid){
			static::reportInvalidArgument(\sprintf($message ?: 'Expected a value to start with a letter. Got: %s',
			                                       static::valueToString($value)));
		}
	}
	/**
	 * @psalm-pure
	 * @param string $value
	 * @param string $suffix
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function endsWith(string $value, string $suffix, string $message = ''){
		if($suffix !== \substr($value, -\strlen($suffix))){
			static::reportInvalidArgument(\sprintf($message ?: 'Expected a value to end with %2$s. Got: %s',
			                                       static::valueToString($value), static::valueToString($suffix)));
		}
	}
	/**
	 * @psalm-pure
	 * @param string $value
	 * @param string $suffix
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function notEndsWith(string $value, string $suffix, string $message = ''){
		if($suffix === \substr($value, -\strlen($suffix))){
			static::reportInvalidArgument(\sprintf($message ?: 'Expected a value not to end with %2$s. Got: %s',
			                                       static::valueToString($value), static::valueToString($suffix)));
		}
	}
	/**
	 * @psalm-pure
	 * @param string $value
	 * @param string $pattern
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function regex(string $value, string $pattern, string $message = ''){
		if(!\preg_match($pattern, $value)){
			static::reportInvalidArgument(\sprintf($message ?: 'The value %s does not match the expected pattern.',
			                                       static::valueToString($value)));
		}
	}
	/**
	 * @psalm-pure
	 * @param string $value
	 * @param string $pattern
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function notRegex(string $value, string $pattern, string $message = ''){
		if(\preg_match($pattern, $value, $matches, PREG_OFFSET_CAPTURE)){
			static::reportInvalidArgument(\sprintf($message ?: 'The value %s matches the pattern %s (at offset %d).',
			                                       static::valueToString($value), static::valueToString($pattern),
			                                       $matches[0][1]));
		}
	}
	/**
	 * @psalm-pure
	 * @param mixed $value
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function unicodeLetters($value, string $message = ''){
		static::string($value);
		if(!\preg_match('/^\p{L}+$/u', $value)){
			static::reportInvalidArgument(\sprintf($message ?: 'Expected a value to contain only Unicode letters. Got: %s',
			                                       static::valueToString($value)));
		}
	}
	/**
	 * @psalm-pure
	 * @param mixed $value
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function alpha($value, string $message = ''){
		static::string($value);
		$locale = \setlocale(LC_CTYPE, 0);
		\setlocale(LC_CTYPE, 'C');
		$valid = !\ctype_alpha($value);
		\setlocale(LC_CTYPE, $locale);
		if($valid){
			static::reportInvalidArgument(\sprintf($message ?: 'Expected a value to contain only letters. Got: %s',
			                                       static::valueToString($value)));
		}
	}
	/**
	 * @psalm-pure
	 * @param string $value
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function digits(string $value, string $message = ''){
		$locale = \setlocale(LC_CTYPE, 0);
		\setlocale(LC_CTYPE, 'C');
		$valid = !\ctype_digit($value);
		\setlocale(LC_CTYPE, $locale);
		if($valid){
			static::reportInvalidArgument(\sprintf($message ?: 'Expected a value to contain digits only. Got: %s',
			                                       static::valueToString($value)));
		}
	}
	/**
	 * @psalm-pure
	 * @param string $value
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function alnum(string $value, string $message = ''){
		$locale = \setlocale(LC_CTYPE, 0);
		\setlocale(LC_CTYPE, 'C');
		$valid = !\ctype_alnum($value);
		\setlocale(LC_CTYPE, $locale);
		if($valid){
			static::reportInvalidArgument(\sprintf($message ?: 'Expected a value to contain letters and digits only. Got: %s',
			                                       static::valueToString($value)));
		}
	}
	/**
	 * @psalm-pure
	 * @psalm-assert lowercase-string $value
	 * @param string $value
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function lower(string $value, string $message = ''){
		$locale = \setlocale(LC_CTYPE, 0);
		\setlocale(LC_CTYPE, 'C');
		$valid = !\ctype_lower($value);
		\setlocale(LC_CTYPE, $locale);
		if($valid){
			static::reportInvalidArgument(\sprintf($message ?: 'Expected a value to contain lowercase characters only. Got: %s',
			                                       static::valueToString($value)));
		}
	}
	/**
	 * @psalm-pure
	 * @psalm-assert !lowercase-string $value
	 * @param string $value
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function upper(string $value, string $message = ''){
		$locale = \setlocale(LC_CTYPE, 0);
		\setlocale(LC_CTYPE, 'C');
		$valid = !\ctype_upper($value);
		\setlocale(LC_CTYPE, $locale);
		if($valid){
			static::reportInvalidArgument(\sprintf($message ?: 'Expected a value to contain uppercase characters only. Got: %s',
			                                       static::valueToString($value)));
		}
	}
	/**
	 * @psalm-pure
	 * @param string $value
	 * @param int $length
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function length(string $value, int $length, string $message = ''){
		if($length !== static::strlen($value)){
			static::reportInvalidArgument(\sprintf($message ?: 'Expected a value to contain %2$s characters. Got: %s',
			                                       static::valueToString($value), $length));
		}
	}
	protected static function strlen($value){
		if(!\function_exists('mb_detect_encoding')){
			return \strlen($value);
		}
		if(false === $encoding = \mb_detect_encoding($value)){
			return \strlen($value);
		}
		return \mb_strlen($value, $encoding);
	}
	/**
	 * Inclusive min.
	 * @psalm-pure
	 * @param string $value
	 * @param int|float $min
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function minLength(string $value, $min, string $message = ''){
		if(static::strlen($value) < $min){
			static::reportInvalidArgument(\sprintf($message ?: 'Expected a value to contain at least %2$s characters. Got: %s',
			                                       static::valueToString($value), $min));
		}
	}
	/**
	 * Inclusive max.
	 * @psalm-pure
	 * @param string $value
	 * @param int|float $max
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function maxLength(string $value, $max, string $message = ''){
		if(static::strlen($value) > $max){
			static::reportInvalidArgument(\sprintf($message ?: 'Expected a value to contain at most %2$s characters. Got: %s',
			                                       static::valueToString($value), $max));
		}
	}
	/**
	 * Inclusive , so Assert::lengthBetween('asd', 3, 5); passes the assertion.
	 * @psalm-pure
	 * @param string $value
	 * @param int|float $min
	 * @param int|float $max
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function lengthBetween(string $value, $min, $max, string $message = ''){
		$length = static::strlen($value);
		if($length < $min || $length > $max){
			static::reportInvalidArgument(\sprintf($message ?: 'Expected a value to contain between %2$s and %3$s characters. Got: %s',
			                                       static::valueToString($value), $min, $max));
		}
	}
	/**
	 * @param string $value
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function readable(string $value, string $message = ''){
		if(!\is_readable($value)){
			static::reportInvalidArgument(\sprintf($message ?: 'The path %s is not readable.',
			                                       static::valueToString($value)));
		}
	}
	/**
	 * @param $contents
	 * @param string $message
	 */
	public static function validJson($contents, string $message = ''){
		$decoded = json_decode($contents);
		if(empty($decoded)){
			static::reportInvalidArgument(\sprintf($message ?: 'The response is not valid json.',
				static::valueToString($contents)));
		}
	}
	/**
	 * @param string $value
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function writable(string $value, string $message = ''){
		if(!\is_writable($value)){
			static::reportInvalidArgument(\sprintf($message ?: 'The path %s is not writable.',
			                                       static::valueToString($value)));
		}
	}
	/**
	 * @psalm-assert class-string $value
	 * @param mixed $value
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function classExists($value, string $message = ''){
		if(!\class_exists($value)){
			static::reportInvalidArgument(\sprintf($message ?: 'Expected an existing class name. Got: %s',
			                                       static::valueToString($value)));
		}
	}
	/**
	 * @psalm-pure
	 * @psalm-template ExpectedType of object
	 * @psalm-param class-string<ExpectedType> $class
	 * @psalm-assert class-string<ExpectedType>|ExpectedType $value
	 * @param mixed $value
	 * @param string|object $class
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function subclassOf($value, $class, string $message = ''){
		if(!\is_subclass_of($value, $class)){
			static::reportInvalidArgument(\sprintf($message ?: 'Expected a sub-class of %2$s. Got: %s',
			                                       static::valueToString($value), static::valueToString($class)));
		}
	}
	/**
	 * @psalm-assert class-string $value
	 * @param mixed $value
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function interfaceExists($value, string $message = ''){
		if(!\interface_exists($value)){
			static::reportInvalidArgument(\sprintf($message ?: 'Expected an existing interface name. got %s',
			                                       static::valueToString($value)));
		}
	}
	/**
	 * @psalm-pure
	 * @psalm-template ExpectedType of object
	 * @psalm-param class-string<ExpectedType> $interface
	 * @psalm-assert class-string<ExpectedType> $value
	 * @param mixed $value
	 * @param mixed $interface
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function implementsInterface($value, $interface, string $message = ''){
		if(!\in_array($interface, \class_implements($value))){
			static::reportInvalidArgument(\sprintf($message ?: 'Expected an implementation of %2$s. Got: %s',
			                                       static::valueToString($value), static::valueToString($interface)));
		}
	}
	/**
	 * @psalm-pure
	 * @psalm-param class-string|object $classOrObject
	 * @param string|object $classOrObject
	 * @param mixed $property
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function propertyExists($classOrObject, $property, string $message = ''){
		if(!\property_exists($classOrObject, $property)){
			static::reportInvalidArgument(\sprintf($message ?: 'Expected the property %s to exist.',
			                                       static::valueToString($property)));
		}
	}
	/**
	 * @psalm-pure
	 * @psalm-param class-string|object $classOrObject
	 * @param string|object $classOrObject
	 * @param mixed $property
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function propertyNotExists($classOrObject, $property, string $message = ''){
		if(\property_exists($classOrObject, $property)){
			static::reportInvalidArgument(\sprintf($message ?: 'Expected the property %s to not exist.',
			                                       static::valueToString($property)));
		}
	}
	/**
	 * @psalm-pure
	 * @psalm-param class-string|object $classOrObject
	 * @param string|object $classOrObject
	 * @param mixed $method
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function methodExists($classOrObject, $method, string $message = ''){
		if(!(\is_string($classOrObject) || \is_object($classOrObject)) || !\method_exists($classOrObject, $method)){
			static::reportInvalidArgument(\sprintf($message ?: 'Expected the method %s to exist.',
			                                       static::valueToString($method)));
		}
	}
	/**
	 * @psalm-pure
	 * @psalm-param class-string|object $classOrObject
	 * @param string|object $classOrObject
	 * @param mixed $method
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function methodNotExists($classOrObject, $method, string $message = ''){
		if((\is_string($classOrObject) || \is_object($classOrObject)) && \method_exists($classOrObject, $method)){
			static::reportInvalidArgument(\sprintf($message ?: 'Expected the method %s to not exist.',
			                                       static::valueToString($method)));
		}
	}
	/**
	 * @psalm-pure
	 * @param array $array
	 * @param string|int $key
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function keyExists(array $array, $key, string $message = ''){
		if(!(isset($array[$key]) || \array_key_exists($key, $array))){
			static::reportInvalidArgument(\sprintf($message ?: 'Expected the key %s to exist.',
			                                       static::valueToString($key)));
		}
	}
	/**
	 * @psalm-pure
	 * @param array $array
	 * @param string|int $key
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function keyNotExists(array $array, $key, string $message = ''){
		if(isset($array[$key]) || \array_key_exists($key, $array)){
			static::reportInvalidArgument(\sprintf($message ?: 'Expected the key %s to not exist.',
			                                       static::valueToString($key)));
		}
	}
	/**
	 * Checks if a value is a valid array key (int or string).
	 * @psalm-pure
	 * @psalm-assert array-key $value
	 * @param mixed $value
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function validArrayKey($value, string $message = ''){
		if(!(\is_int($value) || \is_string($value))){
			static::reportInvalidArgument(\sprintf($message ?: 'Expected string or integer. Got: %s',
			                                       static::typeToString($value)));
		}
	}
	/**
	 * Does not check if $array is countable, this can generate a warning on php versions after 7.2.
	 * @param Countable|array $array
	 * @param int $number
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function count($array, int $number, string $message = ''){
		static::eq(\count($array), $number,
		           \sprintf($message ?: 'Expected an array to contain %d elements. Got: %d.', $number, \count($array)));
	}
	/**
	 * @param mixed $value
	 * @param mixed $expect
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function eq($value, $expect, string $message = ''){
		if($expect != $value){
			static::reportInvalidArgument(\sprintf($message ?: 'Expected a value equal to %2$s. Got: %s',
			                                       static::valueToString($value), static::valueToString($expect)));
		}
	}
	/**
	 * Does not check if $array is countable, this can generate a warning on php versions after 7.2.
	 * @param Countable|array $array
	 * @param int|float $min
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function minCount($array, $min, string $message = ''){
		if(\count($array) < $min){
			static::reportInvalidArgument(\sprintf($message ?: 'Expected an array to contain at least %2$d elements. Got: %d',
			                                       \count($array), $min));
		}
	}
	/**
	 * Does not check if $array is countable, this can generate a warning on php versions after 7.2.
	 * @param Countable|array $array
	 * @param int|float $max
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function maxCount($array, $max, string $message = ''){
		if(\count($array) > $max){
			static::reportInvalidArgument(\sprintf($message ?: 'Expected an array to contain at most %2$d elements. Got: %d',
			                                       \count($array), $max));
		}
	}
	/**
	 * Does not check if $array is countable, this can generate a warning on php versions after 7.2.
	 * @param Countable|array $array
	 * @param int|float $min
	 * @param int|float $max
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function countBetween($array, $min, $max, string $message = ''){
		$count = \count($array);
		if($count < $min || $count > $max){
			static::reportInvalidArgument(\sprintf($message ?: 'Expected an array to contain between %2$d and %3$d elements. Got: %d',
			                                       $count, $min, $max));
		}
	}
	/**
	 * @psalm-pure
	 * @psalm-assert non-empty-list $array
	 * @param mixed $array
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function isNonEmptyList($array, string $message = ''){
		static::isList($array, $message);
		static::notEmpty($array, $message);
	}
	/**
	 * @psalm-pure
	 * @psalm-assert list $array
	 * @param mixed $array
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function isList($array, string $message = ''){
		if(!\is_array($array) || $array !== \array_values($array)){
			static::reportInvalidArgument($message ?: 'Expected list - non-associative array.');
		}
	}
	/**
	 * @psalm-pure
	 * @psalm-assert !empty $value
	 * @param mixed $value
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function notEmpty($value, string $message = ''){
		if(empty($value)){
			static::reportInvalidArgument(\sprintf($message ?: 'Expected a non-empty value. Got: %s',
			                                       static::valueToString($value)));
		}
	}
	/**
	 * @psalm-pure
	 * @psalm-template T
	 * @psalm-param mixed|array<T> $array
	 * @psalm-assert array<string, T> $array
	 * @psalm-assert !empty $array
	 * @param mixed $array
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function isNonEmptyMap($array, string $message = ''){
		static::isMap($array, $message);
		static::notEmpty($array, $message);
	}
	/**
	 * @psalm-pure
	 * @psalm-template T
	 * @psalm-param mixed|array<T> $array
	 * @psalm-assert array<string, T> $array
	 * @param mixed $array
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function isMap($array, string $message = ''){
		if(!\is_array($array) || \array_keys($array) !== \array_filter(\array_keys($array), '\is_string')){
			static::reportInvalidArgument($message ?: 'Expected map - associative array with string keys.');
		}
	}
	/**
	 * @throws BadMethodCallException
	 */
	public static function __callStatic($name, $arguments){
		if('nullOr' === \substr($name, 0, 6)){
			if(null !== $arguments[0]){
				$method = \lcfirst(\substr($name, 6));
				\call_user_func_array(['static', $method], $arguments);
			}
			return;
		}
		if('all' === \substr($name, 0, 3)){
			static::isIterable($arguments[0]);
			$method = \lcfirst(\substr($name, 3));
			$args = $arguments;
			foreach($arguments[0] as $entry){
				$args[0] = $entry;
				\call_user_func_array(['static', $method], $args);
			}
			return;
		}
		throw new BadMethodCallException('No such method: ' . $name);
	}
	/**
	 * @psalm-pure
	 * @psalm-assert iterable $value
	 * @param mixed $value
	 * @param string $message
	 * @throws InvalidArgumentException
	 */
	public static function isIterable($value, string $message = ''){
		if(!\is_array($value) && !($value instanceof Traversable)){
			static::reportInvalidArgument(\sprintf($message ?: 'Expected an iterable. Got: %s',
			                                       static::typeToString($value)));
		}
	}
}
