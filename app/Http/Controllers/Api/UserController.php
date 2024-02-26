<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Throwable;

class UserController extends Controller
{
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getList(Request $request)
    {
        
    }

    /**
     * @param Request $request
     * 
     * @return mixed
     */
    public function getInfo(Request $request)
    {
        $user = $request->user();
        $favorites = $user->favorites;
        $result = [
            'name' => $user->name,
            'email' => $user->email,
            'updated_at' => $user->updated_at,
            'list_movie_favorite' => $favorites,
        ];

        return successResponse(Response::HTTP_OK, $result);
    }

    /**
     * @param Request $request
     * 
     * @return mixed
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email',
        ]);

        if ($user->email != $request->get('email')) {
            return failResponse(Response::HTTP_BAD_REQUEST, __('common.msg_update_error'));
        }

        try {
            $user->name = $request->name;
            $user->save();
            $result = [
                'name' => $user->name,
                'email' => $user->email,
                'updated_at' => $user->updated_at,
            ];

            return successResponse(Response::HTTP_OK, $result);
        } catch (Throwable $thow) {
            return failResponse(Response::HTTP_BAD_REQUEST, __('common.msg_update_error'));
        }
    }
}
