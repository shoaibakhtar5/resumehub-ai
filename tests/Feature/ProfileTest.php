<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_unverified_users_are_redirected_from_profile_when_verification_is_required(): void
    {
        config()->set('features.email_verification', true);

        $user = User::factory()->unverified()->create();

        // The profile route is protected by VerifyEmailFeatureFlag middleware.
        // When email verification is enabled, unverified users should be redirected.
        $middleware = new \App\Http\Middleware\VerifyEmailFeatureFlag();
        $request = \Illuminate\Http\Request::create('/profile', 'GET');
        $request->setUserResolver(fn () => $user);

        $response = $middleware->handle($request, fn ($req) => new \Illuminate\Http\Response('OK'));

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(route('verification.notice'), $response->headers->get('Location'));
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_blank_regional_fields_are_preserved_and_profile_photo_is_stored(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['timezone' => 'UTC', 'locale' => 'en']);

        $response = $this->actingAs($user)->patch('/profile', [
            'name' => 'Photo User',
            'email' => $user->email,
            'timezone' => '',
            'locale' => '',
            'profile_photo' => UploadedFile::fake()->createWithContent(
                'avatar.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='),
            ),
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect('/profile');
        $user->refresh();

        $this->assertSame('UTC', $user->timezone);
        $this->assertSame('en', $user->locale);
        $this->assertStringStartsWith('/storage/profile-photos/', $user->profile_photo_path);
        $this->assertSame($user->profile_photo_path, $user->profile_photo_url);
        Storage::disk('public')->assertExists(Str::after($user->profile_photo_path, '/storage/'));
    }

    public function test_user_settings_are_functional_and_persist_preferences(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('settings'))
            ->assertOk()
            ->assertSee('Language and region');

        $this->patch(route('settings.update'), [
            'timezone' => 'Asia/Karachi',
            'locale' => 'en-GB',
        ])->assertSessionHasNoErrors()->assertRedirect(route('settings'));

        $user->refresh();
        $this->assertSame('Asia/Karachi', $user->timezone);
        $this->assertSame('en-GB', $user->locale);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }
}
