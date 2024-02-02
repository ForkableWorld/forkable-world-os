<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace Netatmo\Common;
class NAScopes {
    public const SCOPE_READ_STATION = "read_station";
    public const SCOPE_READ_THERM = "read_thermostat";
    public const SCOPE_WRITE_THERM = "write_thermostat";
    public const SCOPE_READ_CAMERA = "read_camera";
    public const SCOPE_WRITE_CAMERA = "write_camera";
    public const SCOPE_ACCESS_CAMERA = "access_camera";
    public const SCOPE_READ_JUNE = "read_june";
    public const SCOPE_WRITE_JUNE = "write_june";
    public const SCOPE_READ_PRESENCE = "read_presence";
    public const SCOPE_WRITE_PRESENCE = "write_presence";
    public const SCOPE_ACCESS_PRESENCE = "access_presence";
    public static $defaultScopes = [NAScopes::SCOPE_READ_STATION];
    public static $validScopes = [
        NAScopes::SCOPE_READ_STATION,
        NAScopes::SCOPE_READ_THERM,
        NAScopes::SCOPE_WRITE_THERM,
        NAScopes::SCOPE_READ_CAMERA,
        NAScopes::SCOPE_WRITE_CAMERA,
        NAScopes::SCOPE_ACCESS_CAMERA,
        NAScopes::SCOPE_READ_PRESENCE,
        NAScopes::SCOPE_WRITE_PRESENCE,
        NAScopes::SCOPE_ACCESS_PRESENCE,
        NAScopes::SCOPE_READ_JUNE,
        NAScopes::SCOPE_WRITE_JUNE
    ];
    // scope allowed to everyone (no need to be approved)
    public static $basicScopes = [
        NAScopes::SCOPE_READ_STATION,
        NAScopes::SCOPE_READ_THERM,
        NASCopes::SCOPE_WRITE_THERM,
        NAScopes::SCOPE_READ_CAMERA,
        NAScopes::SCOPE_WRITE_CAMERA,
        NAScopes::SCOPE_READ_PRESENCE,
        NAScopes::SCOPE_WRITE_PRESENCE,
        NAScopes::SCOPE_READ_JUNE,
        NAScopes::SCOPE_WRITE_JUNE
    ];
}
?>
