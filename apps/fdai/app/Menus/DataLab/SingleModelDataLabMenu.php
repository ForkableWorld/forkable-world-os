<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Menus\DataLab;
use App\Exceptions\NoIdException;
use App\Menus\Astral\SingleModelAstralMenu;
use App\Models\BaseModel;
use App\Models\UserVariableRelationship;
use App\Policies\BasePolicy;
use App\Slim\Middleware\QMAuth;
use App\Traits\TestableTrait;
use App\Utils\AppMode;
use function le;
class SingleModelDataLabMenu extends SingleModelAstralMenu {
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
			$buttons[] = $m->getDataLabOpenButton();
		} else{
			$user->can(BasePolicy::POLICY_view, $m);
			le("Why are we getting a menu for something the user isn't allowed to view? Here's the model: " . $m, $m);
		}
		if($notAPI || $user->can(BasePolicy::POLICY_update, $m)){
			$buttons[] = $m->getDataLabEditButton();
		}
		if($notAPI || $user->can(BasePolicy::POLICY_delete, $m)){
			$buttons[] = $m->getDataLabDeleteButton();
		}
		if($m->isAnalyzable()){
			$buttons[] = $m->getDataLabAnalyzeButton();
		}
		if($m->hasColumn(UserVariableRelationship::FIELD_WP_POST_ID) && method_exists($m, 'getPostButton')){
			$buttons[] = $m->getPostButton();
		}
		if(QMAuth::isAdmin()){
			if(method_exists($m, 'getPhpUnitButton')){
				/** @var BaseModel|TestableTrait $m */
				$buttons[] = $m->getPhpUnitButton();
			}
			$buttons[] = $m->getDataLabProfileButton();
		}
		$buttons = array_merge($buttons, $m->getActionButtons());
		$this->addButtons($buttons);
		return $this->buttons;
	}
}
