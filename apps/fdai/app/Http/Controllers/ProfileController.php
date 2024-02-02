<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers;
use App\Http\Requests\PasswordRequest;
use App\Http\Requests\ProfileRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
class ProfileController extends Controller {
	/**
	 * Show the form for editing the profile.
	 * @return View
	 */
	public function edit(){
		return view('user.profile.edit');
	}
	/**
	 * Update the profile
	 * @param ProfileRequest $request
	 * @return RedirectResponse
	 */
	public function update(ProfileRequest $request){
		auth()->user()->update($request->all());
		return back()->withStatus(__('Profile successfully updated.'));
	}
	/**
	 * Change the password
	 * @param PasswordRequest $request
	 * @return RedirectResponse
	 */
	public function password(PasswordRequest $request){
		$plainText = $request->get('password');
		/** @var User $u */
		$u = auth()->user();
		$u->setPlainTextPassword($plainText);
		$u->save();
		return back()->withStatusPassword(__('Password successfully updated.'));
	}
}
