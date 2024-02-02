<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpMissingParentConstructorInspection */
namespace App\Exceptions;
use App\Models\BaseModel;
use App\Models\User;
use App\Solutions\InvalidTimezoneOffsetSolution;
use App\Solutions\ViewModelSolution;
use App\Traits\QMValidatingTrait;
use App\Types\QMStr;
use Facade\IgnitionContracts\ProvidesSolution;
use Facade\IgnitionContracts\Solution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\MessageBag;
use Throwable;
/** Used when validation fails. Contains the invalid model for easy analysis.
 * @package october\database
 * @author Alexey Bobkov, Samuel Georges
 */
class ModelValidationException extends \Exception implements ProvidesSolution
{
    /**
     * @var array Collection of invalid fields.
     */
    protected $fields;
    /**
     * @var MessageBag The message bag instance containing validation error messages
     */
    protected $messageBag;
    /**
     * @var BaseModel|QMValidatingTrait|Model
     */
    public $model;
    /**
     * Receives the invalid model and sets the {@link model} and {@link messageBag} properties.
     * @param QMValidatingTrait|BaseModel|Model $model The troublesome model.
     */
    public function __construct($model, Throwable $previous = null){
        $this->model = $model;
        $messageBag = $this->messageBag = $model->getErrors();
        $shortClass = $this->getShortClassName();
        $message = "
Validation failed for:
    $shortClass:
        ".$model->__toString()."
";
        if($model->hasId()){$message .= "\n".$model->getDataLabShowUrl();}
        $messagesByAttribute = $messageBag->messages();
        foreach ($messagesByAttribute as $attribute => $attributeMessages) {
            $this->fields[$attribute] = $attributeMessages;
            $propClass = $shortClass.QMStr::toClassName($attribute);
            $message .= "\nInvalid $propClass:\n\t";
            $message .= implode("\t\n", $attributeMessages);
        }
        parent::__construct($message, BadRequestException::CODE_BAD_REQUEST, $previous);
    }
    public function getSolution(): Solution{
        if(isset($this->fields[User::FIELD_TIME_ZONE_OFFSET])){
            return new InvalidTimezoneOffsetSolution();
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
    /**
     * Returns directly the message bag instance with the model's errors.
     * @return MessageBag
     */
    public function getMessageBag(): MessageBag{
        return $this->messageBag;
    }
    /**
     * Returns invalid fields.
     */
    public function getFields(): array{
        return $this->fields;
    }
    private function getShortClassName(): string {
        return QMStr::toShortClassName(get_class($this->getModel()));
    }
}
