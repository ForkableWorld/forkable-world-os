<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers;
use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Properties\User\UserPasswordProperty;
use App\Slim\Middleware\QMAuth;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\View\View;
class UserManagementController extends Controller {
	/**
	 * Display a listing of the users
	 * @param User $model
	 * @return RedirectResponse|Redirector|View
	 */
	public function index(User $model){
		if(!QMAuth::getAdminOrLogout()){
			return redirect('login');
		}
		return view('users.index', ['users' => $model->paginate(15)]);
	}
	/**
	 * Show the form for creating a new user
	 * @return View
	 */
	public function create(){
		return view('users.create');
	}
	/**
	 * Store a newly created user in storage
	 * @param UserRequest $request
	 * @param User $model
	 * @return RedirectResponse
	 */
	public function store(UserRequest $request, User $model){
		QMAuth::exceptionUnlessCurrentUserMatchesIdOrIsAdmin($model->ID);
		$plainText = $request->get('password');
		$hashed = UserPasswordProperty::hashPassword($plainText);
		$arr = $request->merge(['password' => $hashed])->all();
		$model->create($arr);
		return redirect()->route('user-management.index')->withStatus(__('User successfully created.'));
	}
	/**
	 * Show the form for editing the specified user
	 * @param User $user
	 * @return View
	 */
	public function edit(User $user){
		QMAuth::exceptionUnlessCurrentUserMatchesIdOrIsAdmin($user->ID);
		return view('users.edit', compact('user'));
	}
	/**
	 * Update the specified user in storage
	 * @param UserRequest $request
	 * @param User $user
	 * @return RedirectResponse
	 */
	public function update(UserRequest $request, User $user){
		QMAuth::exceptionUnlessCurrentUserMatchesIdOrIsAdmin($user->ID);
		$plainText = $request->get('password');
		$hashed = UserPasswordProperty::hashPassword($plainText);
		$attr = $request->merge(['password' => $hashed])->except([$plainText ? '' : 'password']);
		$user->update($attr);
		return redirect()->route('user-management.index')->withStatus(__('User successfully updated.'));
	}
	/**
	 * Remove the specified user from storage
	 * @param User $user
	 * @return RedirectResponse
	 * @throws Exception
	 */
	public function destroy(User $user){
		if(!QMAuth::getAdminOrLogout()){
			return redirect('login');
		}
		$user->delete();
		return redirect()->route('user-management.index')->withStatus(__('User successfully deleted.'));
	}
}
