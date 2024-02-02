<?php /** @noinspection PhpMissingFieldTypeInspection */
/** @noinspection PhpPropertyOnlyWrittenInspection */
namespace App\Exceptions;
use App\Models\BaseModel;
use App\Properties\BaseProperty;
use Exception;
class RedundantVariableParameterException extends Exception
{
    private string $url;
    private string $ruleDescription;
    private BaseModel $model;
    public function __construct(BaseModel $model, BaseProperty $property, string $ruleDescription){
        $this->model = $model;
        $this->ruleDescription = $ruleDescription;
        $this->url = $model->getUrl();
        parent::__construct($ruleDescription.":
        Model: ".$model->getShortClassName()."
        Title: ".$model->getTitleAttribute()."
        Property: ".$property->name."
        Value: ".$property->getDBValue()."
        ");
    }
}
