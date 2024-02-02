<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace Netatmo\Common;
class NABatteryLevelModule {
    /* Battery range: 6000 ... 3600 */
    public const BATTERY_LEVEL_0 = 5500;/*full*/
    public const BATTERY_LEVEL_1 = 5000;/*high*/
    public const BATTERY_LEVEL_2 = 4500;/*medium*/
    public const BATTERY_LEVEL_3 = 4000;/*low*/
    /* below 4000: very low */
}
?>
