<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Actions;
use App\Slim\Middleware\QMAuth;
use App\Storage\DB\QMQB;
use App\Storage\DB\Writable;
use App\Storage\QueryBuilderHelper;
abstract class QueryAction extends AdminAction {
	protected $table;
	protected $params;
	public function __construct(string $table, array $params){
		parent::__construct([
			'table' => $table,
			'params' => $params,
		]);
	}
	/**
	 * Determine if the user is authorized to make this action.
	 * @return bool
	 */
	public function authorize(){
		return QMAuth::isAdmin();
	}
	/**
	 * Get the validation rules that apply to the action.
	 * @return array
	 */
	public function rules(){
		return [
			'table' => ['required'],
			'params' => ['required'],
		];
	}
	/**
	 * @inheritDoc
	 */
	abstract public function handle();
	/**
	 * @return QMQB
	 */
	protected function getQueryBuilder(): QMQB{
		$qb = Writable::getBuilderByTable($this->table);
		QueryBuilderHelper::addParams($qb, $this->params);
		return $qb;
	}
}
