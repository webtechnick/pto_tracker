<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use DatabaseMigrations;

    #[Test]
    public function login_page_renders()
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('name="_token"', false)
            ->assertSee('name="email"', false)
            ->assertSee('name="password"', false);
    }

    #[Test]
    public function user_can_log_in_with_valid_credentials()
    {
        $user = $this->create(User::class, ['email' => 'jane.doe@continued.com']);

        $response = $this->post('/login', [
            'email' => 'jane.doe@continued.com',
            'password' => 'secret',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
    }

    #[Test]
    public function user_cannot_log_in_with_wrong_password()
    {
        $this->create(User::class, ['email' => 'jane.doe@continued.com']);

        $response = $this->from('/login')->post('/login', [
            'email' => 'jane.doe@continued.com',
            'password' => 'wrong',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    #[Test]
    public function authenticated_user_is_redirected_away_from_login_page()
    {
        $this->signIn();

        $this->get('/login')->assertRedirect('/');
    }

    #[Test]
    public function user_can_log_out()
    {
        $this->signIn();

        $this->post('/logout')->assertRedirect('/');

        $this->assertGuest();
    }

    #[Test]
    public function guests_are_redirected_to_login_from_admin_and_manager_pages()
    {
        $this->get('/admin/users')->assertRedirect('/login');
        $this->get('/manager')->assertRedirect('/login');
    }

    #[Test]
    public function non_admin_users_cannot_reach_admin_pages()
    {
        $this->signInManager();

        $this->get('/admin/users')->assertRedirect('/login');
        $this->get('/manager')->assertOk();
    }

    #[Test]
    public function password_reset_request_page_renders()
    {
        $this->get('/password/reset')->assertOk();
    }

    #[Test]
    public function password_reset_link_is_sent()
    {
        Notification::fake();
        $user = $this->create(User::class);

        $this->from('/password/reset')
            ->post('/password/email', ['email' => $user->email])
            ->assertRedirect('/password/reset')
            ->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPassword::class);
    }

    #[Test]
    public function laravel_6_cost_10_password_hashes_still_log_in_and_are_rehashed()
    {
        $legacyHash = password_hash('secret', PASSWORD_BCRYPT, ['cost' => 10]);
        $user = $this->create(User::class, ['password' => $legacyHash]);

        $this->post('/login', ['email' => $user->email, 'password' => 'secret'])->assertRedirect('/');

        $this->assertAuthenticatedAs($user);
        $rehashed = $user->fresh()->password;
        $this->assertNotSame($legacyHash, $rehashed);
        $this->assertSame((int) config('hashing.bcrypt.rounds'), password_get_info($rehashed)['options']['cost']);
        $this->assertTrue(password_verify('secret', $rehashed));
    }
}
