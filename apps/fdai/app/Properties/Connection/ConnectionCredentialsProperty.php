<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Connection;
use App\Models\BaseModel;
use App\Models\Connection;
use App\Traits\PropertyTraits\ConnectionProperty;
use App\Properties\Base\BaseCredentialsProperty;
class ConnectionCredentialsProperty extends BaseCredentialsProperty
{
    use ConnectionProperty;
    public $table = Connection::TABLE;
    public $parentClass = Connection::class;
    public static function updateAll(){
        le("We should probably continue to use credentials table instead of this field so that we can see when credentials were created and stuff");
        $qb = static::whereNullQMQB();
        $qb->whereNull(Connection::FIELD_DELETED_AT);
        $ids = $qb->pluck(Connection::FIELD_ID);
        foreach($ids as $id){
            $c = static::findParent($id);
            $dbm = $c->getDBModel();
            $credentials = $dbm->getCredentialsRow();
            $c->credentials = $credentials;
            $c->save();
            $c = static::findParent($id);
		if($credentials !== $c->credentials){le('$credentials !== $c->credentials');}
        }
    }
    /**
     * @param int $id
     * @return Connection
     */
    public static function findParent($id): ?BaseModel{
        return parent::findParent($id);
    }
}
