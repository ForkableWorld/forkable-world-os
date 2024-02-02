<?php

namespace Tests\UnitTests\Http\Controllers\Auth;

use App\Models\User;
use App\Properties\User\UserEmailProperty;
use App\Properties\User\UserPasswordProperty;
use Illuminate\Support\Facades\Auth;
use Tests\UnitTestCase;
class LoginControllerTest extends UnitTestCase
{

	public function it_visit_page_of_login()
	{
		$this->get('/login')
		     ->assertSee('Login');
	}

	/** @test */
/*	public function a_user_cannot_view_the_login_form_when_authenticated()
	{
		$user = User::testUser();

		$this->signIn($user);
		$response = $this->get('/login');
		$this->assertRedirect($response, '/home');
	}*/

	/** @test */
	public function authenticated_to_a_user()
	{
		$this->get('/login')->assertSee('Login');

		$credentials = [
			"email" => UserEmailProperty::TEST_EMAIL,
			"password" => UserPasswordProperty::TEST_USER_PASSWORD_18535
		];

		$response = $this->post('/login', $credentials);

		$this->assertRedirect($response, 'https://testing.quantimo.do/app/public/#/app/onboarding?loggingIn=1');

		// TODO: $this->assertCredentials($credentials);
	}

	/** @test */
	public function not_authenticate_to_a_user_with_credentials_invalid()
	{
		$user = User::createNewUser( [
			"email" => "user@mail.com"
		]);

		$credentials = [
			"email" => "users@mail.com",
			"password" => "secret"
		];

		$this->assertInvalidCredentials($credentials);
	}

	/** @test */
	public function remember_me_credentials_functionality()
	{
		$user = User::createNewUser( [
			'username' => 'testuser-'.time(),
			'password' => $password = '123456',
		]);
		$response = $this->from('/auth/login')->post('/auth/login', [
			'username' => $user->getUserName(),
			'password' => $password,
			'remember' => 'on',
		]);

		$this->assertRedirect($response, '/app/public/#/app/onboarding?loggingIn=1');
		$user = User::whereUserLogin($user->getUserName())->first();
		$hashedPassword = $user->password;
		$userId = $user->id;
		$rememberToken = $user->getRememberToken();
		$this->assertNotEmpty($rememberToken, 'Remember token is empty');
		$expectedValue = vsprintf('%s|%s|%s', [
			$userId,
			$rememberToken,
			$hashedPassword,
		]);
		$response->assertCookie(Auth::guard()->getRecallerName(), $expectedValue);

		$this->assertAuthenticatedAs($user);
	}

	/** @test */
	public function the_email_is_required_for_authenticate()
	{
		$user = User::testUser();

		$credentials = [
			"email" => null,
			"password" => "secret"
		];

		$response = $this->from('/login')->post('/login', $credentials);

		$this->assertRedirect($response, '/login')
			->assertSessionHasErrors();
//		         ->assertSessionHasErrors([
//			                                  'email' => 'The email field is required.',
//		                                  ]);
	}

	/** @test */
	public function the_password_is_required_for_authenticate()
	{
		$user = User::createNewUser( ['email' => 'zaratedev@gmail.com']);

		$credentials = [
			"email" => "zaratedev@gmail.com",
			"password" => null
		];

		$response = $this->from('/login')->post('/login', $credentials);

		$this->assertRedirect($response, '/login')
			->assertSessionHasErrors();
		// TODO
//		         ->assertSessionHasErrors([
//			                                  'password' => 'The password field is required.',
//		                                  ]);
	}

	/** @test */
	public function a_user_can_logout()
	{
		$this->signIn(User::testUser());
		$response = $this->post('/logout');
		// TODO: $this->assertRedirect($response, '/');
		$this->assertGuest();
	}

	/** @test */
	public function as_user_cannot_logout_when_not_authenticated()
	{
		$response = $this->post('/logout');
		// TODO: $this->assertRedirect($response, '/auth/login');
		$this->assertGuest();
	}

	
	/** @test */
/*	public function a_user_cannot_make_more_than_five_attempts_in_one_minute()
	{
		$user = User::testUser();

		foreach (range(0, 5) as $_) {
			$response = $this->from('/login')->post('/login', [
				'email' => $user->email,
				'password' => BasePasswordProperty::TEST_USER_PASSWORD,
			]);
		}

		$this->assertRedirect($response, '/login');
		$response->assertSessionHasErrors('email');

		$this->assertTrue(session()->hasOldInput('email'));
		$this->assertFalse(session()->hasOldInput('password'));
		$this->assertGuest();
	}*/
}
