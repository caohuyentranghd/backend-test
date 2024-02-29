<?php

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use App\Events\SendMailVerificationEvent;
use App\Events\SendMailCodeForgotPasswordEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Faker\Factory as Faker;

class AuthControllerTest extends TestCase
{
    // use RefreshDatabase;

    public $user;
    public $faker;

    public function setUp(): void
    {
        parent::setUp();
        $this->faker = Faker::create();
        $this->user = User::factory()->state(['role' => ROLE_USER_ADMIN])->create();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $this->user->delete();
    }

    // For test register user and send mail verify success
    public function test_register_user_successfully()
    {
        $requestData = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => $this->faker->password,
        ];
        Event::fake();
        $response = $this->postJson('/api/v1/register', $requestData);
        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJson([
                "success" => true,
                "code" => Response::HTTP_CREATED,
            ]);
        Event::assertDispatched(SendMailVerificationEvent::class, function ($event) use ($requestData) {
            return $event->user->email === $requestData['email'];
        });
    }

    // For test register user but request param invalid
    public function test_register_user_with_invalid_data_should_fail()
    {
        $requestData = [
            'name' => '',
            'email' => 'invalid-email',
            'password' => 'password',
        ];
        $response = $this->postJson('/api/v1/register', $requestData);
        $response->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson([
                "success" => false,
                "code" => Response::HTTP_BAD_REQUEST,
            ]);
        $this->assertDatabaseMissing('users', [
            'email' => $requestData['email'],
        ]);
    }

    // For test verify email success
    public function test_verify_email_successfully()
    {
        $token = Str::random(40) . time();
        $user = User::factory()->create(['verification_token' => $token, 'email_verified_at' => null]);
        $requestData = ['token' => $token];
        $response = $this->postJson('/api/v1/verify-email', $requestData);
        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "success" => true,
                "code" => Response::HTTP_OK,
                "message" => __('common.msg_email_verify_success'),
            ]);
        $user->refresh();

        $this->assertEquals(null, $user['verification_token']);
    }

    // For test verify email but token invalid
    public function test_verify_email_with_invalid_token()
    {
        $requestData = ['token' => 'invalid_token'];
        $response = $this->postJson('/api/v1/verify-email', $requestData);
        $response->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson([
                "success" => false,
                "code" => Response::HTTP_BAD_REQUEST,
                "message" => __('common.msg_valid_fails')
            ]);
    }

    // For test verify email but token not match with user
    public function test_verify_email_with_token_not_match_with_user()
    {
        User::factory()->create(['verification_token' => Str::random(40) . time(), 'email_verified_at' => null]);
        $requestData = ['token' => Str::random(40) . time()];
        $response = $this->postJson('/api/v1/verify-email', $requestData);
        $response->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson([
                "success" => false,
                "code" => Response::HTTP_BAD_REQUEST,
                "message" => __('common.msg_invalid_verify_token')
            ]);
    }

    // For test send mail code forgot passworld success
    public function test_send_reset_link_email_successfully()
    {
        $requestData = ['email' => $this->user->email];
        Event::fake();
        $response = $this->postJson('/api/v1/forgot-password', $requestData);
        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "success" => true,
                "code" => Response::HTTP_OK,
            ]);
        Event::assertDispatched(SendMailCodeForgotPasswordEvent::class, function ($event) use ($requestData) {
            return $event->user->email === $requestData['email'];
        });
    }

    // For test register user but request param invalid
    public function test_send_reset_link_email_with_invalid_data_should_fail()
    {
        $requestData = ['email' => 'email_test@gmail.com'];
        $response = $this->postJson('/api/v1/register', $requestData);
        $response->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson([
                "success" => false,
                "code" => Response::HTTP_BAD_REQUEST,
            ]);
        $this->assertDatabaseMissing('users', [
            'email' => $requestData['email'],
        ]);
    }

    // For test reset password success
    public function test_reset_password_successfully()
    {
        $now = new \DateTime();
        $code = Str::random(10);
        $data = [
            'expiry' => $now->modify('+15 minutes')->format('Y-m-d H:i:s'),
            'code' => $code,
        ];
        $user = User::factory()->state(['code_forgot_password' => json_encode($data)])->create();
        $requestData = [
            'email' => $user->email,
            'code' => $code,
            'password' => 'new_password',
            'password_confirmation' => 'new_password',
        ];

        $response = $this->postJson('/api/v1/reset-password', $requestData);
        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "success" => true,
                "code" => Response::HTTP_OK,
            ]);
    }

    // For test reset password with code forgot invalid
    public function test_reset_password_with_code_forgot_invalid()
    {
        $now = new \DateTime();
        $code = Str::random(10);
        $data = [
            'expiry' => $now->modify('+15 minutes')->format('Y-m-d H:i:s'),
            'code' => $code,
        ];
        $user = User::factory()->state(['code_forgot_password' => json_encode($data)])->create();
        $requestData = [
            'email' => $user->email,
            'code' => Str::random(10),
            'password' => 'new_password',
            'password_confirmation' => 'new_password',
        ];

        $response = $this->postJson('/api/v1/reset-password', $requestData);
        $response->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson([
                "success" => false,
                "message" => __('common.msg_find_not_found', ['data' => 'user']),
                "code" => Response::HTTP_BAD_REQUEST,
            ]);
    }

    // For test reset password with code forgot expiry
    public function test_reset_password_with_code_forgot_expiry()
    {
        $now = new \DateTime();
        $code = Str::random(10);
        $data = [
            'expiry' => $now->modify('+20 minutes')->format('Y-m-d H:i:s'),
            'code' => $code,
        ];
        $user = User::factory()->state(['code_forgot_password' => json_encode($data)])->create();
        $requestData = [
            'email' => $user->email,
            'code' => $code,
            'password' => 'new_password',
            'password_confirmation' => 'new_password',
        ];

        $response = $this->postJson('/api/v1/reset-password', $requestData);
        $response->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson([
                "success" => false,
                "message" => __('common.msg_code_expiry_or_wrong'),
                "code" => Response::HTTP_BAD_REQUEST,
            ]);
    }

    // For test reset password with data invalid
    public function test_reset_password_with_data_invalid()
    {
        $requestData = [
            'email' => $this->user->email,
            'code' => '',
            'password' => 'new_password',
            'password_confirmation' => 'new_password1',
        ];

        $response = $this->postJson('/api/v1/reset-password', $requestData);
        $response->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson([
                "success" => false,
                "message" => __('common.msg_valid_fails'),
                "code" => Response::HTTP_BAD_REQUEST,
            ]);
    }

    // For test reset password with case not found user
    public function test_reset_password_with_not_found_user()
    {
        $requestData = [
            'email' => 'email@gmail.com',
            'code' => '',
            'password' => 'new_password',
            'password_confirmation' => 'new_password1',
        ];

        $response = $this->postJson('/api/v1/reset-password', $requestData);
        $response->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson([
                "success" => false,
                "message" => __('common.msg_valid_fails'),
                "code" => Response::HTTP_BAD_REQUEST,
            ]);
    }

    // For test login success
    public function test_login_successfully()
    {
        $faker = $this->faker;
        $password = $faker->password;
        $user = User::factory()->create([
            'name' => $faker->name,
            'email' => $faker->unique()->safeEmail,
            'password' => $password,
            'email_verified_at' => now(),
        ]);
        $requestData = [
            'email' => $user->email,
            'password' => $password,
        ];
        $response = $this->postJson('/api/v1/login', $requestData);
        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "success" => true,
                "code" => Response::HTTP_OK,
            ]);
        $response->assertJsonStructure([
            "success",
            "data" => [
                "name",
                "email",
                "token",
            ],
            "code"
        ])->assertJson(['data' => ['token' => true]]);
        $this->assertNotNull($response['data']['token']);
    }

    // For test login case user not verify email
    public function test_login_with_case_user_not_verified_email()
    {
        $faker = $this->faker;
        $password = $faker->password;
        $user = User::factory()->create([
            'name' => $faker->name,
            'email' => $faker->unique()->safeEmail,
            'password' => $password,
            'email_verified_at' => null,
        ]);
        $requestData = [
            'email' => $user->email,
            'password' => $password,
        ];
        $response = $this->postJson('/api/v1/login', $requestData);
        $response->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson([
                "success" => false,
                "message" => __('common.msg_not_verify_email'),
                "code" => Response::HTTP_BAD_REQUEST,
            ]);
    }

    // For test login case request param wrong user or pass
    public function test_login_with_case_user_or_pass_wrong()
    {
        $requestData = [
            'email' => 'test@gmail.com',
            'password' => 'password',
        ];
        $response = $this->postJson('/api/v1/login', $requestData);
        $response->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson([
                "success" => false,
                "message" => __('common.msg_login_fail'),
                "code" => Response::HTTP_BAD_REQUEST,
            ]);
    }
}
