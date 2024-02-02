<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpMissingParentConstructorInspection */
namespace App\Exceptions;
use App\CodeGenerators\TVarDumper;
use App\Models\BaseModel;
use App\Models\User;
use App\Solutions\CreateSolution;
use App\Solutions\EditModelSolution;
use App\Solutions\InvalidTimezoneOffsetSolution;
use App\Solutions\ViewModelSolution;
use App\Traits\QMValidatingTrait;
use App\Utils\AppMode;
use App\Utils\EnvOverride;
use Facade\IgnitionContracts\ProvidesSolution;
use Facade\IgnitionContracts\Solution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\MessageBag;
use Tests\QMBaseTestCase;

/** Used when validation fails. Contains the invalid model for easy analysis.
 * @package october\database
 * @author Alexey Bobkov, Samuel Georges
 */
class InvalidAttributeException extends \Exception implements ProvidesSolution
{
    /**
     * @var BaseModel|QMValidatingTrait|Model
     */
    protected $model;
    /**
     * @var string
     */
    public $attributeName;
    /**
     * @var string
     */
    protected $attributeValue;
    protected $userErrorMessageBodyString;
    /**
     * @var string
     */
    private $ruleDescription;
    public $url;
    /**
     * Receives the invalid model and sets the {@link model} and {@link errors} properties.
     * @param QMValidatingTrait|BaseModel|Model $model The troublesome model.
     * @param string $attributeName
     * @param $attributeValue
     * @param string $ruleDescription
     */
    public function __construct(BaseModel $model, string $attributeName, $attributeValue, string $ruleDescription){
        $this->model = $model;
        $this->attributeName = $attributeName;
        $this->attributeValue = $attributeValue;
        $this->ruleDescription = $ruleDescription;
        $this->url = $model->getUrl();
        //debugger("$attributeName ".$ruleDescription." on $model");
        parent::__construct($ruleDescription."
        $attributeName  on $model
        ".TVarDumper::dump($attributeValue));
    }
    public function getSolution(): Solution{
        if(EnvOverride::isLocal()){
            return new CreateSolution($this);
        }
        if(isset($this->fields[User::FIELD_TIME_ZONE_OFFSET])){
            return new InvalidTimezoneOffsetSolution();
        }
        if($this->alreadySaved()){
            return new EditModelSolution($this->getModel());
        }
        return new ViewModelSolution($this->getModel());
    }
    /**
     * Returns the model with invalid attributes.
     * @return BaseModel
     */
    public function getModel(){
        return $this->model;
    }
    public function getSolutionTitle(): string{
        return "Correct the Value";
    }
    public function getSolutionDescription(): string{
        return $this->userErrorMessageBodyString;
    }
    public function getDocumentationLinks(): array{
        return $this->getModel()->getDataLabUrls();
    }
    public function getMessageBag(): MessageBag {
        $bag = new MessageBag();
        $bag->add($this->attributeName, $this->getMessage());
        return $bag;
    }
    private function alreadySaved(): bool {
        $m = $this->getModel();
        return !$m->wasChanged($this->attributeName);
    }
    /**
     * @return string
     */
    public function getValidationMessage(): string{
        $exported = TVarDumper::dump($this->attributeValue);
        $model = $this->getModel();
		$url = null;
		if($model->hasId()){
			try {
				$url = (method_exists($model, 'getAnalyzeUrl')) ?  $model->getAnalyzeUrl() :
					$model->getUrl();
			} catch (\Throwable $e){
				try {$url = $model->getUrl();} catch (\Throwable $e){$url = "Could not get url because: ". $e->getMessage();}
			}
		}
        $message = "Issue: $this->attributeName $this->ruleDescription on ".$model->getShortClassName()."
    Actual Value: ".$exported."
    ".$url;
        $this->userErrorMessageBodyString = $message;
        if(AppMode::isTestingOrStaging() &&
            !get_class($this) === \App\Exceptions\ExceptionHandler::getExpectedRequestException()){
            le($this);
        }
        return $message;
    }
}
