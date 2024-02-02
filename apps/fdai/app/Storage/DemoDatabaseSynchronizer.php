<?php

namespace App\Storage;
use App\Models\User;
use App\Storage\DB\DBTable;
class DemoDatabaseSynchronizer extends DatabaseSynchronizer
{
	protected $sourceUserId = 230;
	protected $destUserId = 1;
	protected function getSourceQB(): \Illuminate\Database\Query\Builder{
	   $qb = parent::getSourceQB();
	   $dbTable = DBTable::find($qb->from);
		if($col = $dbTable->getUserIdColumn()){
		   $qb->where($col, $this->sourceUserId);
	   }

	   return $qb;
   }
   public function sync(): void{
	   $db = $this->getDestinationDB();
	   parent::sync();
	   foreach($db->getTablesWithUserIds() as $table){
		   $table->qb()
			   ->where($table->getUserIdColumn(), $this->sourceUserId)
			   ->update([$table->getUserIdColumn() => $this->destUserId]);
	   }
   }
}
