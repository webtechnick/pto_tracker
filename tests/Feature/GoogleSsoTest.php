<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GoogleSsoTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.google.client_id' => 'test-client-id',
            'services.google.client_secret' => 'test-secret',
            'services.google.redirect' => 'http://localhost/login/google/callback',
            'services.google.domain' => 'continued.com',
        ]);
    }

    protected function googleUser($name = 'Jane Doe')
    {
        return (new SocialiteUser)->setToken('google-token')->map([
            'id' => '123',
            'name' => $name,
            'email' => 'jane.doe@continued.com',
        ]);
    }

    #[Test]
    public function calendar_requires_google_login_outside_local_environment()
    {
        $this->get('/')->assertRedirect('/login/google');
        $this->get('/oncall')->assertRedirect('/login/google');
    }

    #[Test]
    public function redirect_goes_to_google_restricted_to_the_configured_domain()
    {
        $response = $this->get('/login/google');

        $response->assertRedirect();
        $location = $response->headers->get('Location');
        $this->assertStringStartsWith('https://accounts.google.com/o/oauth2/auth', $location);

        parse_str(parse_url($location, PHP_URL_QUERY), $query);
        $this->assertSame('continued.com', $query['hd']);
        $this->assertSame('test-client-id', $query['client_id']);
        $this->assertSame('http://localhost/login/google/callback', $query['redirect_uri']);
        $this->assertNotEmpty($query['state']);
        $this->assertSame(session('state'), $query['state']);
    }

    #[Test]
    public function callback_stores_google_user_in_session_and_returns_home()
    {
        Socialite::shouldReceive('driver->user')->once()->andReturn($this->googleUser());

        $response = $this->get('/login/google/callback?state=abc&code=xyz');

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('GoogleToken', 'google-token');
        $this->assertSame('Jane Doe', session('GoogleUser')->getName());
    }

    #[Test]
    public function callback_with_invalid_state_clears_the_google_session()
    {
        $response = $this->withSession([
            'GoogleToken' => 'stale-token',
            'GoogleUser' => $this->googleUser(),
        ])->get('/login/google/callback?state=forged&code=xyz');

        $response->assertRedirect(route('home'));
        $response->assertSessionMissing('GoogleToken');
        $response->assertSessionMissing('GoogleUser');
    }

    #[Test]
    public function calendar_renders_for_a_google_authenticated_session()
    {
        $this->create('App\Employee', ['name' => 'Jane Doe']);

        $this->withSession([
            'GoogleToken' => 'google-token',
            'GoogleUser' => $this->googleUser(),
        ])->get('/')
            ->assertOk()
            ->assertSee('Jane Doe')
            ->assertSee('name="q"', false);
    }

    #[Test]
    public function google_user_is_fetched_from_token_when_missing_from_session()
    {
        Socialite::shouldReceive('driver->userFromToken')
            ->once()
            ->with('google-token')
            ->andReturn($this->googleUser());

        $this->withSession(['GoogleToken' => 'google-token'])
            ->get('/')
            ->assertOk()
            ->assertSessionHas('GoogleUser');
    }
}
