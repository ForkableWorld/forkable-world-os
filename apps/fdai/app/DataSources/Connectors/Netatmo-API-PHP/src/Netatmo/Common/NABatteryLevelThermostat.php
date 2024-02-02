<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace Netatmo\Common;
class NABatteryLevelThermostat {
    /* Battery range: 4500 ... 3000 */
    public const THERMOSTAT_BATTERY_LEVEL_0 = 4100;/*full*/
    public const THERMOSTAT_BATTERY_LEVEL_1 = 3600;/*high*/
    public const THERMOSTAT_BATTERY_LEVEL_2 = 3300;/*medium*/
    public const THERMOSTAT_BATTERY_LEVEL_3 = 3000;/*low*/
    /* below 3000: very low */
}
?>
