<?php

namespace App\Http\Controllers\Api;

use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Movie\StoreRequest;
use App\Http\Requests\Api\Movie\UpdateRequest;
use Illuminate\Support\Facades\Auth;
use Throwable;

class MovieController extends Controller
{
    private $movie;

    public function __construct(Movie $movie)
    {
        $this->movie = $movie;
    }

    /**
     * Get List Movie
     *
     * @param Request $request
     * 
     * @return mixed
     */
    public function index(Request $request)
    {
        $movies = $this->movie->orderBy('id', 'desc')->paginate(PAGINATION_PAGE_DEFAULT);

        return successPaginateResponse($movies, Response::HTTP_OK);
    }

    /**
     * Find Movie
     *
     * @param Request $request
     * 
     * @return mixed
     */
    public function show($id)
    {
        try {
            $movie = $this->movie->findOrFail($id);

            return successResponse(Response::HTTP_CREATED, $movie);
        } catch (Throwable $throw) {
            return failResponse(Response::HTTP_BAD_REQUEST, __('common.msg_find_not_found', ['data' => __('movie.lbl_title')]));
        }
    }

    /**
     * Store Movie
     *
     * @param Request $request
     * 
     * @return mixed
     */
    public function store(StoreRequest $request)
    {
        try {
            $data = $request->only([
                'title',
                'description',
                'release_date',
                'duration',
                'genre',
                'director',
                'cast',
                'rating',
                'poster',
                'trailer',
                'country',
                'language',
            ]);
            $movie = $this->movie->create($data);

            return successResponse(Response::HTTP_CREATED, $movie);
        } catch (Throwable $throw) {
            return failResponse(Response::HTTP_BAD_REQUEST, __('common.msg_create_error'));
        }
    }

    /**
     * Update Movie
     *
     * @param Request $request
     * 
     * @return mixed
     */
    public function update(UpdateRequest $request, $id)
    {
        try {
            $movie = $this->movie->findOrFail($id);
            $data = $request->only([
                'title',
                'description',
                'release_date',
                'duration',
                'genre',
                'director',
                'cast',
                'rating',
                'poster',
                'trailer',
                'country',
                'language',
            ]);
            $movie->update($data);

            return successResponse(Response::HTTP_OK, $movie);
        } catch (Throwable $throw) {
            return failResponse(Response::HTTP_BAD_REQUEST, __('common.msg_find_not_found', ['data' => __('movie.lbl_title')]));
        }
    }

    /**
     * Destroy Movie
     *
     * @param Request $request
     * 
     * @return mixed
     */
    public function destroy($id)
    {
        try {
            $this->movie->findOrFail($id)->delete();

            return successResponse(Response::HTTP_OK, [], __('common.msg_delete_success'));
        } catch (Throwable $throw) {
            return failResponse(Response::HTTP_BAD_REQUEST, __('common.msg_find_not_found', ['data' => __('movie.lbl_title')]));
        }
    }
}
