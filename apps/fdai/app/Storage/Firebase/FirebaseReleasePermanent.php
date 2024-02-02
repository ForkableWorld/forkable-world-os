<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Storage\Firebase;
use App\Utils\ReleaseStage;
class FirebaseReleasePermanent extends FirebaseGlobalPermanent
{
    public static function formatKey(string $path, bool $temporary = false): string {
        return ReleaseStage::getReleaseStage().'/'.parent::formatKey($path, $temporary);
    }
}
