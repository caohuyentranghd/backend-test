<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Movie\StoreRequest;
use App\Http\Requests\Api\Movie\UpdateRequest;
use App\Services\Internals\Movie\MovieServiceInterface;
use Illuminate\Support\Facades\Log;
use Throwable;

class MovieController extends Controller
{
    private $movieService;

    public function __construct(MovieServiceInterface $movieService)
    {
        $this->movieService = $movieService;
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
        $movies = $this->movieService->getList(
            collect([]),
            collect([
                'limit' => PAGINATION_PAGE_DEFAULT,
                'is_sort_by_id_desc' => true,
            ])
        );

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
            $movie = $this->movieService->show(collect(['id' => $id]));

            return successResponse(Response::HTTP_OK, $movie);
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
            $movie = $this->movieService->store($data);

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
            $movie = $this->movieService->update(collect($data), collect(['id' => $id]));

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
            $this->movieService->destroy(collect(['id' => $id]));

            return successResponse(Response::HTTP_OK, [], __('common.msg_delete_success'));
        } catch (Throwable $throw) {
            return failResponse(Response::HTTP_BAD_REQUEST, __('common.msg_find_not_found', ['data' => __('movie.lbl_title')]));
        }
    }
}
