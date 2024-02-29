<?php

namespace Tests\Feature\Controllers;

use App\Models\Movie;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Http\Response;
use Faker\Factory as Faker;

class MovieControllerTest extends TestCase
{
    // use RefreshDatabase;

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


    /** @test */
    public function test_get_list_movie()
    {
        $response = $this->getJson('/api/v1/movie/');

        $response->assertStatus(Response::HTTP_OK);
    }

    // For get show movie
    public function test_show_movie_successfully()
    {
        $response = $this->getJson('/api/v1/movie/show/' . $this->movie->id);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'success' => true,
                'data' => $this->movie->toArray(),
                'code' => Response::HTTP_OK
            ]);
    }

    // For get movie not exist
    public function test_show_non_existing_movie()
    {
        $response = $this->getJson('/api/v1/movie/show/999999');

        $response->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson([
                'success' => false,
                'code' => Response::HTTP_BAD_REQUEST
            ]);
    }

    // For store movie
    public function test_store_movie_successfully()
    {
        $faker = \Faker\Factory::create();
        $requestData = [
            'title' => $faker->sentence,
            'description' => $faker->paragraph,
            'release_date' => $faker->date,
            'duration' => $faker->numberBetween(60, 180),
            'genre' => $faker->word,
            'director' => $faker->name,
            'cast' => $faker->name . ', ' . $faker->name . ', ' . $faker->name,
            'rating' => $faker->randomFloat(1, 0, 10),
            'poster' => $faker->url,
            'trailer' => $faker->url,
            'country' => $faker->country,
            'language' => $faker->languageCode,
        ];
        $response = $this->postJson('/api/v1/movie/store', $requestData);
        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJson([
                "success" => true,
                "code" => Response::HTTP_CREATED,
            ]);

        $this->assertDatabaseHas('movies', $requestData);
    }

    // For store with param request invalid
    public function test_store_movie_with_invalid_data()
    {
        $dataInvalid = ['title' => 'Inception data invalid'];
        $response = $this->postJson('/api/v1/movie/store', $dataInvalid);
        $response->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson([
                "success" => false,
                "message" => __('common.msg_valid_fails'),
                "code" => Response::HTTP_BAD_REQUEST,
            ]);

        $this->assertDatabaseMissing('movies', $dataInvalid);
    }

    // For test update movie success
    public function test_update_movie_successfully()
    {
        $newData = [
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'release_date' => $this->faker->date,
            'duration' => $this->faker->numberBetween(60, 180),
            'genre' => $this->faker->word,
            'director' => $this->faker->name,
            'cast' => $this->faker->name . ', ' . $this->faker->name,
            'rating' => $this->faker->randomFloat(1, 0, 10),
            'poster' => $this->faker->imageUrl(),
            'trailer' => $this->faker->url,
            'country' => $this->faker->country,
            'language' => $this->faker->languageCode,
        ];
        $response = $this->putJson('/api/v1/movie/update/' . $this->movie->id, $newData);
        $response->assertStatus(200)
            ->assertJson([
                "success" => true,
                "code" => Response::HTTP_OK,
            ]);

        $this->assertDatabaseHas('movies', $newData);
    }

    // For test update movie not exist
    public function test_update_non_existent_movie_should_fail()
    {
        $newData = [
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'release_date' => $this->faker->date,
            'duration' => $this->faker->numberBetween(60, 180),
            'genre' => $this->faker->word,
            'director' => $this->faker->name,
            'cast' => $this->faker->name . ', ' . $this->faker->name,
            'rating' => $this->faker->randomFloat(1, 0, 10),
            'poster' => $this->faker->imageUrl(),
            'trailer' => $this->faker->url,
            'country' => $this->faker->country,
            'language' => $this->faker->languageCode,
        ];
        $response = $this->putJson('/api/v1/movie/update/999999', $newData);
        $response->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson([
                "success" => false,
                "message" => __('common.msg_find_not_found', ['data' => __('movie.lbl_title')]),
                "code" => Response::HTTP_BAD_REQUEST,
            ]);
    }

    // For test destroy movie success
    public function test_destroy_movie_successfully()
    {
        $response = $this->deleteJson('/api/v1/movie/destroy/' . $this->movie->id);
        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "success" => true,
                "code" => Response::HTTP_OK,
                "message" => __('common.msg_delete_success'),
            ]);

        $this->assertDatabaseMissing('movies', ['id' => $this->movie->id]);
    }

    // For test destroy non exist movie => fail
    public function test_destroy_nonexistent_movie_should_fail()
    {
        $response = $this->deleteJson('/api/v1/movie/destroy/999999');
        $response->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson([
                "success" => false,
                "code" => Response::HTTP_BAD_REQUEST,
            ]);
    }
}
