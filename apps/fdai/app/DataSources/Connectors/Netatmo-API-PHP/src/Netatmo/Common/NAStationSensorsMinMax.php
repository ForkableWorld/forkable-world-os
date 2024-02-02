<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace Netatmo\Common;
/* Defines the min and max values of the sensors.
 */
class NAStationSensorsMinMax {
    public const TEMP_MIN = -40;
    public const TEMP_MAX = 60;
    public const HUM_MIN = 1;
    public const HUM_MAX = 99;
    public const CO2_MIN = 300;
    public const CO2_MAX = 4000;
    public const PRESSURE_MIN = 700;
    public const PRESSURE_MAX = 1300;
    public const NOISE_MIN = 10;
    public const NOISE_MAX = 120;
    public const RAIN_MIN = 2;
    public const RAIN_MAX = 300;
    public const WIND_MIN = 5;
    public const WIND_MAX = 150;
}
?>
