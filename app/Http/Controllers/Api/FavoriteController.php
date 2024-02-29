<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Services\Internals\Favorite\FavoriteServiceInterface;
use Illuminate\Support\Facades\Auth;
use Throwable;

class FavoriteController extends Controller
{
    private $service;

    public function __construct(FavoriteServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * @param Request $request
     * 
     * @return mixed
     */
    public function store(Request $request)
    {
        $request->validate([
            'movie_id' => 'required|exists:movies,id',
        ]);
        $user = Auth::user();
        $movieId = $request->get('movie_id');
        $existingFavorite = $this->service->getFirstBy(
            collect([
                'user_id' => $user->id,
                'movie_id' => $movieId,
            ])
        );

        try {
            if (!empty($existingFavorite)) {
                return successResponse(Response::HTTP_OK, [], __('common.msg_favorite_exists_list'));
            }

            $result = $this->service->store([
                'user_id' => $user->id,
                'movie_id' => $movieId,
            ]);

            return successResponse(Response::HTTP_CREATED, $result);
        } catch (Throwable $thow) {
            return failResponse(Response::HTTP_BAD_REQUEST, __('common.msg_create_error'));
        }
    }

    /**
     * @param Request $request
     * 
     * @return mixed
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'movie_id' => 'required|exists:movies,id'
        ]);
        $user = Auth::user();
        $movieId = $request->get('movie_id');

        try {
            $favorite = $this->service->getFirstBy(
                collect([
                    'user_id' => $user->id,
                    'movie_id' => $movieId,
                ])
            );

            if (empty($favorite)) {
                return successResponse(Response::HTTP_OK, [], __('common.msg_favorite_not_exists_list'));
            }

            $this->service->destroy(collect(['id' => $favorite->id]));

            return successResponse(Response::HTTP_OK, [], __('common.msg_remove_movie_favorite_success'));
        } catch (Throwable $thow) {
            return failResponse(Response::HTTP_BAD_REQUEST, __('common.msg_remove_movie_favorite_error'));
        }
    }
}
