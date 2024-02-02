<?php

namespace Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\UnitTestCase;
use function Tests\Feature\create;
class PasswordResetTest extends UnitTestCase
{

    /** @test */
    public function it_visit_page_password_email()
    {
        $this->get('/password/reset/efd886f6a2117dbaebd504e0d3ceac3f3c58a4458933cb88cb5ffe4e2217474e')
            ->assertStatus(200)
            ->assertSee("Reset Password");
    }

    /** @todo */
    public function a_user_can_reset_his_password()
    {
        $user = User::createNewUser( ["email" => "zaratedev@gmail.com"]);

        $response = $this->post('/password/reset', [
            "token" => Password::broker()->createToken($user),
            "email" => "zaratedev@gmail.com",
            "password" => "123456",
            "password_confirmation" => "123456"
        ]);

        $this->assertRedirect($response, '/home');
    }

    /** @todo */
    public function a_user_cannot_view_password_reset_form_when_authenticated()
    {
        $user = User::testUser();

        $this->signIn($user);

        $response = $this->get('/password/reset/' . Password::broker()->createToken($user));

        $this->assertRedirect($response, '/home');
    }

    /** @todo */
    public function a_user_cannot_reset_password_with_invalid_token()
    {
        $user = User::createNewUser([
            'password' => bcrypt('123456'),
        ]);

        $response = $this->from('/password/reset/token-invalid')->post('/password/reset', [
            'token' => 'token-invalid',
            'email' => $user->email,
            'password' => 'qwerty',
            'password_confirmation' => 'qwerty',
        ]);

        $this->assertRedirect($response, '/password/reset/token-invalid');
        $this->assertEquals($user->email, $user->fresh()->email);
        $this->assertTrue(Hash::check('123456', $user->fresh()->password));
        $this->assertGuest();
    }

    /** @todo */
    public function a_user_cannot_reset_password_without_confirmation_password()
    {
        $user = User::createNewUser([
            'password' => bcrypt('123456'),
        ]);

        $response = $this->from('/password/reset/' . $token = Password::broker()->createToken($user))->post('/password/reset', [
            'token' => $token,
            'email' => $user->email,
            'password' => '',
            'password_confirmation' => '',
        ]);

        $this->assertRedirect($response, '/password/reset/' . $token);
        $response->assertSessionHasErrors('password');
        $this->assertTrue(session()->hasOldInput('email'));
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertEquals($user->email, $user->fresh()->email);
        $this->assertTrue(Hash::check('123456', $user->fresh()->password));
        $this->assertGuest();
    }

    /** @todo */
    public function a_user_cannot_reset_password_without_email()
    {
        $user = User::createNewUser([
            'password' => bcrypt('123456'),
        ]);

        $response = $this->from('/password/reset/' . $token = Password::broker()->createToken($user))->post('/password/reset', [
            'token' => $token,
            'email' => '',
            'password' => 'qwerty',
            'password_confirmation' => 'qwerty',
        ]);

        $this->assertRedirect($response, '/password/reset/' . $token);
        $response->assertSessionHasErrors('email');
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertEquals($user->email, $user->fresh()->email);
        $this->assertTrue(Hash::check('123456', $user->fresh()->password));
        $this->assertGuest();
    }
}
