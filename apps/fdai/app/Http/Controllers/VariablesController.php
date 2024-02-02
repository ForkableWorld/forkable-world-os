<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers;
use App\Exceptions\NotFoundException;
use App\Exceptions\UnauthorizedException;
use App\Logging\QMLog;
use App\Models\Variable;
use App\Properties\Variable\VariableIdProperty;
use Illuminate\Http\Request;

class VariablesController extends Controller {
	public function index(): string{
		if($id = VariableIdProperty::fromRequest()){
			return $this->show($id);
		}
		return Variable::getIndexPageHtml();
	}
	/**
	 * @param $query
	 * @return string
	 * @throws UnauthorizedException
	 */
	public function show($query): string{
		try {
			if(str_starts_with($query, "tags/")){
				return Variable::generateShowPage(str_replace(["-", "tags/"], " ", $query));
			}
			return Variable::generateShowPage($query);
		} catch (NotFoundException $e) {
			QMLog::error(__METHOD__.": ".$e->getMessage());
			return Variable::generateIndexHtml();
		}
	}
    public function create()
    {
        //
    }
    public function store(Request $request)
    {
        //
    }
    public function edit(Variable $variable)
    {
        //
    }
    public function update(Request $request, Variable $variable)
    {
        //
    }
    public function destroy(Variable $variable)
    {
        //
    }
}
