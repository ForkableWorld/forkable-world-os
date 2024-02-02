<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Actions;
use App\Buttons\GithubButton;
use App\Exceptions\CredentialsNotFoundException;
use OAuth\Common\Exception\Exception;
use OAuth\Common\Token\Exception\ExpiredTokenException;
class GenerateGithubButtonsAction extends AdminAction {
	/**
	 * Determine if the user is authorized to make this action.
	 * @return bool
	 */
	public function authorize(){
		return true;
	}
	/**
	 * Get the validation rules that apply to the action.
	 * @return array
	 */
	public function rules(){
		return [];
	}
	/**
	 * Execute the action and return a result.
	 * @return mixed
	 * @throws CredentialsNotFoundException
	 * @throws Exception
	 * @throws ExpiredTokenException
	 */
	public function handle(){
		GithubButton::generate();
	}
}
