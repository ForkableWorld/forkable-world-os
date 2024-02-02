<?php namespace App\Http\Controllers\Web;
use Illuminate\Support\Str;
use App\Exceptions\ExceptionHandler;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Lang;
use Redirect;
use Validator;
use View;
class GroupsController extends Controller {
	/**
	 * Show a list of all the groups.
	 * @return View
	 */
	public function getIndex(){
		// Grab all the groups
		$roles = Sentinel::getRoleRepository()->all();
		// Show the page
		return View::make('admin/groups/index', compact('roles'));
	}
	/**
	 * Group create.
	 * @return View
	 */
	public function getCreate(){
		// Show the page
		return View::make('admin/groups/create');
	}
	/**
	 * Group create form processing.
	 * @return RedirectResponse
	 */
	public function postCreate(){
		// Declare the rules for the form validation
		$rules = [
			'name' => 'required',
			'slug' => 'required|unique:roles',
		];
		//manually add slug to Input array for validation
		$this->getRequest()->merge(['slug' => Str::slug($this->getRequest()->get('name'))]);
		// Create a new validator instance from our validation rules
		$inputs = $this->getRequest()->all();
		$validator = Validator::make($inputs, $rules);
		// If validation fails, we'll exit the operation now.
		if($validator->fails()){
			return redirect()->back()->withInput()->withErrors($validator);
			//return $this->goBackWithErrorMessageQueryParam($validator, $inputs);
		}
		// Was the group created?
		if($role = Sentinel::getRoleRepository()->createModel()->create([
			'name' => $this->getRequest()->get('name'),
			'slug' => Str::slug($this->getRequest()->get('name')),
		])){
			// Redirect to the new group page
			return Redirect::route('groups')->with('success', Lang::get('groups/message.success.create'));
		}
		// Redirect to the new group page
		return Redirect::route('create/group')->with('error', Lang::get('groups/message.error.create'));
	}
	/**
	 * Group update.
	 * @param int $id
	 * @return View
	 */
	public function getEdit($id = null){
		try {
			// Get the group information
			$role = Sentinel::findRoleById($id);
		} catch (GroupNotFoundException $e) {
			// Redirect to the groups management page
			ExceptionHandler::logExceptionOrThrowIfLocalOrPHPUnitTest($e);
			return Redirect::route('groups')->with('error', Lang::get('groups/message.group_not_found', compact('id')));
		}
		// Show the page
		return View::make('admin/groups/edit', compact('role'));
	}
	/**
	 * Role update form processing page.
	 * @param int $id
	 * @return Redirect|RedirectResponse
	 */
	public function postEdit($id = null){
		if($role = Sentinel::findRoleById($id)){
			// Declare the rules for the form validation
			$rules = [
				'name' => 'required',
			];
			$inputs = $this->getRequest()->all();
			// Create a new validator instance from our validation rules
			$validator = Validator::make($inputs, $rules);
			// If validation fails, we'll exit the operation now.
			if($validator->fails()){
				return redirect()->back()->withInput()->withErrors($validator);
				//return $this->goBackWithErrorMessageQueryParam($validator, $inputs);
			}
			// Update the group data
			$role->name = $this->getRequest()->get('name');
			// Was the group updated?
			if($role->save()){
				// Redirect to the group page
				return Redirect::route('groups')->with('success', Lang::get('groups/message.success.update'));
			} else{
				// Redirect to the group page
				return Redirect::route('update/group', $id)->with('error', Lang::get('groups/message.error.update'));
			}
		} else{
			return Rediret::route('groups')->with('error', Lang::get('groups/message.group_not_found', compact('id')));
		}
	}
	/**
	 * Delete confirmation for the given group.
	 * @param int $id
	 * @return View
	 */
	public function getModalDelete($id = null){
		$model = 'groups';
		$confirm_route = $error = null;
		if($role = Sentinel::findRoleById($id)){
			$confirm_route = route('delete/group', ['id' => $role->id]);
			return View::make('admin/layouts/modal_confirmation', compact('error', 'model', 'confirm_route'));
		} else{
			$error = Lang::get('admin/groups/message.group_not_found', compact('id'));
			return View::make('admin/layouts/modal_confirmation', compact('error', 'model', 'confirm_route'));
		}
	}
	/**
	 * Delete the given group.
	 * @param int $id
	 * @return RedirectResponse
	 */
	public function getDelete($id = null){
		// Get group information
		if($role = Sentinel::findRoleById($id)){
			// Delete the group
			$role->delete();
			// Redirect to the group management page
			return Redirect::route('groups')->with('success', Lang::get('groups/message.success.delete'));
		} else{
			// Redirect to the group management page
			return Redirect::route('groups')->with('error', Lang::get('groups/message.group_not_found', compact('id')));
		}
	}
}/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */


