<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Requests;
use App\Rules\CurrentPasswordCheckRule;
use App\UI\HtmlHelper;
use App\Utils\UrlHelper;
use Illuminate\Foundation\Http\FormRequest;
class PasswordRequest extends FormRequest {
	/**
	 * Determine if the user is authorized to make this request.
	 * @return bool
	 */
	public function authorize(){
		return auth()->check();
	}
	/**
	 * Get the validation rules that apply to the request.
	 * @return array
	 */
	public function rules(){
		return [
			'old_password' => ['required', 'min:6', new CurrentPasswordCheckRule],
			'password' => ['required', 'min:6', 'confirmed', 'different:old_password'],
			'password_confirmation' => ['required', 'min:6'],
		];
	}
	/**
	 * Get the validation attributes that apply to the request.
	 * @return array
	 */
	public function attributes(){
		return [
			'old_password' => __('current password'),
		];
	}
	public static function getUrl(string $email = null, array $params = []): string{
		if($email){
			$params['email'] = $email;
		}
		return UrlHelper::getUrl('auth/password/reset', $params);
	}
	public static function getLink(string $email = null, array $params = []): string{
		$url = self::getUrl($email, $params);
		return HtmlHelper::generateLink($url, $url, false);
	}
}
