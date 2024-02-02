<?php

namespace Http\Controllers\Auth;

use App\Mail\ConfirmedYourEmail;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Mail;
use Tests\UnitTestCase;
class RegistrationTest extends UnitTestCase
{
    /** @test */
    public function it_visit_page_of_register()
    {
        $this->get('/register')
            ->assertSee('Register');
    }

    /** @todo */
    public function cannot_view_registration_form_when_authenticated()
    {
        $user = User::testUser();
        $this->signIn($user);
        $response = $this->get('/register');
        $this->assertRedirect($response, '/home');
    }


    /** @todo */
    public function user_can_registered_in_the_site_web()
    {
        $response = $this->post('/register', [
            'name'                   => 'Jonathan',
            'last_name'              => 'zarate hernandez',
            'email'                  => 'zaratedev@gmail.com',
            'password'               => '123456',
            'password_confirmation'  => '123456',
            'job'                    => 'developer',
        ]);

        $this->assertRedirect($response, '/home');

        $this->assertCredentials([
            'name'                   => 'Jonathan',
            'last_name'              => 'zarate hernandez',
            'email'                  => 'zaratedev@gmail.com',
            'password'               => '123456',
            'password_confirmation'  => '123456',
            'job'                    => 'developer',
        ]);
    }

    /** @todo */
    public function the_name_is_required()
    {
        $response = $this->from('/register')->post('/register', [
            'name'                  => null,
            'last_name'              => 'zarate hernandez',
            'email'                  => 'zaratedev@gmail.com',
            'password'               => '123456',
            'password_confirmation'  => '123456',
            'job'                    => 'developer',
        ]);

        $this->assertRedirect($response, '/register')
                 ->assertSessionHasErrors();
/*                    ->assertSessionHasErrors([
                        'name' => 'The name field is required.',
                    ]);*/

        $this->assertDatabaseMissing('users', [
            'email' => 'zaratedev@gmail.com'
        ]);
    }

    /** @todo */
    public function the_name_has_more_of_255_characters()
    {
        $response = $this->from('/register')->post('/register', [
            'name'                  => "Lorem ipsum dolor sit amet consectetur adipiscing elit sapien, aenean suspendisse mattis volutpat sollicitudin condimentum hendrerit praesent, montes nec tempor habitant blandit id class. Sem mollis semper fames risus torquent maecenas, in bibendum litora justo pellentesque porta, vel montes molestie nascetur ligula.",
            'last_name'              => 'zarate hernandez',
            'email'                  => 'zaratedev@gmail.com',
            'password'               => '123456',
            'password_confirmation'  => '123456',
            'job'                    => 'developer',
        ]);

        $this->assertRedirect($response, '/register')
                 ->assertSessionHasErrors();
/*            ->assertSessionHasErrors([
                'name' => 'The name may not be greater than 255 characters.',
            ]);*/

        $this->assertDatabaseMissing('users', [
            'email' => 'zaratedev@gmail.com'
        ]);
    }

    /** @todo */
    public function the_password_has_how_minimum_six_characters()
    {
        $this->withExceptionHandling();

        $response = $this->from('/register')->post('/register', [
            'name'                  => 'Jonathan',
            'last_name'              => 'zarate',
            'email'                  => 'zaratedev@gmail.com',
            'password'               => "123",
            'password_confirmation'  => "123",
            'job'                    => 'developer',
        ]);

        $this->assertRedirect($response, '/register')
            ->assertSessionHasErrors([
                "password" => "The password must be at least 6 characters."
            ]);

        $this->assertDatabaseMissing('users', [
            "email" => "zaratedev@gmail.com"
        ]);
    }


    /** @todo */
    public function a_confirmation_email_is_sent_upon_registration()
    {
        Mail::fake();

        event(new Registered(User::factory()->create()));

        Mail::assertSent(ConfirmedYourEmail::class);
    }

    /** @todo */
    public function user_can_fully_confirm_their_email_address()
    {
        $this->from('/register')->post('/register', [
            'last_name'              => 'zarate hernandez',
            'email'                  => 'zaratedev@gmail.com',
            'password'               => '123456',
            'password_confirmation'  => '123456',
            'job'                    => 'developer',
        ]);

        $user = User::whereUserEmail('zaratedev@gmail.com')->first();

        $this->assertFalse($user->confirmed);
        $this->assertNotNull($user->confirmation_token);


        // Let the user confirmed their account
        $this->get('/register/confirm?token='. $user->confirmation_token);
        $this->assertTrue($user->fresh()->confirmed);
    }

}
