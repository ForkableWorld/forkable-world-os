<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Rules;
use Illuminate\Contracts\Validation\Rule;
class NotJson implements Rule {
	/**
	 * Create a new rule instance.
	 * @return void
	 */
	public function __construct(){
		//
	}
	/**
	 * Determine if the validation rule passes.
	 * @param string $attribute
	 * @param mixed $value
	 * @return bool
	 */
	public function passes($attribute, $value){
		return strpos($value, "{") === false;
	}
	/**
	 * Get the validation error message.
	 * @return string
	 */
	public function message(){
		return 'The validation error message.';
	}
}
