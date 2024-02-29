<?php

namespace Tests\Feature\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Faker\Factory as Faker;

class UserControllerTest extends TestCase
{
    //use RefreshDatabase;

    public $user;
    public $faker;

    public function setUp(): void
    {
        parent::setUp();
        $this->faker = Faker::create();
        $this->user = User::factory()->state(['role' => ROLE_USER_ADMIN])->create();
        $this->actingAs($this->user);
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $this->user->delete();
    }

    /** @test */
    public function test_get_user_info()
    {
        $list_movie_favorite = $this->user->favorites->toArray();
        $response = $this->getJson('/api/v1/user/info');

        $response->assertStatus(200)
            ->assertJson([
                "success" => true,
                "message" => "",
                "data" => [
                    "name" => $this->user->name,
                    "email" => $this->user->email,
                    "updated_at" => $this->user->updated_at->format('Y-m-d H:i:s'),
                    "list_movie_favorite" => $list_movie_favorite,
                ],
                "code" => 200
            ]);
    }

    /** @test */
    public function test_update_user_profile()
    {
        $newProfileData = [
            'name' => 'New Name',
            'email' => $this->user->email,
        ];

        $response = $this->putJson('/api/v1/user/update-profile', $newProfileData);

        // Refresh the user instance to get the latest data from the database
        $this->user->refresh();

        $response->assertStatus(200)
            ->assertJson([
                "success" => true,
                "message" => "",
                "data" => [
                    "name" => $this->user->name,
                    "email" => $this->user->email,
                    "updated_at" => $this->user->updated_at->format('Y-m-d H:i:s'),
                ],
                "code" => 200
            ]);

        // Assert that the user profile has been updated correctly
        $this->assertEquals($newProfileData['name'], $this->user->name);
        $this->assertEquals($newProfileData['email'], $this->user->email);
    }

    /** @test */
    public function test_update_user_profile_when_email_not_equal()
    {
        $newProfileData = [
            'name' => 'New Name',
            'email' => 'new@example.com',
        ];
        $response = $this->putJson('/api/v1/user/update-profile', $newProfileData);
        $response->assertStatus(400)
            ->assertJson([
                "success" => false,
                "message" => "Update fail",
                "error" => [],
                "code" => 400
            ]);
    }
}
