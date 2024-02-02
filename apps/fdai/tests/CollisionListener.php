<?php

/**
 * This file is part of Collision.
 *
 * (c) Nuno Maduro <enunomaduro@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Tests;

use App\Utils\AppMode;
use NunoMaduro\Collision\Contracts\Adapters\Phpunit\Listener as ListenerContract;
use NunoMaduro\Collision\Contracts\Writer as WriterContract;
use NunoMaduro\Collision\Writer;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\ExceptionWrapper;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\Warning;
use PHPUnit\Runner\Version;
use ReflectionException;
use ReflectionObject;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Whoops\Exception\Inspector;
if (class_exists(Version::class) && intval(substr(Version::id(), 0, 1)) >= 7) {

	/**
	 * This is an Collision Phpunit Adapter implementation.
	 *
	 * @author Nuno Maduro <enunomaduro@gmail.com>
	 */
	class CollisionListener implements ListenerContract
	{
		/**
		 * Holds an instance of the writer.
		 * @var WriterContract
		 */
		protected $writer;

		/**
		 * Holds the exception found, if any.
		 *
		 * @var \Throwable|null
		 */
		protected $exceptionFound;

		/**
		 * Creates a new instance of the class.
		 *
		 * @param WriterContract|null $writer
		 */
		public function __construct(WriterContract $writer = null)
		{
			$this->writer = $writer ?: $this->buildWriter();
		}

		/**
		 * {@inheritdoc}
		 */
		public function render(Test $test, \Throwable $t)
		{
			if ($t instanceof ExceptionWrapper && $t->getOriginalException() !== null) {
				$t = $t->getOriginalException();
			}

			$inspector = new Inspector($t);

			$this->writer->write($inspector);
		}

		/**
		 * {@inheritdoc}
		 */
		public function addError(Test $test, \Throwable $t, float $time): void
		{
			if ($this->exceptionFound === null) {
				$this->exceptionFound = $t;
			}
		}

		/**
		 * {@inheritdoc}
		 */
		public function addWarning(Test $test, Warning $t, float $time): void
		{
		}

		/**
		 * {@inheritdoc}
		 */
		public function addFailure(Test $test, AssertionFailedError $e, float $time): void
		{
			$this->writer->ignoreFilesIn(['/vendor/'])
			             ->showTrace(false);

			if ($this->exceptionFound === null) {
				$this->exceptionFound = $e;
			}
		}

		/**
		 * {@inheritdoc}
		 */
		public function addIncompleteTest(Test $test, \Throwable $t, float $time): void
		{
		}

		/**
		 * {@inheritdoc}
		 */
		public function addRiskyTest(Test $test, \Throwable $t, float $time): void
		{
		}

		/**
		 * {@inheritdoc}
		 */
		public function addSkippedTest(Test $test, \Throwable $t, float $time): void
		{
		}

		/**
		 * {@inheritdoc}
		 */
		public function startTestSuite(TestSuite $suite): void
		{
		}

		/**
		 * {@inheritdoc}
		 */
		public function endTestSuite(TestSuite $suite): void
		{
		}

		/**
		 * {@inheritdoc}
		 */
		public function startTest(Test $test): void
		{
		}

		/**
		 * {@inheritdoc}
		 */
		public function endTest(Test $test, float $time): void
		{
		}


		public function __destruct()
		{
			//$t = \App\Utils\AppMode::getCurrentTest();
			//$t->saveTdddResults($this->exceptionFound);
			if ($this->exceptionFound !== null) {
				$this->render(AppMode::getCurrentTest(), $this->exceptionFound);
			}
		}
		/**
		 * Builds an Writer.
		 * @return WriterContract
		 */
		protected function buildWriter(): WriterContract
		{
			$writer = new Writer;

			$application = new Application();
			$reflector = new ReflectionObject($application);
			$method = $reflector->getMethod('configureIO');
			$method->setAccessible(true);
			try {
				$method->invoke($application, new ArgvInput, $output = new ConsoleOutput);
			} catch (ReflectionException $e) {
				le($e);
			}
			return $writer->setOutput($output);
		}
	}
}
