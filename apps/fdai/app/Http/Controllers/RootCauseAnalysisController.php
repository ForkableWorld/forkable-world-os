<?php

namespace App\Http\Controllers;

use App\Files\FileHelper;
use App\Utils\EnvOverride;
use App\Variables\CommonVariables\EmotionsCommonVariables\EnthusiasmCommonVariable;
use App\Variables\QMUserVariable;
class RootCauseAnalysisController extends Controller
{
    public function __invoke()
    {
	    $uv = QMUserVariable::fromRequest();
	    if(!$uv){
		    $uv  = QMUserVariable::findUserVariableByNameIdOrSynonym(230, EnthusiasmCommonVariable::ID);
	    }
	    $view = view('root-cause', ['uv' => $uv]);
	    $html = $view->render();
	    if(EnvOverride::isLocal()){
		    FileHelper::writeHtmlFile('root-cause', $html);
	    }
	    return $html;
    }
}
