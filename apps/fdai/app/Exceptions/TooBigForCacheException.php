<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use App\Logging\QMLog;
use App\Storage\S3\S3Private;
use App\Types\ObjectHelper;
use Facade\IgnitionContracts\BaseSolution;
use Facade\IgnitionContracts\ProvidesSolution;
use Facade\IgnitionContracts\Solution;
class TooBigForCacheException extends \Exception implements ProvidesSolution
{
    /**
     * @var string
     */
    private $key;
    private $value;
    /**
     * TooBigForCacheException constructor.
     * @param string $key
     * @param $value
     * @param int $sizeInKb
     * @param int $maximumKb
     */
    public function __construct(string $key, $value, int $sizeInKb, int $maximumKb)
    {
        $this->key = $key;
        $this->value = $value;
        $propertySizes = ObjectHelper::getSubPropertySizesInKb($value);
        QMLog::warning("$key size $sizeInKb kb is too big for memcached even attempting to shrink!
                Max: $maximumKb kb
                Property sizes (kb): ".\App\Logging\QMLog::print_r($propertySizes, true), ['property_sizes_kb' => $propertySizes]);
        parent::__construct("$key size $sizeInKb kb is too big for memcached even attempting to shrink!
                Property sizes (kb): ".\App\Logging\QMLog::print_r($propertySizes, true));
    }
    public function getSolution(): Solution{
        return BaseSolution::create("Examine this Object")
            ->setSolutionDescription("Either find a way to shrink it or don't try to cache it")
            ->setDocumentationLinks([
                "View" => S3Private::uploadObject(__METHOD__."/$this->key", $this->value)
            ]);
    }
}
