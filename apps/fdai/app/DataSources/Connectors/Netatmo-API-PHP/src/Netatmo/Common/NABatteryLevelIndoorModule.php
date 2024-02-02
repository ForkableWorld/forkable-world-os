<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace Netatmo\Common;
class NABatteryLevelIndoorModule {
    /* Battery range: 6000 ... 4200 */
    public const INDOOR_BATTERY_LEVEL_0 = 5640;/*full*/
    public const INDOOR_BATTERY_LEVEL_1 = 5280;/*high*/
    public const INDOOR_BATTERY_LEVEL_2 = 4920;/*medium*/
    public const INDOOR_BATTERY_LEVEL_3 = 4560;/*low*/
    /* Below 4560: very low */
}
?>
