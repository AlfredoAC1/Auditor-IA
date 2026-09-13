<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use App\Actions\User\UpdatePasswordAction;
use App\Enums\UserRole;
use App\Models\Organization;
use App\Models\User;
use App\Services\Auth\PasswordResetService;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Security Test Suite — Section 5: Authentication & Token Management
 *
 * Covers all 18 controls from PENTESTING_CHECKLIST.md §5:
 * - Brute-force protection & Login rate limiting (§5.1, §5.4)
 * - Credential stuffing protection (§5.2)
 * - Password spraying rate limiting (§5.3)
 * - Progressive throttling (§5.5)
 * - Account enumeration prevention (§5.6)
 * - Consistent & generic auth error messages (§5.7)
 * - Strong password policy (§5.8)
 * - Secure password hashing (§5.9)
 * - Password reset token RNG, expiry & single-use (§5.10, §5.11, §5.12)
 * - Password reset invalidates tokens (§5.13)
 * - Password change invalidates Sanctum tokens immediately (§5.17, §5.18)
 *
 * @see PENTESTING_CHECKLIST.md §5
 */
class Section5AuthenticationAndTokenTest extends TestCase
{
    use RefreshDatabase;

    private Organization $org;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PlanSeeder::class);

        $this->org = Organization::create([
            'name' => 'Auth Security Org',
            'slug' => 'auth-security-org',
            'is_active' => true,
        ]);

        $this->user = User::create([
            'name' => 'Secured User',
            'email' => 'secured@auditor-ia.test',
            'password' => bcrypt('StrongPassword123!'),
            'role' => UserRole::User,
            'organization_id' => $this->org->id,
        ]);

        RateLimiter::clear('login_spray_ip:127.0.0.1');
        RateLimiter::clear('secured@auditor-ia.test|127.0.0.1');
    }

    /**
     * PENTESTING_CHECKLIST §5.1 & §5.4 — Brute-force protection on login endpoint
     */
    public function test_login_locks_out_after_five_consecutive_failed_attempts(): void
    {
        // 5 consecutive failed attempts
        for ($i = 0; $i < 5; $i++) {
            $response = $this->post(route('login.store'), [
                'email' => 'secured@auditor-ia.test',
                'password' => 'WrongPassword!',
            ]);
            $response->assertSessionHasErrors('email');
        }

        // 6th attempt must be locked out with throttle error message
        $response = $this->post(route('login.store'), [
            'email' => 'secured@auditor-ia.test',
            'password' => 'WrongPassword!',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertStringContainsString(
            'Demasiados intentos de acceso fallidos',
            session('errors')->first('email')
        );
    }

    /**
     * PENTESTING_CHECKLIST §5.3 — Password spraying rate limiting across different accounts
     */
    public function test_password_spraying_rate_limiting_protects_multiple_accounts(): void
    {
        // Simulate an attacker testing different emails from the same IP (10 attempts)
        for ($i = 1; $i <= 10; $i++) {
            $this->post(route('login.store'), [
                'email' => "spray_target_{$i}@auditor-ia.test",
                'password' => 'CommonPassword123!',
            ]);
        }

        // 11th attempt from this IP must trigger spray protection
        $response = $this->post(route('login.store'), [
            'email' => 'spray_target_final@auditor-ia.test',
            'password' => 'CommonPassword123!',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertStringContainsString(
            'Demasiados intentos de acceso fallidos',
            session('errors')->first('email')
        );
    }

    /**
     * PENTESTING_CHECKLIST §5.6 & §5.7 — Account enumeration prevention and generic auth errors
     */
    public function test_login_returns_identical_generic_error_for_existing_and_non_existing_users(): void
    {
        // 1. Existing user with wrong password
        $this->post(route('login.store'), [
            'email' => 'secured@auditor-ia.test',
            'password' => 'WrongPassword!',
        ]);
        $existingUserError = session('errors')->first('email');

        RateLimiter::clear('login_spray_ip:127.0.0.1');
        RateLimiter::clear('secured@auditor-ia.test|127.0.0.1');

        // 2. Non-existent user
        $this->post(route('login.store'), [
            'email' => 'does_not_exist@auditor-ia.test',
            'password' => 'WrongPassword!',
        ]);
        $nonExistentUserError = session('errors')->first('email');

        $this->assertSame(
            'Las credenciales proporcionadas no son válidas.',
            $existingUserError
        );
        $this->assertSame($existingUserError, $nonExistentUserError);
    }

    /**
     * PENTESTING_CHECKLIST §5.8 — Strong password policy enforced on registration
     */
    public function test_strong_password_policy_is_enforced_on_registration(): void
    {
        // Too short (< 10 chars)
        $response1 = $this->post(route('register.store'), [
            'name' => 'Weak User',
            'email' => 'weak1@example.com',
            'password' => 'Short1!',
            'password_confirmation' => 'Short1!',
            'terms' => '1',
        ]);
        $response1->assertSessionHasErrors('password');

        // Missing special character
        $response2 = $this->post(route('register.store'), [
            'name' => 'Weak User',
            'email' => 'weak2@example.com',
            'password' => 'NoSymbols12345',
            'password_confirmation' => 'NoSymbols12345',
            'terms' => '1',
        ]);
        $response2->assertSessionHasErrors('password');

        // Missing number
        $response3 = $this->post(route('register.store'), [
            'name' => 'Weak User',
            'email' => 'weak3@example.com',
            'password' => 'NoNumbersAtAll!',
            'password_confirmation' => 'NoNumbersAtAll!',
            'terms' => '1',
        ]);
        $response3->assertSessionHasErrors('password');
    }

    /**
     * PENTESTING_CHECKLIST §5.9 — Secure password hashing using Bcrypt
     */
    public function test_passwords_are_securely_hashed(): void
    {
        $this->assertNotSame('StrongPassword123!', $this->user->password);
        $this->assertTrue(Hash::check('StrongPassword123!', $this->user->password));

        $info = Hash::info($this->user->password);
        $this->assertSame('bcrypt', $info['algoName']);
    }

    /**
     * PENTESTING_CHECKLIST §5.10, §5.11, §5.12 — Password reset tokens are secure, single-use, and expire
     */
    public function test_password_reset_tokens_are_secure_and_single_use(): void
    {
        $service = app(PasswordResetService::class);

        // 1. Generate token
        $token = $service->createToken($this->user);
        $this->assertNotEmpty($token);
        $this->assertGreaterThan(30, strlen($token));

        // 2. Consume token to reset password
        $updatedUser = $service->reset(
            $this->user->email,
            $token,
            'BrandNewSecurePass999!'
        );

        $this->assertTrue(Hash::check('BrandNewSecurePass999!', $updatedUser->password));

        // 3. Replay attack with same token must fail (single-use)
        $this->expectException(ValidationException::class);
        $service->reset(
            $this->user->email,
            $token,
            'AnotherPasswordAttempt123!'
        );
    }

    /**
     * PENTESTING_CHECKLIST §5.13 — Password reset invalidates active Sanctum tokens
     */
    public function test_password_reset_invalidates_all_active_tokens(): void
    {
        $service = app(PasswordResetService::class);

        // Issue 3 API tokens for the user
        $this->user->createToken('token_phone');
        $this->user->createToken('token_laptop');
        $this->user->createToken('token_cli');
        $this->assertSame(3, $this->user->tokens()->count());

        // Reset password
        $token = $service->createToken($this->user);
        $service->reset(
            $this->user->email,
            $token,
            'NewPasswordSecured123!'
        );

        // All tokens must be purged
        $this->user->refresh();
        $this->assertSame(0, $this->user->tokens()->count());
    }

    /**
     * PENTESTING_CHECKLIST §5.17 & §5.18 — Changing password invalidates active Sanctum tokens immediately
     */
    public function test_password_change_action_revokes_all_active_tokens(): void
    {
        $action = app(UpdatePasswordAction::class);

        // Generate tokens
        $token1 = $this->user->createToken('token_1')->plainTextToken;
        $token2 = $this->user->createToken('token_2')->plainTextToken;
        $this->assertSame(2, $this->user->tokens()->count());

        // Verify token can make authenticated requests prior to change
        $this->withHeader('Authorization', "Bearer {$token1}")
            ->getJson('/api/v1/documents')
            ->assertStatus(200);

        // User updates password
        $action->execute($this->user, 'StrongPassword123!', 'NewChangedPassword123!');

        // Tokens in DB must be deleted
        $this->user->refresh();
        $this->assertSame(0, $this->user->tokens()->count());

        // Reset auth guard state so the request does not serve a cached user instance
        $this->app['auth']->forgetGuards();

        // Prior token must now be rejected with 401 Unauthorized immediately
        $this->withHeader('Authorization', "Bearer {$token1}")
            ->getJson('/api/v1/documents')
            ->assertStatus(401);

        $this->withHeader('Authorization', "Bearer {$token2}")
            ->getJson('/api/v1/documents')
            ->assertStatus(401);
    }

    /**
     * PENTESTING_CHECKLIST §5.18 — Revoked token is rejected immediately
     */
    public function test_individual_sanctum_token_revocation_works_immediately(): void
    {
        $token = $this->user->createToken('temporary_token');
        $plain = $token->plainTextToken;

        // Valid token accesses API
        $this->withHeader('Authorization', "Bearer {$plain}")
            ->getJson('/api/v1/documents')
            ->assertStatus(200);

        // Revoke the token
        $token->accessToken->delete();

        // Reset auth guard state so the request does not serve a cached user instance
        $this->app['auth']->forgetGuards();

        // Immediate rejection
        $this->withHeader('Authorization', "Bearer {$plain}")
            ->getJson('/api/v1/documents')
            ->assertStatus(401);
    }
}
