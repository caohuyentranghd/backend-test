<?php

namespace App\Http\Controllers\Api;

use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Throwable;

class FavoriteController extends Controller
{
    private $favorite;

    public function __construct(Favorite $favorite)
    {
        $this->favorite = $favorite;
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
        $existingFavorite = $this->favorite->where('user_id', $user->id)
            ->where('movie_id', $movieId)
            ->exists();

        try {
            if ($existingFavorite) {
                return successResponse(Response::HTTP_OK, [], __('common.msg_favorite_exists_list'));
            }

            $result = $this->favorite->create([
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
            $existingFavorite = $this->favorite->where('user_id', $user->id)
                ->where('movie_id', $movieId)
                ->first();

            if (!$existingFavorite) {
                return successResponse(Response::HTTP_OK, [], __('common.msg_favorite_not_exists_list'));
            }

            $existingFavorite->delete();

            return successResponse(Response::HTTP_OK, [], __('common.msg_remove_movie_favorite_success'));
        } catch (Throwable $thow) {
            return failResponse(Response::HTTP_BAD_REQUEST, __('common.msg_remove_movie_favorite_error'));
        }
    }
}
