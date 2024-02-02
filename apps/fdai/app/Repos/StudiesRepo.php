<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Repos;
use App\Computers\ThisComputer;
use App\Models\Variable;
use App\Properties\User\UserIdProperty;
use App\Variables\CommonVariables\EmotionsCommonVariables\AnxietyNervousnessCommonVariable;
use Illuminate\View\View;
use Throwable;
class StudiesRepo extends GitRepo {
	public static $REPO_NAME = 'qm-studies';
	public const DEFAULT_BRANCH = 'static';
	public static function build(){
		ThisComputer::exec("bundle install");
	}
	public static function testPublish(){
		static::checkout(static::DEFAULT_BRANCH);
		//VoteGlobalVariableRelationshipIdProperty::updateAll();
		//VoteCorrelationIdProperty::updateAll();
		$ket = Variable::findByName("Prozac");
		$t = $ket->treatment();
		$se = $t->ct_treatment_side_effects()->get();
		$ct_condition_treatment = $t->ct_condition_treatment()->get();
		$condition_variables = $ket->condition_variables_where_treatment();
		$ket->publish();
		return;
		$mike = User::mike();
		$mike->publishUpVotedStudies();
		$cv = AnxietyNervousnessCommonVariable::instance();
		$uv = $cv->getOrCreateUserVariable(UserIdProperty::USER_ID_MIKE);
		$r = $uv->getRootCauseAnalysis();
		$r->uploadDynamicHtml();
	}
	/**
	 * @param string $path
	 * @param string|View $html
	 */
	public static function writeHtml(string $path, $html){
		if(!is_string($html)){
			try {
				$html = $html->render();
			} catch (Throwable $e) {
				le($e);
				throw new \LogicException();
			}
		}
		if(strpos($path, ".html") === false){
			$path .= ".html";
		}
		static::writeToFile('_static/' . $path, $html);
	}
}
