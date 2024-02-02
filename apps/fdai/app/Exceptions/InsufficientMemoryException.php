<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use App\Computers\ThisComputer;
use App\Logging\QMLog;
use RuntimeException;
class InsufficientMemoryException extends RuntimeException {
    /**
     * InsufficientMemoryException constructor.
     * @param string $message
     * @param float $needed = null
     */
    public function __construct(string $message, float $needed = null){
        $message = InsufficientMemoryException::getMemorySummary($message);
        if($needed){
            $mbNeeded = $needed / ThisComputer::MB;
            $message .= "
            NEEDED: $mbNeeded MB
            ";
        }
        parent::__construct($message);
    }
    /**
     * @param $message
     * @return string
     */
    public static function getMemorySummary(string $message = ""): string{
        $limit = ThisComputer::getMemoryLimitInBytes();
        $limitMB = $limit / ThisComputer::MB;
        $currentUsage = memory_get_usage();
        $currentUsageMB = $currentUsage / ThisComputer::MB;
        $available = $limit - $currentUsage;
        $mbAvailable = $available / ThisComputer::MB;
        $message .= "
            AVAILABLE: $mbAvailable MB
            CURRENT USAGE: $currentUsageMB MB
            TOTAL LIMIT: $limitMB MB";
        return $message;
    }
    public static function logMemorySummary(string $message = null){
        \App\Logging\ConsoleLog::info($message."\n".self::getMemorySummary());
    }
}
