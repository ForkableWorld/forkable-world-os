<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use App\Buttons\Admin\PHPStormButton;
use App\Files\FileHelper;
use App\Storage\S3\S3Private;
use App\Types\QMStr;
use App\Utils\EnvOverride;
use Facade\IgnitionContracts\Solution;
class AnalyzeSizeSolution implements Solution
{
    protected $value;
    /**
     * @var array
     */
    public $links;
    public $s3Path;
    /**
     * AnalyzeSizeSolution constructor.
     * @param string $s3Path
     * @param $value
     */
    public function __construct(string $s3Path, $value){
        $this->value = $value;
        $this->s3Path;
    }
    public function getSolutionTitle(): string{
        return "View Sizes";
    }
    public function getSolutionDescription(): string{
        return "View Property Sizes";
    }
    public function getDocumentationLinks(): array{
        if($this->links){return $this->links;}
        $value = $this->value;
        $s3Path = $this->s3Path;
        if(is_object($value) || is_array($value)){
            $value = QMStr::prettyJsonEncode($value);
            if(stripos($s3Path, '.json') === false){$s3Path .= '.json';}
        }
        if(EnvOverride::isLocal()){
            $path = "tmp/".$s3Path;
            FileHelper::writeByFilePath($path, $value);
            $url = PHPStormButton::redirectUrl($path);
        } else {
            $url = S3Private::upload($s3Path, $value);
        }
        return $this->links = [
            "View Object" => $url,
            "JSON Size Analyzer" => "https://www.debugbear.com/json-size-analyzer"
        ];
    }
}
