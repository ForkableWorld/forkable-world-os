<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace Netatmo\Common;
class NACameraSDEvent {
    public const CSDE_ABSENT = 1;
    public const CSDE_INSERTED = 2;
    public const CSDE_FORMATED = 3;
    public const CSDE_OK = 4;
    public const CSDE_DEFECT = 5;
    public const CSDE_INCOMPATIBLE = 6;
    public const CSDE_TOO_SMALL = 7;
    public static $issueEvents = [
        NACameraSDEvent::CSDE_ABSENT,
        NACameraSDEvent::CSDE_DEFECT,
        NACameraSDEvent::CSDE_INCOMPATIBLE,
        NACameraSDEvent::CSDE_TOO_SMALL
    ];
}
?>
