<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Actions;
class RunSQLAction extends AdminAction {
	public $sql;
	/**
	 * @inheritDoc
	 */
	public function handle(){
		$this->validated();
		$sql = $this->sql;
		db_statement($sql);
	}
}
