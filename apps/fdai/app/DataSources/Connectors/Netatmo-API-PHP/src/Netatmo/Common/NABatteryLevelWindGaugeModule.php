<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace Netatmo\Common;
class NABatteryLevelWindGaugeModule {
    /* Battery range: 6000 ... 3950 */
    public const WG_BATTERY_LEVEL_0 = 5590;/*full*/
    public const WG_BATTERY_LEVEL_1 = 5180;/*high*/
    public const WG_BATTERY_LEVEL_2 = 4770;/*medium*/
    public const WG_BATTERY_LEVEL_3 = 4360;/*low*/
    /* below 4360: very low */
}
?>
