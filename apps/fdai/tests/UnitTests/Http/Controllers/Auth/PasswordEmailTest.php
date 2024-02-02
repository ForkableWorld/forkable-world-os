<?php

namespace Http\Controllers\Auth;

use App\Models\User;
use Tests\UnitTestCase;
class PasswordEmailTest extends UnitTestCase
{

    /** @test */
    public function it_visit_page_password_email()
    {
        $this->get('/password/reset')
            ->assertStatus(200)
            ->assertSee("Reset Password");
    }
    /** @todo */
    public function a_user_cannot_view_an_email_password_form_when_authenticated()
    {
        $user = User::testUser();
        $this->signIn($user);

        $response = $this->get('/password/reset');
        $this->assertRedirect($response, '/home');
    }

    /** @todo */
    public function a_user_can_reset_password_with_email_address()
    {
        $this->withExceptionHandling();

        $user = User::createNewUser( ["email" => "zaratedev@gmail.com"]);

        $response = $this->from('/password/reset')->post("/password/email", [
            "email" => "zaratedev@gmail.com"
        ]);

        $this->assertRedirect($response, '/password/reset')
            ->assertSessionHas([
            "status" => "We have e-mailed your password reset link!"
        ]);
    }

    /** @test */
    public function the_email_not_found_for_password_reset()
    {
        $this->withExceptionHandling();

        $user = User::testUser();

        $response = $this->from('/password/reset')->post("/password/email", [
            "email" => "zaratedev@gmail.com"
        ]);

        $this->assertRedirect($response, '/password/reset')
            ->assertSessionHasErrors([
                "email" => "We can't find a user with that e-mail address."
            ]);
    }

    /** @todo */
    public function the_email_is_required_for_password_reset()
    {
        $this->withExceptionHandling();

        $response = $this->from('/password/reset')->post("/password/email", [
            "email" => null
        ]);

        $this->assertRedirect($response, '/password/reset')
            ->assertSessionHasErrors([
                "email" => "The email field is required."
            ]);
    }

    /** @todo */
    public function the_email_is_not_valid_for_password_reset()
    {
        $this->withExceptionHandling();

        $response = $this->from('/password/reset')->post("/password/email", [
            "email" => "user@@email.com"
        ]);

        $this->assertRedirect($response, '/password/reset')
            ->assertSessionHasErrors([
                "email" => "The email must be a valid email address."
            ]);
    }
}
