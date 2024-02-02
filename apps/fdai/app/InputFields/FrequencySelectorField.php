<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\InputFields;
use App\Fields\Select;
class FrequencySelectorField extends Select {
    public const OPTIONS = [
        "As-Needed"         => 0,
        "Every 12 hours"    => 12 * 60 * 60,
        "Every 8 hours"     => 8 * 60 * 60,
        "Every 6 hours"     => 6 * 60 * 60,
        "Every 4 hours"     => 4 * 60 * 60,
        "Every 3 hours"     => 180 * 60,
        "Every 30 minutes"  => 30 * 60,
        "Every minute"      => 60,
        "Hourly"            => 60 * 60,
        "Daily"             => 24 * 60 * 60,
        "Twice a day"       => 12 * 60 * 60,
        "Three times a day" => 8 * 60 * 60,
        "Minutely"          => 60,
        "Every other day"   => 172800,
        'Weekly'            => 7 * 86400,
        'Every 2 weeks'     => 14 * 86400,
        'Every 4 weeks'     => 28 * 86400
    ];
    /**
     * Create a new field.
     * @param string $name
     * @param string|null $attribute
     * @param mixed|null $resolveCallback
     */
    public function __construct(string $name = "Frequency", $attribute = null, $resolveCallback = null){
        parent::__construct($name, $attribute, $resolveCallback);
        $this->displayUsingLabels();
        $this->options(self::getSecondsToStringOptions());
    }
    public static function secondsToString(int $seconds): string {
        return self::OPTIONS[$seconds];
    }
    public static function getSecondsToStringOptions():array{
        $arr = [];
        foreach(self::OPTIONS as $label => $seconds){
            $arr[$seconds] = $label;
        }
        return $arr;
    }
}
