<?php
namespace Tests\TestGenerators;
use Illuminate\Support\Arr;
use App\Logging\QMLog;
class StaticUnitTestFile extends UnitTestFile
{
    /**
     * @param string|null $content
     * @param string|null $namePrefix
     * @return string
     */
    public static function generateAndGetUrl(string $content = null, string $namePrefix = null): string {
        $trace = debug_backtrace();
        $staticCalls = Arr::where($trace,
            function($frame){
                return $frame['type'] === "::";
            });
        foreach($staticCalls as $frame){
            $code = $frame["class"].$frame['type'].$frame["function"]."(";
            $arguments = $frame["args"];
            foreach($arguments as $i => $value){
                $code .= QMLog::var_export($frame["args"][$i], true);
                if($i < count($arguments) - 1){
                    $code .= ",\n";
                }
            }
            $code .= ");";
            $url = StagingJobTestFile::getUrl($content ?? $frame["function"], $code, $frame["class"]);
            \App\Logging\ConsoleLog::info($url);
        }
        return $url;
    }
}
