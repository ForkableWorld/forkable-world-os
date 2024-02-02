<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use App\Traits\HasClassName;
use App\Traits\QMAnalyzableTrait;
use App\Traits\DataLabTrait;
abstract class AnalysisException extends BaseException {
    /**
     * @var QMAnalyzableTrait
     */
    protected $analyzable;
    /**
     * InsufficientVarianceException constructor.
     * @param string $problemTitle
     * @param string $problemDetailsSentence
     * @param string|null $internalErrorMessage
     * @param int $code
     */
    public function __construct(string $problemTitle,
                                string $problemDetailsSentence,
                                string $internalErrorMessage = null,
                                int $code = 0){
        $this->getAnalyzable()->addException($this);
        parent::__construct($problemTitle,
            $problemDetailsSentence,
            $internalErrorMessage,
            null, $code);
    }
    public function getDocumentationLinks(): array{
        return $this->links = array_merge($this->links, $this->getAnalyzable()->getUrls());
    }
    /**
     * @return QMAnalyzableTrait|HasClassName
     */
    public function getAnalyzable(){
        if(!$this->analyzable){
            throw new \LogicException("Please set analyzable in ".static::class);
        }
        return $this->analyzable;
    }
    /**
     * @return DataLabTrait[]
     */
    public function getRelatedModels(): array{
        $a = $this->getAnalyzable();
        $models = $a->getSourceObjects();
        $models[] = $a;
        return $models;
    }
}
