<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Menus\Astral;
use App\Exceptions\NoIdException;
use App\Menus\BaseModelMenu;
use App\Models\BaseModel;
use App\Models\UserVariableRelationship;
use App\Policies\BasePolicy;
use App\Slim\Middleware\QMAuth;
use App\Traits\TestableTrait;
use App\Utils\AppMode;
use function le;
class SingleModelAstralMenu extends BaseModelMenu {
	/**
	 * @param null $tableOrModel
	 * @param string|null $title
	 */
	public function __construct($tableOrModel = null, string $title = null){
		parent::__construct($tableOrModel);
		$m = $this->getModel();
		if(!$this->fontAwesome){
			$this->fontAwesome = $m->getFontAwesome();
		}
		if(!$this->tooltip){
			$this->tooltip = $m->getTitleAttribute() . " Options";
		}
		if(!$this->backgroundColor){
			$this->backgroundColor = $m->getHexColor();
		}
		if($title){
			$this->title = $title;
		}
	}
	/**
	 * @inheritDoc
	 * @throws NoIdException
	 */
	public function getButtons(): array{
		$m = $this->getModel();
		$buttons = $this->buttons;
		$user = QMAuth::getQMUser();
		if($user){
			$user = $user->l();
		}
		$notAPI = !AppMode::isApiRequest();
		if($notAPI || $user->can(BasePolicy::POLICY_view, $m)){
			$buttons[] = $m->getAstralButton();
		} else{
			$user->can(BasePolicy::POLICY_view, $m);
			le("Why are we getting a menu for something the user isn't allowed to view? Here's the model: " . $m, $m);
		}
		if($notAPI || $user->can(BasePolicy::POLICY_update, $m)){
			$buttons[] = $m->getAstralEditButton();
		}
		// TODO
		//        if($notAPI || $user->can(BasePolicy::POLICY_delete, $m)){
		//            $buttons[] = $m->getAstralDeleteButton();
		//        }
		// TODO
		//        if($m->isAnalyzable()){
		//            $buttons[] = $m->getAstralAnalyzeButton();
		//        }
		if($m->hasColumn(UserVariableRelationship::FIELD_WP_POST_ID) && method_exists($m, 'getPostButton')){
			$buttons[] = $m->getPostButton();
		}
		if(QMAuth::isAdmin()){
			if(method_exists($m, 'getPhpUnitButton')){
				/** @var BaseModel|TestableTrait $m */
				$buttons[] = $m->getPhpUnitButton();
			}
			$buttons[] = $m->getAstralProfileButton();
		}
		$buttons = array_merge($buttons, $m->getActionButtons());
		$this->addButtons($buttons);
		return $this->buttons;
	}
}
