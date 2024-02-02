<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use App\Buttons\Admin\PHPStormExceptionButton;
use App\Utils\UrlHelper;
use Exception;
use Facade\IgnitionContracts\BaseSolution;
use Facade\IgnitionContracts\ProvidesSolution;
use Facade\IgnitionContracts\Solution;
use Tests\QMBaseTestCase;
class UnexpectedStatusCodeException extends Exception implements ProvidesSolution
{
    /**
     * @var string
     */
    private $requestUrl;
    /**
     * @var string
     */
    private $method;
    public function __construct(int $expectedCode, int $actual, string $url, string $method, string $message = null){
        $this->requestUrl = UrlHelper::getTestUrl($url);
        $this->method = $method;
        parent::__construct("Expected $expectedCode but got $actual from ".$url."\n$message",
                            $actual);
    }
    public function getSolution(): Solution{
        $links = [
	        "Run " . \App\Utils\AppMode::getCurrentTestName() => \App\Files\FileFinder::getPathOrUrlToCurrentTest(),
        ];
        if($this->method === "GET"){
            $links["Request URL $this->requestUrl"] = $this->requestUrl;
        }
        return BaseSolution::create("Debug Request")
            ->setSolutionDescription("Add break point and run " . \App\Utils\AppMode::getCurrentTestName())
            ->setDocumentationLinks($links);
    }
}
