<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use App\Logging\ConsoleLog;
use App\Logging\QMIgnition;
use App\Types\QMStr;
use App\Utils\DiffFile;
use Facade\IgnitionContracts\BaseSolution;
use Facade\IgnitionContracts\ProvidesSolution;
use Facade\IgnitionContracts\Solution;
class DiffException extends \Exception implements ProvidesSolution
{
    /**
     * @var DiffFile
     */
    private $differ;
    /**
     * @var BaseSolution
     */
    private $solution;
	private array $links = [];
	/**
     * DiffException constructor.
     * @param DiffFile $differ
     * @param string|null $message
     */
    public function __construct(DiffFile $differ, string $message = null){
        $this->differ = $differ;
        if($message){$message .= "\n";}
        $path = $this->getFixturePath();
        $message .= "New $path doesn't match old one!. ".QMStr::list($this->getDocumentationLinks());
        // Need to set solution in advance so it's available in tests after app is destroyed
	    //\App\Utils\AppMode::getCurrentTest()->addAttachment($path, 'Expected Fixture', 'text/plain');
	    //\App\Utils\AppMode::getCurrentTest()->addAttachment($differ->getActualNormalized(), 'Actual Html', 'text/plain');
        parent::__construct($message);
    }
	/**
	 * @return Solution
	 */
    public function getSolution(): Solution{
        if($this->solution){return $this->solution;}
		try {
			$links = $this->getDocumentationLinks();
		} catch (\Throwable $e) {
			$message = "Failed to getDocumentationLinks for solution: ".$e->getMessage();
		    ConsoleLog::error($message);
			$links[$message] = "";
		}
        return $this->solution = BaseSolution::create("See Diffs")
            ->setSolutionDescription("Update response or check diff to see what's fucked up")
            ->setDocumentationLinks($links);
    }
    /**
     * @return string
     */
    protected function getFixturePath(): string{
        $testName = \App\Utils\AppMode::getCurrentTestName();
        $differ = $this->getDiffer();
        $path = $differ->fixturePath;
        if($testName){$path = QMStr::after($testName, $path, $path);}
		if(!$path){
			ConsoleLog::error("No fixture path found for test $testName! \$differ->fixturePath is $differ->fixturePath");
			return '';
		}
        return $path;
    }
    /**
     * @return DiffFile
     */
    public function getDiffer(): DiffFile{
        return $this->differ;
    }
	/**
	 * @return string
	 */
	private function getIgnitionUrl(): string{
		return QMIgnition::generateUrl($this);
	}
	/**
	 * @return array
	 */
	public function getDocumentationLinks(): array{
		if($this->links){return $this->links;}
		try {
			return $this->links = $this->differ->getLinks();
		} catch (\Throwable $e) {
			ConsoleLog::error(__METHOD__.': Could not differ->getLinks because '.$e->getMessage());
			return [];
		}
	}
}
