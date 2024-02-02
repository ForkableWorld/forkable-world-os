<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use App\Models\UserVariable;
use App\Models\Variable;
use App\Traits\QMAnalyzableTrait;
use App\Variables\QMUserVariable;
use App\Variables\QMVariable;
use Facade\IgnitionContracts\BaseSolution;
use Facade\IgnitionContracts\Solution;
class InvalidVariableValueException extends BaseException {
    /**
     * @var QMVariable|null
     */
    protected $analyzable;
    /**
     * @param string $message
     * @param QMAnalyzableTrait|null|UserVariable|Variable $analyzable
     * @param int|null $durationInSeconds
     */
    public function __construct(string $message, $analyzable, int $durationInSeconds = null){
        //debugger($message);
        if($analyzable && !is_object($analyzable)){le("not object");}
        $this->analyzable = $analyzable;
        $body = "Please provide variable to exception for more info";
        if($analyzable){
            $url = $analyzable->getUrl();
            if(method_exists($analyzable, 'getCommonUnit')){
                $unit = $analyzable->getCommonUnit();
                $commonUnitName = $unit->name;
                $body = "
            Variable Id: {$analyzable->getVariableIdAttribute()}
            Variable Name: {$analyzable->getNameAttribute()}
            Aggregation Period (seconds): $durationInSeconds
            Unit MaximumDailyValue: {$unit->getMaximumDailyValue()} $commonUnitName
            Unit MaximumValue: {$unit->getMaximumValue()} $commonUnitName
            Unit MinimumValue: {$unit->getMinimumValue()} $commonUnitName
            Maximum: {$analyzable->getMaximumAllowedValueAttribute()} $commonUnitName
            Minimum: {$analyzable->getCommonMinimumAllowedValue()} $commonUnitName";
                if($analyzable instanceof QMUserVariable){
                    $body .= "
            UserMaximumAllowedDailyValue: {$analyzable->getUserMaximumAllowedDailyValueAttribute()} $commonUnitName
           UserMinimumAllowedDailyValue: {$analyzable->getUserMinimumAllowedDailyValueAttribute()} $commonUnitName";
                }
                $body .= "
            Clean up here:
            => $url
            ";
            }
        }
        parent::__construct($message, $body);
    }
    public function getSolutionTitle(): string{
        return "Change Analysis Settings or Delete Some Data";
    }
    public function getSolutionDescription(): string{
        return $this->userErrorMessageBodyString;
    }
    public function getDocumentationLinks(): array{
        $a = $this->getAnalyzable();
        if(!$a){
            return [];
        }
        return $a->getUrls();
    }
    /**
     * @return QMVariable|QMAnalyzableTrait
     */
    public function getAnalyzable(){
        return $this->analyzable;
    }
    public function getSolution(): Solution{
        return BaseSolution::create($this->getSolutionTitle())
            ->setSolutionDescription($this->getSolutionDescription())
            ->setDocumentationLinks($this->getDocumentationLinks());
    }
}
