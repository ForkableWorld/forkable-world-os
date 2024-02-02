<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use App\Models\BaseModel;
use App\Solutions\GenerateJobSolution;
use App\Traits\DataLabTrait;
use App\Traits\HasClassName;
use App\UI\HtmlHelper;
use Exception;
use Facade\FlareClient\Stacktrace\Frame;
use Facade\FlareClient\Stacktrace\Stacktrace;
use Facade\IgnitionContracts\ProvidesSolution;
use Throwable;
use Whoops\Handler\HandlerInterface;
abstract class BaseException extends Exception implements ProvidesSolution {
    use HasClassName;
    protected $relatedModels = [];
    public $meta = [];
    public $description;
    public $internalErrorMessage;
    public $links = [];
    public $solutionDescription;
    public $solutionTitle;
    public $userErrorMessageBodyHtml;
    public $userErrorMessageBodyString;
    public $userErrorMessageTitle;
    /**
     * @var GenerateJobSolution
     */
    public GenerateJobSolution $solution;
    /**
     * @var string
     */
    public string $html;
    /**
     * @var BaseModel The invalid model.
     */
    protected BaseModel $model;
    /**
     * NotEnoughVariablesToCorrelateWithException constructor.
     * @param string|null $userErrorMessageTitle
     * @param string|null $userErrorMessageBodyString
     * @param string|null $internalErrorMessage
     * @param string|null $userErrorMessageBodyHtml
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $userErrorMessageTitle = null,
                                string $userErrorMessageBodyString = null,
                                string $internalErrorMessage = null,
                                string $userErrorMessageBodyHtml = null,
                                int $code = 0,
                                Throwable $previous = null){
        $this->userErrorMessageTitle = $userErrorMessageTitle;
        $this->userErrorMessageBodyString = $userErrorMessageBodyString;
        $this->userErrorMessageBodyHtml = $userErrorMessageBodyHtml;
        $this->internalErrorMessage = $internalErrorMessage;
        $msg = $this->renderString();
        if($internalErrorMessage){$msg .= "\n".$internalErrorMessage;}
        parent::__construct($msg, $code, $previous);
    }
    /**
     * @return string
     */
    public function renderHtmlWithSolution(): string {
        // Don't use <h6> because it's smaller than the text sometimes
        $html = "<h5>$this->userErrorMessageTitle</h5>";
        if($this->userErrorMessageBodyHtml){$html .= $this->userErrorMessageBodyHtml;}
        $html .= "<h5>Solution: $this->solutionTitle".$this->getSolutionTitle()."</h5>";
        if($this->userErrorMessageBodyHtml){
            $html .= $this->userErrorMessageBodyHtml;
        }
        return $html;
    }
    public function getSolutionTitle(): string {
        return $this->getSolution()->getSolutionTitle();
    }
    /**
     * Returns the model with invalid attributes.
     * @return BaseModel
     */
    public function getModel(): BaseModel{
        return $this->model;
    }
    /**
     * @return string
     */
    public function renderString(): string {
        $str = "<p><b>$this->userErrorMessageTitle:</b></p>\n";
        if($body = $this->userErrorMessageBodyString){
            $str .= "<p>$body</p>\n";
        }
        return $str;
    }
    /**
     * @return DataLabTrait[]
     */
    public function getRelatedModels(): array{
        return $this->relatedModels;
    }
    /**
     * @param DataLabTrait $relatedModel
     */
    public function addRelatedModel($relatedModel): void{
        $this->relatedModels[$relatedModel->getUniqueIndexIdsSlug()] = $relatedModel;
    }
    /**
     * @return string
     */
    public function getUserErrorMessageHtml(): string{
        return $this->html = $this->renderHtmlWithSolution();
    }
    public function logError(){
        ExceptionHandler::logError($this);
    }
    public function getDocumentationLinks(): array{
        return $this->getSolution()->getDocumentationLinks();
    }
    public static function getHandler(){
        $handler = app(ExceptionHandler::class);
        return $handler;
    }
    /** @noinspection PhpUnused */
    public function handleWithWhoops(): ?int{
        $handler = app(HandlerInterface::class);
        // The HandlerInterface does not require an Exception passed to handle()
        // and neither of our bundled handlers use it.
        // However, 3rd party handlers may have already relied on this parameter,
        // and removing it would be possibly breaking for users.
        $handlerResponse = $handler->handle($this);
        return $handlerResponse;
    }
    public function getIgnitionStacktrace():Stacktrace{
        return Stacktrace::createForThrowable($this, base_path());
    }
    /**
     * @return Frame[]
     * @noinspection PhpUnused
     */
    public function getFramesWithSnippets(): array {
        $stack = $this->getIgnitionStacktrace();
        return $stack->toArray();
    }
    /**
     * @return Frame
     * @noinspection PhpUnused
     */
    public function firstApplicationFrame(): Frame{
        return $this->getIgnitionStacktrace()->firstApplicationFrame();
    }
    /**
     * @return string|null
     */
    public function getUserErrorMessageBodyHtml(): ?string{
        if($html = $this->userErrorMessageBodyHtml){
            return $html;
        }
        if($str = $this->userErrorMessageBodyString){
			$str = HtmlHelper::text_to_html($str);
            return $str;
        }
        return null;
    }
    public function getHtml(): string {
        return HtmlHelper::renderView(view('exception', ['exception' => $this]));
    }
}
