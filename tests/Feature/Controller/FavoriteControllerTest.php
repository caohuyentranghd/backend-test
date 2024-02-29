<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Movie;
use App\Models\Favorite;
use Illuminate\Http\Response;
use Faker\Factory as Faker;

class FavoriteControllerTest extends TestCase
{
    //use RefreshDatabase;

    public $movie;
    public $user;
    public $faker;

    public function setUp(): void
    {
        parent::setUp();
        $this->faker = Faker::create();
        $this->movie = Movie::factory()->create();
        $this->user = User::factory()->state(['role' => ROLE_USER_ADMIN])->create();
        $this->actingAs($this->user);
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $this->movie->delete();
        $this->user->delete();
    }

    // For store favorite movie success
    public function test_store_favorite_movie_successfully()
    {
        $response = $this->postJson('/api/v1/favorite/store', ['movie_id' => $this->movie->id]);
        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJson([
                "success" => true,
                "code" => Response::HTTP_CREATED,
            ]);
        $this->assertDatabaseHas('favorites', [
            'user_id' => $this->user->id,
            'movie_id' => $this->movie->id,
        ]);
    }

    // For store favorite movie but movie already exists in favorite
    public function test_store_favorite_movie_already_exists_should_fail()
    {
        Favorite::factory()->create(['user_id' => $this->user->id, 'movie_id' => $this->movie->id]);
        $response = $this->postJson('/api/v1/favorite/store', ['movie_id' => $this->movie->id]);
        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "success" => true,
                "code" => Response::HTTP_OK,
            ]);
    }

    // For store favorite movie but request invalidate
    public function test_store_favorite_movie_without_movie_id_should_fail()
    {
        $response = $this->postJson('/api/v1/favorite/store', []);
        $response->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson([
                "success" => false,
                "code" => Response::HTTP_BAD_REQUEST,
            ]);
    }

    // For store favorite movie but movie is not exist in database
    public function test_store_favorite_movie_with_invalid_movie_id_should_fail()
    {
        $response = $this->postJson('/api/v1/favorite/store', ['movie_id' => 999]);
        $response->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson([
                "success" => false,
                "code" => Response::HTTP_BAD_REQUEST,
            ]);
    }

    // For destroy movie in favorite list success
    public function test_destroy_favorite_movie_successfully()
    {
        Favorite::factory()->create(['user_id' => $this->user->id, 'movie_id' => $this->movie->id]);
        $response = $this->deleteJson('/api/v1/favorite/remove', ['movie_id' => $this->movie->id]);
        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "success" => true,
                "code" => Response::HTTP_OK,
            ]);

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $this->user->id,
            'movie_id' => $this->movie->movie_id,
        ]);
    }

    // For destroy movie in favorite list but movie not exist in favorite of user
    public function test_destroy_favorite_movie_not_exists_should_fail()
    {
        $response = $this->deleteJson('/api/v1/favorite/remove', ['movie_id' => $this->movie->id]);
        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "success" => true,
                "code" => Response::HTTP_OK,
            ]);
    }

    // For destroy movie in favorite list but movie not exist in database
    public function test_destroy_favorite_movie_without_movie_id_should_fail()
    {
        $response = $this->deleteJson('/api/v1/favorite/remove', []);

        $response->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson([
                "success" => false,
                "code" => Response::HTTP_BAD_REQUEST,
            ]);
    }
}
