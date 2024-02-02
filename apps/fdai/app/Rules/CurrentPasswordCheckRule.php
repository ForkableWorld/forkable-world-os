<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Rules;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Hash;
class CurrentPasswordCheckRule implements Rule {
	/**
	 * Determine if the validation rule passes.
	 * @param string $attribute
	 * @param mixed $value
	 * @return bool
	 */
	public function passes($attribute, $value){
		return Hash::check($value, auth()->user()->password);
	}
	/**
	 * Get the validation error message.
	 * @return string
	 */
	public function message(){
		return __('The current password field does not match your password');
	}
}
