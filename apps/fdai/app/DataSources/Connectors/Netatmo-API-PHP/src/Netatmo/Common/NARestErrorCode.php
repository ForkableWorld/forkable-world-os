<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace Netatmo\Common;
class NARestErrorCode {
    public const ACCESS_TOKEN_MISSING = 1;
    public const INVALID_ACCESS_TOKEN = 2;
    public const ACCESS_TOKEN_EXPIRED = 3;
    public const INCONSISTENCY_ERROR = 4;
    public const APPLICATION_DEACTIVATED = 5;
    public const INVALID_EMAIL = 6;
    public const NOTHING_TO_MODIFY = 7;
    public const EMAIL_ALREADY_EXISTS = 8;
    public const DEVICE_NOT_FOUND = 9;
    public const MISSING_ARGS = 10;
    public const INTERNAL_ERROR = 11;
    public const DEVICE_OR_SECRET_NO_MATCH = 12;
    public const OPERATION_FORBIDDEN = 13;
    public const APPLICATION_NAME_ALREADY_EXISTS = 14;
    public const NO_PLACES_IN_DEVICE = 15;
    public const MGT_KEY_MISSING = 16;
    public const BAD_MGT_KEY = 17;
    public const DEVICE_ID_ALREADY_EXISTS = 18;
    public const IP_NOT_FOUND = 19;
    public const TOO_MANY_USER_WITH_IP = 20;
    public const INVALID_ARG = 21;
    public const APPLICATION_NOT_FOUND = 22;
    public const USER_NOT_FOUND = 23;
    public const INVALID_TIMEZONE = 24;
    public const INVALID_DATE = 25;
    public const MAX_USAGE_REACHED = 26;
    public const MEASURE_ALREADY_EXISTS = 27;
    public const ALREADY_DEVICE_OWNER = 28;
    public const INVALID_IP = 29;
    public const INVALID_REFRESH_TOKEN = 30;
    public const NOT_FOUND = 31;
    public const BAD_PASSWORD = 32;
    public const FORCE_ASSOCIATE = 33;
    public const MODULE_ALREADY_PAIRED = 34;
    public const UNABLE_TO_EXECUTE = 35;
    public const PROHIBITTED_STRING = 36;
    public const CAMERA_NO_SPACE_AVAILABLE = 37;
    public const PASSWORD_COMPLEXITY_TOO_LOW = 38;
    public const TOO_MANY_CONNECTION_FAILURE = 39;
}
?>
