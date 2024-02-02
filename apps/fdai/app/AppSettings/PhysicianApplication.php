<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\AppSettings;
class PhysicianApplication extends AppSettings {
    /**
     * @return PhysicianApplication[]
     */
    public static function getAll():array {
        $qb = self::getBaseSelectQuery();
        $qb->whereNull(self::TABLE.'.'.self::FIELD_DELETED_AT);
        $qb->where(self::FIELD_PHYSICIAN, 1);
        $models = $qb->getDBModels();
        return $models;
    }
}
